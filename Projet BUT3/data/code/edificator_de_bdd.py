import pandas as pd
import os
import numpy as np
from datetime import datetime

def nettoyer_csv(path_in, path_out=None):
    """
    Nettoie un CSV :
      - supprime doublons et lignes incomplètes
      - convertit float → int si possible
      - explosion contrôlée (jamais pour Artist/Track)
      - FK invalides -> NULL (genre)
      - normalise les dates pour Album et Track
      - convertit MM:SS → secondes pour Track
      - supprime anciens CSV nettoyés pour ne garder que le nouveau
    """

    # Détection automatique du type
    filename = os.path.basename(path_in).lower()
    is_artist = "artist_table" in filename
    is_genre = "genre_table" in filename
    is_album = "album_table" in filename
    is_track = "track_table" in filename
    is_echonest = "stats_echonest" in filename
    is_a_a_t = "artist_album_track" in filename
    is_t_g_m = "track_genre_majoritaire" in filename

    is_u_a_f = "user_artist_favorite" in filename

    is_track_relation = "track_genre_majoritaire" in filename or \
                    "track_genre" in filename or \
                    "track_language" in filename or \
                    "track_tag" in filename

    # ============================
    # Chargement CSV
    # ============================
    df = pd.read_csv(path_in, dtype=str).fillna("")

    # ============================
    # 1) Supprimer lignes vides/incomplètes
    # ============================
    df = df[df.apply(lambda row: row.astype(bool).sum() > 1, axis=1)]

    # ============================
    # 2) Explosion (désactivée pour Artist et Track)
    # ============================
    if not (is_artist or is_track or is_album):
        cols_to_explode = []
        for col in df.columns:
            if df[col].str.contains(",", regex=False).any():
                # considérer comme liste seulement si pas un texte classique
                if df[col].str.contains(r",\s*[0-9A-Za-z]").any():
                    cols_to_explode.append(col)

        for col in cols_to_explode:
            df[col] = df[col].apply(lambda x: x.split(",") if "," in x else [x])
            df = df.explode(col)

    # ============================
    # 3) Nettoyer espaces
    # ============================
    df = df.map(lambda x: x.strip() if isinstance(x, str) else x)

    # ============================
    # 4) Supprimer doublons
    # ============================
    df = df.drop_duplicates()

    # ============================
    # 5) float -> int si possible
    # ============================
    def try_convert(value):
        try:
            num = float(value)
            return str(int(num)) if num.is_integer() else value
        except:
            return value

    df = df.map(try_convert)

    # ============================
    # 6) FK invalides pour Genre
    # ============================
    if is_genre and "genre_parent_id" in df.columns:
        valid_ids = set(df["genre_id"])
        df["genre_parent_id"] = df["genre_parent_id"].apply(
            lambda x: x if x in valid_ids else ""
        )

    # ============================
    # 7) Normalisation des dates pour Album et Track
    # ============================
    if is_album or is_track:
        def normalize_date(value):
            if not isinstance(value, str) or value == "":
                return ""
            for fmt in ("%m/%d/%Y %I:%M:%S %p", "%m/%d/%Y", "%m/%d/%y"):
                try:
                    dt = datetime.strptime(value, fmt)
                    return dt.strftime("%Y-%m-%d %H:%M:%S")
                except:
                    continue
            return value

        date_cols = [c for c in df.columns if "date" in c.lower()]
        for col in date_cols:
            df[col] = df[col].apply(normalize_date)

    # ============================
    # 8) Conversion duration MM:SS → secondes pour Track
    # ============================
    if is_track and "track_duration" in df.columns:
        def duration_to_seconds(value):
            if not isinstance(value, str) or value == "":
                return ""
            try:
                parts = value.split(":")
                if len(parts) == 2:
                    minutes, seconds = int(parts[0]), int(parts[1])
                    return str(minutes * 60 + seconds)
                elif len(parts) == 3:
                    hours, minutes, seconds = int(parts[0]), int(parts[1]), int(parts[2])
                    return str(hours * 3600 + minutes * 60 + seconds)
                else:
                    return value
            except:
                return value

        df["track_duration"] = df["track_duration"].apply(duration_to_seconds)

    # ============================
    # 9a) Suppression des lignes dont track_id n'existe pas
    # ============================
    if "track_id" in df.columns and (is_echonest or is_track_relation):
        track_cleaned = "./data/output_tables/track_table_cleaned.csv"
        if os.path.exists(track_cleaned):
            df_tracks = pd.read_csv(track_cleaned, dtype=str).fillna("")
            # Nettoyage des espaces
            df["track_id"] = df["track_id"].astype(str).str.strip()
            df_tracks["track_id"] = df_tracks["track_id"].astype(str).str.strip()
            valid_track_ids = set(df_tracks["track_id"])
            df = df[df["track_id"].isin(valid_track_ids)]

    # ============================
    # 9b) Suppression des lignes dont genre_id n'existe pas (relations)
    # ============================
    if "genre_id" in df.columns and is_t_g_m:
        genre_cleaned = "./data/output_tables/genre_table_cleaned.csv"
        if os.path.exists(genre_cleaned):
            df_genres = pd.read_csv(genre_cleaned, dtype=str).fillna("")
            # Nettoyage des espaces
            df["genre_id"] = df["genre_id"].astype(str).str.strip()
            df_genres["genre_id"] = df_genres["genre_id"].astype(str).str.strip()
            valid_genre_ids = set(df_genres["genre_id"])
            df = df[df["genre_id"].isin(valid_genre_ids)]

    # ============================
    # 9c) Suppression des lignes dont artist_id n'existe pas
    # ============================
    if "artist_id" in df.columns and is_u_a_f:
        artist_cleaned = "./data/output_tables/artist_table_cleaned.csv"
        if os.path.exists(artist_cleaned):
            df_artists = pd.read_csv(artist_cleaned, dtype=str).fillna("")
            # Nettoyage des espaces
            df["artist_id"] = df["artist_id"].astype(str).str.strip()
            df_artists["artist_id"] = df_artists["artist_id"].astype(str).str.strip()
            valid_artist_ids = set(df_artists["artist_id"])
            df = df[df["artist_id"].isin(valid_artist_ids)]

    # ============================
    # 10) Nettoyage pour Artist_Album_Track : supprimer lignes incomplètes
    # ============================

    if is_a_a_t:
        required_cols = ["artist_id", "album_id", "track_id"]
        df = df[df[required_cols].apply(lambda row: all(c != "" for c in row), axis=1)]

    # ============================
    # 11) FK validation pour Artist_Album_Track
    # ============================

    if is_a_a_t:

        # Charger les IDs valides depuis les CSV nettoyés
        try:
            valid_artists = set(pd.read_csv("./data/output_tables/artist_table_cleaned.csv", dtype=str)["artist_id"])
            valid_albums  = set(pd.read_csv("./data/output_tables/album_table_cleaned.csv", dtype=str)["album_id"])
            valid_tracks  = set(pd.read_csv("./data/output_tables/track_table_cleaned.csv", dtype=str)["track_id"])
        except Exception as e:
            valid_artists = valid_albums = valid_tracks = set()

        # Filtrer les lignes invalides
        def valid_relation(row):
            return (
                row["artist_id"] in valid_artists and
                row["album_id"]  in valid_albums and
                row["track_id"]  in valid_tracks
            )

        df = df[df.apply(valid_relation, axis=1)]


    # ============================
    # 12) Sauvegarde
    # ============================
    if path_out is None:
        path_out = path_in.replace(".csv", "_cleaned.csv")

    df.to_csv(path_out, index=False, encoding="utf-8")
    print(f"Nettoyage terminé -> {path_out}")

    return df
    


