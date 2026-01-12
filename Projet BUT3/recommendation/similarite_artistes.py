from sqlalchemy import create_engine
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity

engine = create_engine(
    "postgresql+psycopg2://user:password@localhost:5432/db"
)


def load_all_data():
    with engine.connect() as conn:
        artists = pd.read_sql(
            "SELECT artist_id, artist_name FROM sae.Artist", conn
        )

        tags = pd.read_sql("""
            SELECT at.artist_id, t.tag_name
            FROM sae.Artist_Tag at
            JOIN sae.Tag t USING(tag_id)
        """, conn)

        languages = pd.read_sql("""
            SELECT al.artist_id, l.language_name
            FROM sae.Artist_Language al
            JOIN sae.Language l USING(language_id)
        """, conn)

        tracks = pd.read_sql("""
            SELECT track_id, track_title, track_duration, genres_list, artist_id
            FROM sae.View_Track_Materialise
        """, conn)

        genres = pd.read_sql(
            "SELECT genre_id, genre_title FROM sae.Genre", conn
        )

    return artists, tags, languages, tracks, genres


def tag_similarity(tags_df, artist_id):
    mat = pd.crosstab(tags_df.artist_id, tags_df.tag_name)
    sim = cosine_similarity(mat)
    sim_df = pd.DataFrame(sim, index=mat.index, columns=mat.index)
    return sim_df.loc[artist_id].reset_index(name="tag_similarity")


def nb_tracks_distance(tracks_df, artist_id):
    counts = tracks_df.groupby("artist_id").size()
    ref = counts.loc[artist_id]
    max_dist = abs(counts - ref).max()
    dist = abs(counts - ref) / max_dist
    return dist.reset_index(name="nb_tracks_distance")


def language_similarity(lang_df, artist_id):
    mat = pd.crosstab(lang_df.artist_id, lang_df.language_name)
    sim = cosine_similarity(mat)
    sim_df = pd.DataFrame(sim, index=mat.index, columns=mat.index)
    return sim_df.loc[artist_id].reset_index(name="language_similarity")


def track_title_similarity(tracks_df, artist_id):
    ref_tracks = set(tracks_df[tracks_df.artist_id == artist_id].track_title)

    results = []
    for aid, group in tracks_df.groupby("artist_id"):
        other_tracks = set(group.track_title)
        inter = len(ref_tracks & other_tracks)
        union = len(ref_tracks | other_tracks)
        score = inter / union if union else 0
        results.append({"artist_id": aid, "track_title_similarity": score})

    return pd.DataFrame(results)


def genre_similarity(tracks_df, genres_df, artist_id):
    exploded = tracks_df.copy()
    exploded["genre_id"] = exploded["genres_list"].str.split(",")
    exploded = exploded.explode("genre_id")
    exploded["genre_id"] = exploded["genre_id"].astype(int)

    exploded = exploded.merge(genres_df, on="genre_id")

    mat = pd.crosstab(exploded.artist_id, exploded.genre_title)
    mat = mat.div(mat.sum(axis=1), axis=0).fillna(0)

    sim = cosine_similarity(mat)
    sim_df = pd.DataFrame(sim, index=mat.index, columns=mat.index)

    return sim_df.loc[artist_id].reset_index(name="genre_similarity")


def track_duration_distance(tracks_df, artist_id):
    durations = tracks_df.groupby("artist_id")["track_duration"].mean()
    ref = durations.loc[artist_id]
    max_dist = abs(durations - ref).max()
    dist = abs(durations - ref) / max_dist
    return dist.reset_index(name="track_duration_distance")


def global_similarity(artist_id, weights=None):
    if weights is None:
        weights = {
            "tag_similarity": 1.5,
            "nb_tracks_distance": 0.5,
            "language_similarity": 1,
            "track_title_similarity": 2.5,
            "genre_similarity": 2.5,
            "track_duration_distance": 2,
        }

    artists, tags, languages, tracks, genres = load_all_data()

    df = (
        tag_similarity(tags, artist_id)
        .merge(nb_tracks_distance(tracks, artist_id), on="artist_id")
        .merge(language_similarity(languages, artist_id), on="artist_id")
        .merge(track_title_similarity(tracks, artist_id), on="artist_id")
        .merge(genre_similarity(tracks, genres, artist_id), on="artist_id")
        .merge(track_duration_distance(tracks, artist_id), on="artist_id")
    )

    for col, w in weights.items():
        df[col] = df[col] * w

    df["global_score"] = df[list(weights.keys())].sum(axis=1)

    return df.sort_values("global_score", ascending=False)


result = global_similarity(artist_id=1)
print(result.head(10))
