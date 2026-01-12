#!/usr/bin/env python3
# graphsage_with_genre_parser.py
# GraphSAGE pipeline with robust genre parsing (single id OR list of ids)
# - Feature construction (numeric + multi-label genre + artist top-k)
# - Standard scaling, optional feature weights
# - Graph construction user<->track
# - Training with BPR-like pairwise loss + negative sampling
# - Recommendations + explanations (genre overlap + top feature matches)
# Author: ChatGPT (generator), adapt as needed

import os
import random
import math
import argparse
from collections import defaultdict

import numpy as np
import pandas as pd
from sklearn.preprocessing import StandardScaler, MultiLabelBinarizer
from sklearn.metrics.pairwise import cosine_similarity

import torch
import torch.nn.functional as F
from torch_geometric.data import Data
from torch_geometric.nn import SAGEConv

# --------------------
# CONFIG
# --------------------
CONFIG = {
    "csv_path": "Code_Sae/data/code/final/track_final9.csv",   # path to your CSV (edit)
    # user_listens: mapping user_key -> list of track_id values as in CSV (not dataframe indices)
    "user_listens": {
        "U0": [2, 3, 5,10,20,38741,146155,51013,51014],
        "U1": [10, 20],
        "U2": [2, 10]
    },

    # columns to use as numeric features if present in CSV
    "numeric_features": [ "track_duration"#,"track_interest"
                          ],

    # genre handling
    "use_genre_onehot": True,
    "genre_columns": ["track_genre_top", "track_genres"],  # fields to parse
    "max_unique_genres": None,  # None => keep all observed genres; or set limit to top-k

    # artist handling
    "use_artist_topk_onehot": True,
    "top_k_artists": 50,  # reduce dimension

    # feature weighting (applied AFTER scaling). Use exact column names or wildcard 'genre_*'
    "feature_weights": {
        # example: "duration_s": 0.8, "genre_*": 1.5
        "genre_*": 20
    },

    # model / training
    "hidden_size": 64,
    "out_size": 32,
    "dropout": 0.3,
    "lr": 2e-3,
    "epochs": 180,
    "n_negatives": 3,
    "device": "cuda" if torch.cuda.is_available() else "cpu",

    # recommendation
    "topk": 5
}
# --------------------
# Genre ID → Name mapping
# --------------------
def load_genre_mapping(path="Code_Sae/data/code/final/raw_genres.csv"):
    """
    Load genre_id → genre_name mapping from CSV.
    Expected columns: genre_id, genre_name
    """
    if not os.path.exists(path):
        print(f"[WARN] genre mapping file not found: {path}")
        return {}

    gdf = pd.read_csv(path)
    mapping = {}

    for _, row in gdf.iterrows():
        try:
            gid = int(row["genre_id"])
            mapping[gid] = str(row["genre_title"])
        except:
            continue

    print(f"[INFO] Loaded {len(mapping)} genre labels")
    return mapping


# --------------------
# Utilities: robust genre parsing
# --------------------
def parse_genre_field(val):
    """
    Convert a genre field to a list of ints (genre ids).
    Handles:
     - NaN / None -> []
     - single numeric string '10' -> [10]
     - list CSV '14364,10361' -> [14364, 10361]
     - sometimes the IDs in original dataset may be non-digit; we ignore non-digits
    """
    if pd.isna(val):
        return []
    if isinstance(val, (int, np.integer)):
        return [int(val)]
    if isinstance(val, float) and not math.isnan(val):
        try:
            return [int(val)]
        except:
            return []
    if isinstance(val, str):
        s = val.strip()
        if s == "":
            return []
        # if it contains non-digit separators, split by comma
        parts = [p.strip() for p in s.split(",")]
        ids = []
        for p in parts:
            if p == "":
                continue
            # some fields might contain quotes or brackets; keep digits
            token = ''.join(ch for ch in p if ch.isdigit())
            if token != "":
                try:
                    ids.append(int(token))
                except:
                    continue
        return ids
    # fallback
    return []

