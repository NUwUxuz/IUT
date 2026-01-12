import psycopg
import pandas as pd
from sklearn.preprocessing import StandardScaler
from sklearn.neighbors import NearestNeighbors
import numpy as np

def connect_db():
    """
    Se connecte à la base de données PostgreSQL.
    Remplacez les valeurs ci-dessous par vos informations de connexion.
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

def get_user_profiles(conn):
    """
    Récupère et construit les profils statistiques pour chaque utilisateur.
    Utilise la table pré-calculée Stats_user.
    """
    sql = """
    SELECT
        user_id,
        danceability_affinity,
        energy_affinity,
        instrumentalness_affinity,
        liveness_affinity,
        speechness_affinity,
        tempo_affinity,
        valence_affinity,
        currency_affinity,
        hotness_affinity
    FROM
        sae.stats_user
    """
    try:
        profiles_df = pd.read_sql(sql, conn)
        profiles_df.fillna(0, inplace=True)
        return profiles_df
    except (Exception, psycopg.Error) as error:
        print("Erreur lors de la récupération des profils utilisateur : ", error)
        return pd.DataFrame()

def get_user_recent_listens(conn, user_id, limit=10):
    """
    Récupère les écoutes récentes d'un utilisateur.
    """
    sql = """
    SELECT t.track_title
    FROM sae.Track_User_favorite tuf
    JOIN sae.Track t ON tuf.track_id = t.track_id
    WHERE tuf.user_id = %s
    ORDER BY tuf.added_at DESC
    LIMIT %s;
    """
    try:
        tracks_df = pd.read_sql(sql, conn, params=(user_id, limit))
        return tracks_df['track_title'].tolist()
    except (Exception, psycopg.Error) as error:
        print(f"Erreur lors de la récupération des écoutes de l'utilisateur {user_id}: {error}")
        return []

def recommend_tracks_for_user(user_id, user_profiles_df, model, scaler, conn):
    """
    Recommande des morceaux pour un utilisateur donné en se basant sur ses voisins.
    """
    if user_id not in user_profiles_df['user_id'].values:
        print(f"L'utilisateur {user_id} n'a pas de profil.")
        return

    # Préparer les données pour la prédiction
    profile_features = user_profiles_df.drop('user_id', axis=1)
    
    # Trouver l'index de l'utilisateur dans le DataFrame
    try:
        user_index = user_profiles_df.index[user_profiles_df['user_id'] == user_id][0]
    except IndexError:
        print(f"Impossible de trouver l'index pour l'utilisateur {user_id}")
        return

    # Transformer le profil de l'utilisateur cible
    user_profile_scaled = scaler.transform([profile_features.iloc[user_index]])

    # Trouver les k-plus proches voisins (k=5 ici, le premier est l'utilisateur lui-même)
    distances, indices = model.kneighbors(user_profile_scaled, n_neighbors=5)

    # Exclure l'utilisateur lui-même (qui est toujours le plus proche)
    neighbor_indices = indices.flatten()[1:]
    
    print(f"Utilisateurs les plus similaires à l'utilisateur {user_id}:")
    recommendations = set()

    for neighbor_idx in neighbor_indices:
        neighbor_id = user_profiles_df.iloc[neighbor_idx]['user_id']
        print(f"- Voisin ID: {neighbor_id}")

        # Récupérer les écoutes récentes du voisin
        neighbor_tracks = get_user_recent_listens(conn, neighbor_id)
        if neighbor_tracks:
            print(f"  Morceaux écoutés par le voisin {neighbor_id}: {', '.join(neighbor_tracks)}")
            for track in neighbor_tracks:
                recommendations.add(track)


    print("\n--- Recommandations finales pour l'utilisateur", user_id, "---")
    if recommendations:
        for track in recommendations:
            print(f"- {track}")
    else:
        print("Aucune recommandation à faire pour le moment.")

    return recommendations

def main(id_user):
    """
    Fonction principale pour exécuter l'algorithme de recommandation.
    """
    conn = connect_db()
    if conn is None:
        return

    # 1. Obtenir les profils de tous les utilisateurs
    user_profiles_df = get_user_profiles(conn)
    if user_profiles_df.empty:
        print("Aucun profil utilisateur n'a pu être généré. Arrêt du script.")
        conn.close()
        return

    print("Profils utilisateurs générés :")
    print(user_profiles_df.head())

    # 2. Préparer les données pour le modèle k-NN
    profile_features = user_profiles_df.drop('user_id', axis=1)

    # 3. Normaliser les données
    scaler = StandardScaler()
    profiles_scaled = scaler.fit_transform(profile_features)

    # 4. Entraîner le modèle k-NN
    knn_model = NearestNeighbors(n_neighbors=5, algorithm='brute', metric='euclidean')
    knn_model.fit(profiles_scaled)

    # 5. Faire une recommandation pour un utilisateur cible (ex: user_id = 1)
    target_user_id = id_user 
    res = recommend_tracks_for_user(target_user_id, user_profiles_df, knn_model, scaler, conn)

    conn.close()

    return res

if __name__ == '__main__':
    main(1)