# ======================================
# Dictionnaire des tables et relations
# ======================================

language = {
    "language_id": ("language_id", "language"),
    "language_name": ("language_name", "language"),
}

album_type = {
    "type_id": ("album_type_id", "album_type"),
    "type_name": ("album_type_name", "album_type")
}

license = {
    "license_id": ("license_id", "license"),
    "license_name": ("license_name", "license")
}

tag = {
    "tag_id": ("tag_id", "tag"),
    "tag_name": ("tag_name", "tag")
}

genre_table = {
    "genre_id": ("genre_id", "genre"),
    "genre_parent_id": ("genre_parent_id", "genre"),
    "genre_title": ("genre_title", "genre"),
    "genre_handle": ("genre_handle", "genre")
}

artist_table = {
    "artist_id": ("artist_id", "artist"),
    "artist_handle": ("artist_handle", "artist"),
    "artist_name": ("artist_name", "artist"),
    "artist_bio": ("artist_bio", "artist"),
    "artist_location": ("artist_location", "artist"),
    "artist_latitude": ("artist_latitude", "artist"),
    "artist_longitude": ("artist_longitude", "artist"),
    "artist_members": ("artist_members", "artist"),
    "artist_associated_labels": ("artist_associated_labels", "artist"),
    "artist_related_projects": ("artist_related_projects", "artist"),
    "artist_active_year_begin": ("artist_active_year_begin", "artist"),
    "artist_year_end": ("artist_active_year_end", "artist"),
    "artist_contact": ("artist_contact", "artist"),
    "artist_url": ("artist_url", "artist"),
    "artist_image_file": ("artist_image_file", "artist")
}