# --------------------
# Data loading & feature construction
# --------------------
def load_and_prepare_features(csv_path, config):
    df = pd.read_csv(csv_path, dtype=str).reset_index(drop=True)

    print("Colonnes du CSV détectées :")
    print(df.columns.tolist())


    # ensure essential columns exist
    if 'track_id' not in df.columns:
        df['track_id'] = df.index.astype(int)
    if 'track_title' not in df.columns:
        df['track_title'] = df['track_id'].astype(str)

    # parse duration to seconds if present
    def to_seconds(s):
        try:
            if pd.isna(s):
                return np.nan
            parts = [int(p) for p in str(s).split(":")]
            if len(parts) == 2:
                return parts[0] * 60 + parts[1]
            if len(parts) == 3:
                return parts[0] * 3600 + parts[1] * 60 + parts[2]
            return float(s)
        except:
            try:
                return float(s)
            except:
                return np.nan

    if 'track_duration' in df.columns:
        df['duration_s'] = df['track_duration'].apply(to_seconds)
    else:
        df['duration_s'] = np.nan

    # ensure numeric features are numeric where present
    for col in config['numeric_features']:
        if col in df.columns:
            df[col] = pd.to_numeric(df[col], errors='coerce')

    # parse genres from multiple columns into unified list per row
    # create 'all_genres' column as list of ints
    genre_cols = [c for c in config.get('genre_columns', []) if c in df.columns]
    if config.get('use_genre_onehot', True) and len(genre_cols) > 0:
        # parse each genre column into lists
        for c in genre_cols:
            parsed = df[c].apply(parse_genre_field)
            df[c + "_parsed"] = parsed
        # combine
        def combine_genres(row):
            combined = []
            for c in genre_cols:
                parsed = row.get(c + "_parsed", [])
                if isinstance(parsed, list):
                    combined.extend(parsed)
            # remove duplicates and keep ints
            combined_clean = []
            for g in combined:
                try:
                    gi = int(g)
                except:
                    continue
                if gi not in combined_clean:
                    combined_clean.append(gi)
            return combined_clean
        df['all_genres'] = df.apply(combine_genres, axis=1)
    else:
        df['all_genres'] = [[] for _ in range(len(df))]

    # artist top-k mapping
    if config.get('use_artist_topk_onehot', True) and 'artist_id' in df.columns:
        art_series = df['artist_id'].fillna('-1').astype(str)
        topk = config.get('top_k_artists', 50)
        top_artists = art_series.value_counts().nlargest(topk).index.tolist()
        df['artist_topk'] = art_series.map(lambda a: a if a in top_artists else 'OTHER')
    else:
        df['artist_topk'] = df['artist_id'].fillna('OTHER').astype(str)

    # Build feature DataFrame pieces
    feature_frames = []

    # numeric features present in data
    numeric_cols_present = [c for c in config['numeric_features'] if c in df.columns]
    if len(numeric_cols_present) > 0:
        feature_frames.append(df[numeric_cols_present].fillna(0.0).astype(float))

    # genre multilabel -> MultiLabelBinarizer
    genre_mlb = None
    genre_df = None
    if config.get('use_genre_onehot', True):
        mlb = MultiLabelBinarizer(sparse_output=False)
        # optionally limit universe: if max_unique_genres set, keep only top K by frequency
        if config.get('max_unique_genres', None) is None:
            genre_matrix = mlb.fit_transform(df['all_genres'])
            genre_cols = [f"genre_{g}" for g in mlb.classes_]
            genre_df = pd.DataFrame(genre_matrix, columns=genre_cols, index=df.index)
        else:
            # compute frequency and keep top-K
            all_ids = [g for row in df['all_genres'] for g in row]
            vc = pd.Series(all_ids).value_counts()
            topk = int(config['max_unique_genres'])
            top_ids = vc.nlargest(topk).index.astype(int).tolist()
            # transform per-row: keep only ids in top_ids
            def filter_top(row):
                return [g for g in row if g in top_ids]
            filtered = df['all_genres'].apply(filter_top)
            mlb = MultiLabelBinarizer(classes=sorted(top_ids))
            genre_matrix = mlb.fit_transform(filtered)
            genre_cols = [f"genre_{g}" for g in mlb.classes_]
            genre_df = pd.DataFrame(genre_matrix, columns=genre_cols, index=df.index)
        if genre_df is not None:
            feature_frames.append(genre_df)
            genre_mlb = mlb

    # artist one-hot
    if config.get('use_artist_topk_onehot', True):
        artist_dummies = pd.get_dummies(df['artist_topk'].astype(str), prefix='artist')
        feature_frames.append(artist_dummies)

    # final features
    if len(feature_frames) == 0:
        raise RuntimeError("No features available for model. Check CONFIG and CSV columns.")
    features_df = pd.concat(feature_frames, axis=1).fillna(0.0)

    # standard scaling
    scaler = StandardScaler()
    scaled = pd.DataFrame(scaler.fit_transform(features_df.values),
                          columns=features_df.columns, index=features_df.index)

    # apply feature weights if present (exact name or prefix*)
    def apply_weights(df_feat, weights):
        if not weights:
            return df_feat
        out = df_feat.copy()
        for key, w in weights.items():
            if key.endswith('*'):
                prefix = key[:-1]
                cols = [c for c in out.columns if c.startswith(prefix)]
                for c in cols:
                    out[c] = out[c] * float(w)
            else:
                if key in out.columns:
                    out[key] = out[key] * float(w)
        return out

    scaled = apply_weights(scaled, config.get('feature_weights', {}))
    genre_id_to_name = load_genre_mapping("Code_Sae/data/code/final/raw_genres.csv")

    # return necessary objects
    meta = {
    'df': df,
    'features_df': features_df,
    'scaled': scaled,
    'genre_mlb': genre_mlb,
    'genre_id_to_name': genre_id_to_name
    }


    return meta

