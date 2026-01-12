import psycopg
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.neighbors import NearestNeighbors
import numpy as np

def connect_db():
    """
    Se connecte à la base de données PostgreSQL.
    """
    try:
        conn = psycopg.connect(
            host="127.0.0.1",
            dbname="sae5",
            user="postgres",
            password="marcheSTP"
        )
        return conn
    except psycopg.OperationalError as e:
        print(f"Erreur de connexion à la base de données : {e}")
        return None

def get_track_features(conn):
    """
    Récupère les caractéristiques audio des morceaux depuis stats_echonest.
    """
    sql = """
    SELECT
        t.track_title,
        s.track_id,
        s.danceability,
        s.energy,
        s.instrumentalness,
        s.liveness,
        s.speechness,
        s.tempo,
        s.valence
    FROM sae.stats_echonest s
    JOIN sae.Track t ON s.track_id = t.track_id
    """
    try:
        tracks_df = pd.read_sql(sql, conn)
        return tracks_df
    except (Exception, psycopg.Error) as error:
        print(f"Erreur lors de la récupération des stats musiques : {error}")
        return pd.DataFrame()

def get_user_profile(conn, user_id):
    """
    Récupère le profil d'affinité d'un utilisateur spécifique.
    """
    sql = """
    SELECT
        danceability_affinity,
        energy_affinity,
        instrumentalness_affinity,
        liveness_affinity,
        speechness_affinity,
        tempo_affinity,
        valence_affinity
    FROM sae.stats_user
    WHERE user_id = %s
    """
    try:
        user_df = pd.read_sql(sql, conn, params=(user_id,))
        return user_df
    except (Exception, psycopg.Error) as error:
        print(f"Erreur lors de la récupération du profil utilisateur {user_id} : {error}")
        return pd.DataFrame()

def recommend_content_based(user_id, user_df, tracks_df, model, scaler):
    """
    Recommande des morceaux similaires aux goûts de l'utilisateur (Content-Based).
    """
    if user_df.empty:
        print(f"L'utilisateur {user_id} n'a pas de profil.")
        return

    # 1. Construire le vecteur utilisateur dans le même ordre que les tracks
    user_row = user_df.iloc[0]
    user_vector = [
        user_row['danceability_affinity'],
        user_row['energy_affinity'],
        user_row['instrumentalness_affinity'],
        user_row['liveness_affinity'],
        user_row['speechness_affinity'],
        user_row['tempo_affinity'],
        user_row['valence_affinity']
    ]

    # 2. Normaliser ce vecteur avec le MÊME scaler que les musiques
    user_vector_scaled = scaler.transform([user_vector])

    # 3. Trouver les morceaux les plus proches de ce vecteur idéal
    distances, indices = model.kneighbors(user_vector_scaled, n_neighbors=10)

    neighbor_indices = indices.flatten()
    neighbor_distances = distances.flatten()
    
    print(f"\n--- Recommandations (Content-Based) pour l'utilisateur {user_id} ---")
    print("Basé sur la similarité mathématique entre ses goûts et les musiques :\n")

    for i, idx in enumerate(neighbor_indices):
        track = tracks_df.iloc[idx]
        dist = neighbor_distances[i]
        print(f"{track['track_id']} - {track['track_title']} (Distance: {dist:.4f})")
    return track

def main(id_user):
    conn = connect_db()
    if conn is None:
        return

    # 1. Obtenir les stats des musiques
    print("Chargement des données musicales...")
    tracks_df = get_track_features(conn)
    if tracks_df.empty:
        print("Impossible de récupérer les stats des musiques.")
        conn.close()
        return

    # Colonnes utilisées pour le calcul de similarité
    feature_cols = ['danceability', 'energy', 'instrumentalness', 'liveness', 'speechness', 'tempo', 'valence']
    track_features = tracks_df[feature_cols]

    # 2. Normaliser les données des MUSIQUES et entraîner le modèle
    scaler = StandardScaler()
    tracks_scaled = scaler.fit_transform(track_features)
    knn_model = NearestNeighbors(n_neighbors=10, algorithm='brute', metric='euclidean')
    knn_model.fit(tracks_scaled)

    # 3. Faire la recommandation pour l'utilisateur 1
    target_user_id = id_user
    user_profile_df = get_user_profile(conn, target_user_id)
    res = recommend_content_based(target_user_id, user_profile_df, tracks_df, knn_model, scaler)

    conn.close()

    return res

if __name__ == '__main__':
    main(1)