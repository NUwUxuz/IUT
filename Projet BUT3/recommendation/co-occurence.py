#################
#  Co-Occurence #
#################

import psycopg2
import pandas as pd
from collections import Counter

# ---- CLASSE 1 : Récupération de l'Historique Utilisateur (User Input Loader) ---- #

class UserInputLoader:
    def __init__(self, db_config):
        self.db_config = db_config
        
        def get_user_recent_tracks(self, user_id, limit=50):
            """
            Récupère les ID des dernières musiques écoutées ou aimées par l'utilisateur.
            Ce sont les "Graines" (Seeds) pour la co-occurrence.
            """
            try:
                connection = psycopg2.connect(**self.db_config)
                query = """
                    SELECT track_id FROM sae.User_Track_Listening 
                    WHERE user_id = %s 
                    ORDER BY nb_listening DESC LIMIT %s;
                """
                cursor = connection.cursor()
                cursor.execute(query, (user_id, limit))
                track_ids = [row[0] for row in cursor.fetchall()]
                connection.close()
                return track_ids

            except Exception as e:
                print(f"Erreur historique : {e}")
                return []
            
# ---- CLASSE 2 : Le Modèle de Co-occurrence (Co-Occurence Engine)  ---- #

class CooccurrenceModel:
    def __init__(self, db_config):
        self.db_config = db_config
    
    def predict_related_items(self, track_ids, exclude_user_id=None):
        """
        Simule le 'Forward' : Prend des IDs en entrée -> Sort des IDs liés.
        Logique : "Qui a écouté ces sons ? Qu'ont-ils écouté d'autre ?"
        """
        if not track_ids:
            return pd.DataFrame()

        try:
            conn = psycopg2.connect(**self.db_config)
            
            query = """
            WITH SeedTracks AS (
                SELECT unnest(%s::int[]) as track_id
            ),
            -- 1. Trouve les utilisateurs qui ont écouté ces sons (Témoins)
            TémoinsUsers AS (
                SELECT DISTINCT utl.user_id
                FROM sae.User_Track_Listening utl
                JOIN SeedTracks st ON utl.track_id = st.track_id
                WHERE utl.user_id != %s  -- On exclut l'utilisateur courant pour éviter le biais
            ),
            -- 2. Trouve ce que les témoins ont écouté d'autre (Candidats)
            Candidats AS (
                SELECT utl.track_id
                FROM sae.User_Track_Listening utl
                JOIN TémoinsUsers wu ON utl.user_id = wu.user_id
                WHERE utl.track_id NOT IN (SELECT track_id FROM SeedTracks) -- Pas les sons d'origine
            )
            -- 3. Score de co-occurrence
            SELECT 
                c.track_id,
                COUNT(*) as cooccurrence_score
            FROM Candidats c
            GROUP BY c.track_id
            ORDER BY cooccurrence_score DESC
            LIMIT 20;
            """
            
            params = (track_ids, exclude_user_id if exclude_user_id else -1)
            df = pd.read_sql_query(query, conn, params=params)
            conn.close()
            return df
        
        except Exception as e:
            print(f"Erreur modèle co-occurrence : {e}")
            return pd.DataFrame()
        
# ---- Classe 3 : Enrichissement des Résultats (Dense Layer) ---- #
class TrackMetadataLoader:
    def __init__(self, db_config):
        self.db_config = db_config       
    
    def add_names_and_artists(self, recommendations_df):
        """
        Ajoute les noms des pistes et des artistes aux recommandations. 
        """
        if recommendations_df.empty:
            return recommendations_df
        track_ids = tuple(recommendations_df['track_id'].tolist())
        
        try:
            conn = psycopg2.connect(**self.db_config)
            query = """
                SELECT track_id, track_name, artist_name
                FROM sae.Tracks_Metadata
                WHERE track_id IN %s;
            """
            df_metadata = pd.read_sql_query(query, conn, params=(track_ids,))
            conn.close()
            
            return pd.merge(recommendations_df, df_metadata, on='track_id')
        
        except Exception as e:
            print(f"Erreur métadonnées : {e}")
            return recommendations_df
            

# ---- CLASSE 4 : Système Complet (Recommentations) ---- #
class RecommenderSystem:
    def __init__(self, db_config):
        
        self.db_config = db_config
        
        # Instanciation des modules
        self.input_loader = UserInputLoader(db_config)
        self.model = CooccurrenceModel(db_config)
        self.metadata = TrackMetadataLoader(db_config)
        
    def recommend(self, user_id):
        print(f"--- Démarrage Recommandation Co-occurrence pour User {user_id} ---")
        
        # 1. Input : Qu'est-ce que l'utilisateur aime ?
        seed_tracks = self.input_loader.get_user_recent_tracks(user_id)
        print(f"-> Basé sur {len(seed_tracks)} titres de son historique.")
        
        if not seed_tracks:
            return "Pas assez d'historique (Cold Start)."
        
        # 2. Model : Prédiction des items liés
        raw_recos = self.model.predict_related_items(seed_tracks, exclude_user_id=user_id)
        
        if raw_recos.empty:
            return "Aucune co-occurrence trouvée (Sons trop rares ?)."
            
        # 3. Post-Process : Ajout des noms pour l'affichage
        final_recos = self.metadata.add_names_and_artists(raw_recos)
        
        # 4. Nettoyage final (Tri)
        final_recos = final_recos.sort_values(by='cooccurrence_score', ascending=False)
        
        return final_recos
    
# ---- TEST ---- #
if __name__ == "__main__":
    DB_CONFIG = {
        'dbname': 'sae', 'user': 'postgres', 
        'password': 'admin', 'host': 'localhost', 'port': '5432'
    }

    recsys = RecommenderSystem(DB_CONFIG)
    
    # Lancement
    results = recsys.recommend(user_id=1)
    print(results)