# --------------------
# Graph construction helpers
# --------------------
def build_graph_from_user_listens(scaled_features_df, user_listens_cfg):
    # scaled_features_df is DataFrame indexed by df index (0..n-1)
    # Map track_id -> df index outside (caller)
    # Build user features as zeros (same feature dim) and edges user<->song
    num_users = len(user_listens_cfg)
    feat_dim = scaled_features_df.shape[1]
    x_users = torch.zeros((num_users, feat_dim), dtype=torch.float)
    x_music = torch.tensor(scaled_features_df.values, dtype=torch.float)
    x = torch.cat([x_users, x_music], dim=0)

    edge_list = []
    # user_listens_cfg here must map user_key->list of df indices (0..n-1)
    for ui, listens in enumerate(user_listens_cfg.values()):
        for idx in listens:
            edge_list.append([ui, num_users + int(idx)])
            edge_list.append([num_users + int(idx), ui])
    if len(edge_list) == 0:
        raise RuntimeError("No edges created: user_listens may be empty or not mapped to df indices.")
    edge_index = torch.tensor(edge_list, dtype=torch.long).t().contiguous()
    return x, edge_index

# --------------------
# Model definition
# --------------------
class GraphSAGEModel(torch.nn.Module):
    def __init__(self, in_ch, hidden_ch, out_ch, dropout=0.2):
        super().__init__()
        self.conv1 = SAGEConv(in_ch, hidden_ch)
        self.conv2 = SAGEConv(hidden_ch, out_ch)
        self.dropout = dropout

    def forward(self, x, edge_index):
        x = self.conv1(x, edge_index)
        x = F.relu(x)
        x = F.dropout(x, p=self.dropout, training=self.training)
        x = self.conv2(x, edge_index)
        return x

# --------------------
# BPR-like loss
# --------------------
def sample_negative_index(n_songs, exclude_set):
    # sample single negative df index in [0..n_songs-1] not in exclude_set
    while True:
        c = random.randint(0, n_songs - 1)
        if c not in exclude_set:
            return c

def bpr_loss_scalar(u_emb, pos_emb, neg_embs):
    # u_emb: (D,), pos_emb: (D,), neg_embs: (n_neg, D)
    pos_score = (u_emb * pos_emb).sum()
    neg_scores = (neg_embs * u_emb.unsqueeze(0)).sum(dim=1)
    diffs = pos_score - neg_scores
    loss = -torch.log(torch.sigmoid(diffs) + 1e-15).mean()
    return loss