album_table = {
    "album_id": ("album_id", "album"),
    "album_handle": ("album_handle", "album"),
    "album_title": ("album_title", "album"),
    "album_information": ("album_information", "album"),
    "album_date_created": ("album_date_created", "album"),
    "album_date_released": ("album_date_released", "album"),
    "album_producer": ("album_producer", "album"),
    "album_engineer": ("album_engineer", "album"),
    "album_image_file": ("album_image_file", "album"),
    "album_url": ("album_url", "album"),
    "type_id": ("album_type_id", "album")
}

track_table = {
    "track_id": ("track_id", "track"),
    "track_title": ("track_title", "track"),
    "track_duration": ("track_duration", "track"),
    "track_interest": ("track_interest", "track"),
    "track_date_recorded": ("track_date_recorded", "track"),
    "track_composer": ("track_composer", "track"),
    "track_lyricist": ("track_lyricist", "track"),
    "track_publisher": ("track_publisher", "track"),
    "license": ("license_id", "track")
}

stats_echonest = {
    "track_id": ("track_id", "echonest"),
    "acousticness": ("acousticness", "echonest"),
    "danceability": ("danceability", "echonest"),
    "energy": ("energy", "echonest"),
    "instrumentalness": ("instrumentalness", "echonest"),
    "liveness": ("liveness", "echonest"),
    "speechness": ("speechness", "echonest"),
    "tempo": ("tempo", "echonest"),
    "valence": ("valence", "echonest"),
    "currency": ("currency", "echonest"),
    "hotness": ("hotness", "echonest")
}

artist_album_track = {
    "artist_id": ("artist_id", "artist_album_track"),
    "album_id": ("album_id", "artist_album_track"),
    "track_id": ("track_id", "artist_album_track")
}

track_genre = {
    "track_id": ("track_id", "track_genre"),
    "genre_id": ("track_genres", "track_genre")
}

track_genre_majoritaire = {
    "track_id": ("track_id", "track_genre_maj"),
    "genre_id": ("track_genre_top", "track_genre_maj")
}

track_language = {
    "track_id": ("track_id", "track_language"),
    "language_id": ("track_language_code", "track_language")
}

album_tag = {
    "album_id": ("album_id", "album_tag"),
    "tag_id": ("tags_album", "album_tag")
}

track_tag = {
    "track_id": ("track_id", "track_tag"),
    "tag_id": ("tags_track", "track_tag")
}