# --------------------
# Training and recommendation pipeline
# --------------------
def train_and_recommend(meta, config):
    df = meta['df']
    scaled = meta['scaled']

    # map track_id -> df index
    trackid_to_idx = {
        int(t): idx
        for idx, t in enumerate(df['track_id'].astype(int).tolist())
    }

    # convert user_listens from track_id to df index
    user_listens_cfg = config['user_listens']
    user_listens_mapped = {}

    for u, track_ids in user_listens_cfg.items():
        mapped = []
        for tid in track_ids:
            try:
                t_int = int(tid)
            except:
                continue

            if t_int in trackid_to_idx:
                mapped.append(trackid_to_idx[t_int])

        if len(mapped) == 0:
            mapped = [0]  # fallback minimal

        user_listens_mapped[u] = mapped

    num_users = len(user_listens_mapped)
    n_songs = len(df)

    # build graph
    x, edge_index = build_graph_from_user_listens(scaled, user_listens_mapped)
    device = config['device']
    x = x.to(device)
    edge_index = edge_index.to(device)

    # model
    model = GraphSAGEModel(
        in_ch=x.shape[1],
        hidden_ch=config['hidden_size'],
        out_ch=config['out_size'],
        dropout=config['dropout']
    ).to(device)

    optimizer = torch.optim.Adam(model.parameters(), lr=config['lr'])

    # training
    model.train()
    for epoch in range(1, config['epochs'] + 1):
        optimizer.zero_grad()
        embeddings = model(x, edge_index)
        loss = torch.tensor(0.0, device=device)

        for ui, listens in enumerate(user_listens_mapped.values()):
            u_emb = embeddings[ui]
            exclude_set = set(listens)

            for s in listens:
                pos_emb = embeddings[num_users + s]

                neg_idxs = [
                    num_users + sample_negative_index(n_songs, exclude_set)
                    for _ in range(config['n_negatives'])
                ]
                neg_embs = embeddings[neg_idxs]

                loss += bpr_loss_scalar(
                    F.normalize(u_emb, p=2, dim=0),
                    F.normalize(pos_emb, p=2, dim=0),
                    F.normalize(neg_embs, p=2, dim=1)
                )

        loss = loss / max(1, num_users)
        loss.backward()
        optimizer.step()

        if epoch % 20 == 0 or epoch == 1:
            print(f"Epoch {epoch}/{config['epochs']} - loss = {loss.item():.6f}")

    print("Training finished.")

    # final embeddings
    model.eval()
    with torch.no_grad():
        emb = model(x, edge_index).cpu()

    # recommendations
    recommendations = {}
    explanations = {}

    for ui, uname in enumerate(user_listens_mapped.keys()):
        u_emb = F.normalize(emb[ui], p=2, dim=0).unsqueeze(0)
        song_embs = F.normalize(emb[num_users:], p=2, dim=1)
        scores = (u_emb @ song_embs.T).squeeze(0).numpy()

        ranked = np.argsort(-scores)
        listened = set(user_listens_mapped[uname])

        topk = []
        for idx in ranked:
            if idx not in listened:
                topk.append((int(idx), float(scores[idx])))
            if len(topk) >= config['topk']:
                break

        recommendations[uname] = topk

        expls = []
        for idx, sc in topk:
            expls.append(
                explain_track_recommendation(
                    df,
                    scaled,
                    user_listens_mapped[uname],
                    idx,
                    sc,
                    config,
                    meta["genre_id_to_name"]
                )
            )
        explanations[uname] = expls

    return recommendations, explanations, model, emb, user_listens_mapped

# --------------------
# Explanation helper
# --------------------
def humanize_feature_names(feature_dict, genre_id_to_name):
    """
    Convert genre_XX features to human-readable genre names.
    """
    readable = {}

    for feat, val in feature_dict.items():
        if feat.startswith("genre_"):
            try:
                gid = int(feat.replace("genre_", ""))
                gname = genre_id_to_name.get(gid, f"UnknownGenre({gid})")
                readable[f"{gname} (id={gid})"] = val
            except:
                readable[feat] = val
        else:
            readable[feat] = val

    return readable