artist_tag = {
    "artist_id": ("artist_id", "artist_tag"),
    "tag_id": ("tags_artist", "artist_tag")
}


# ======================================
# Charger les CSV sources
# ======================================

CSV_SOURCES = {
    "artist_album_track": pd.read_csv("./data/code/final/artist_album_track.csv"),
    "echonest": pd.read_csv("./data/code/final/echonest_clean.csv"),
    "album_type": pd.read_csv("./data/code/final/mapping_album_type.csv"),
    "language": pd.read_csv("./data/code/final/mapping_language.csv"),
    "license": pd.read_csv("./data/code/final/mapping_license.csv"),
    "tag": pd.read_csv("./data/code/final/mapping_tags.csv"),
    "album": pd.read_csv("./data/code/final/final_albums.csv"),
    "album_tag": pd.read_csv("./data/code/final/tags_album_id.csv"),
    "artist_tag": pd.read_csv("./data/code/final/tags_artist_id.csv"),
    "artist": pd.read_csv("./data/code/final/raw_artiste_sans_balise.csv"),
    "genre": pd.read_csv("./data/code/final/raw_genres.csv"),
    "track": pd.read_csv("./data/code/final/final_track.csv"),
    "track_tag": pd.read_csv("./data/code/final/tags_track_id.csv"),
    "global": pd.read_csv("./data/code/final/track_final9.csv"),
    "track_genre": pd.read_csv("./data/code/final/track_genre_id.csv"),
    "track_language": pd.read_csv("./data/code/final/track_language_id.csv"),
    "track_genre_maj": pd.read_csv("./data/code/final/track_track_genre_maj_id.csv")
}

# ======================================
# Collecter tous les dictionnaires
# ======================================

EXCLUDED = {"CSV_SOURCES", "ALL_TABLES", "__annotations__"}

ALL_TABLES = {
    name: obj for name, obj in globals().items()
    if isinstance(obj, dict) and name not in EXCLUDED
}


# ======================================
# Générer les tables
# ======================================
def run():
    os.makedirs("./data/output_tables", exist_ok=True)

    for table_name, mapping in ALL_TABLES.items():

        print(f"\n--> Création de {table_name}.csv")
        output_df = pd.DataFrame()

        for output_col, source_info in mapping.items():

            if source_info is None:
                # Crée une colonne vide avec la bonne longueur si le DataFrame existe déjà
                if len(output_df) > 0:
                    output_df[output_col] = ""
                else:
                    # sinon crée un DataFrame temporaire d'une ligne vide
                    output_df = pd.DataFrame({output_col: [""]})
                continue

            source_col, source_file = source_info
            df_source = CSV_SOURCES[source_file]

            if source_col not in df_source.columns:
                output_df[output_col] = ""
                continue

            series = df_source[source_col]

            # Ajustement à la bonne longueur
            if len(output_df) == 0:
                output_df = pd.DataFrame({output_col: series})
            else:
                series = series.reindex(output_df.index, fill_value="")
                output_df[output_col] = series

        output_path = os.path.join("./data/output_tables", f"{table_name}.csv")
        output_df.to_csv(output_path, index=False, encoding="utf-8")

        print(f"{output_path} créée.")


    fichiers = [
        "./data/output_tables/track_table.csv",
        "./data/output_tables/track_genre.csv",
        "./data/output_tables/album_tag.csv",
        "./data/output_tables/artist_tag.csv",
        "./data/output_tables/genre_table.csv",
        "./data/output_tables/track_genre_majoritaire.csv",
        "./data/output_tables/track_language.csv",
        "./data/output_tables/artist_table.csv",
        "./data/output_tables/album_table.csv",
        "./data/output_tables/track_tag.csv",
        "./data/output_tables/stats_echonest.csv",
        "./data/output_tables/artist_album_track.csv",
        "./data_perso/final/user_artist_favorite.csv"
    ]

    for f in fichiers:
        nettoyer_csv(f)

run()