def explain_track_recommendation(
    df,
    scaled_features,
    listened_indices,
    candidate_idx,
    score,
    config,
    genre_id_to_name,
    top_n=3
):

    """
    Build a human-readable explanation:
    - genre overlap with listened tracks
    - artist match
    - top N per-feature similarity (smallest abs diff compared to mean listened profile)
    """
    # base info
    info = {}
    info['track_df_idx'] = int(candidate_idx)
    info['track_id'] = int(df.loc[candidate_idx, 'track_id'])
    info['track_title'] = df.loc[candidate_idx, 'track_title'] if 'track_title' in df.columns else str(info['track_id'])
    info['score'] = float(score)

    # genre overlap: get the all_genres and check intersection with listened genres
    if 'all_genres' in df.columns:
        cand_genres = set(df.loc[candidate_idx, 'all_genres'])
        listened_genre_union = set()
        for li in listened_indices:
            listened_genre_union.update(df.loc[li, 'all_genres'])
        info['genre_overlap_count'] = len(cand_genres & listened_genre_union)
        info['genre_overlap_fraction'] = (len(cand_genres & listened_genre_union) / max(1, len(cand_genres))) if len(cand_genres)>0 else 0.0
    else:
        info['genre_overlap_count'] = 0
        info['genre_overlap_fraction'] = -1

    # artist match (exact topk artist match)
    if 'artist_id' in df.columns:
        cand_artist = df.loc[candidate_idx, 'artist_id']
        info['same_artist_as_listened'] = any(str(df.loc[li, 'artist_id']) == str(cand_artist) for li in listened_indices)
    else:
        info['same_artist_as_listened'] = False

    # per-feature similarity vs mean profile of listened tracks
    mean_profile = scaled_features.iloc[listened_indices].mean(axis=0)
    candidate_profile = scaled_features.iloc[candidate_idx]
    diffs = (candidate_profile - mean_profile).abs()
    top_feats = diffs.nsmallest(top_n).index.tolist()
    raw_feats = {f: float(diffs[f]) for f in top_feats}
    info['top_similar_features'] = humanize_feature_names(
        raw_feats, genre_id_to_name
    )

    return info

# --------------------
# CLI & run
# --------------------
def main():
    parser = argparse.ArgumentParser(description="GraphSAGE recommendation with robust genre parsing")
    parser.add_argument("--config", default=None, help="optional python file to import CONFIG from")
    args = parser.parse_args()

    # optionally import external CONFIG (a Python file defining CONFIG dict)
    if args.config:
        spec = {}
        with open(args.config, 'r', encoding='utf-8') as f:
            code = f.read()
        exec(code, spec)
        if 'CONFIG' in spec:
            cfg_override = spec['CONFIG']
            CONFIG.update(cfg_override)
            print("CONFIG overridden by", args.config)

    # load and prepare
    print("Loading CSV and preparing features...")
    meta = load_and_prepare_features("Code_Sae/data/code/final/track_final9.csv", CONFIG)
    print("Features prepared. feature dim =", meta['scaled'].shape[1])

    # train + recommend
    recs, expls, model, emb, user_listens_mapped = train_and_recommend(meta, CONFIG)

    # print recommendations & explanations
    for user, rec_list in recs.items():
        print(f"\nRecommendations for user {user}:")

        for rank, (df_idx, score) in enumerate(rec_list, start=1):

            row = meta['df'].loc[df_idx]

            track_id = row['track_id']              # ✅ vrai ID
            title = row.get('track_title', 'UNKNOWN')

            print(
                f" {rank:2d}. {title} "
                f"(track_id= {track_id}, df_idx= {df_idx}, score={score:.4f})"
            )

            # explanation
            expl = next(
                (e for e in expls[user] if e['track_df_idx'] == df_idx),
                None
            )

            if expl:
                print(
                    f"     -> genre_overlap_count: {expl['genre_overlap_count']}, "
                    f"genre_overlap_fraction: {expl['genre_overlap_fraction']:.2f}, "
                    f"same_artist: {expl['same_artist_as_listened']}"
                )
                print(
                    f"     -> top_similar_features: {expl['top_similar_features']}"
                )

    print("\nDone.")


if __name__ == "__main__":
    main()
