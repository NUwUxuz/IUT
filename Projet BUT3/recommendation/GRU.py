###############################
#  GRU - Gated Recurrent Unit #
###############################

import torch
import torch.nn as nn
import psycopg2
from sentence_transformers import SentenceTransformer
from sklearn.preprocessing import MinMaxScaler
import pandas as pd
import torch.nn.functional as F
from torch.nn.utils.rnn import pack_padded_sequence, pad_packed_sequence


# ---- CLASSE 1 : Gestion de la Base de Données ---- #
class HistoryRepository:
    def __init__(self, db_config):
        self.db_config = db_config
        
    def get_user_last_20_search(self, user_id):
        """
        Récupère les 20 dernières recherches effectuées par un utilisateur
        """
        try:
            connection = psycopg2.connect(**self.db_config)
            cursor = connection.cursor()
            
            query = """
                SELECT history_query
                FROM sae.Search_history
                WHERE user_id = %s
                ORDER BY timestamp DESC
                LIMIT 20;
            """
            cursor.execute(query,(user_id,))
            results = cursor.fetchall()
            connection.close()
            
            # Nettoyage (extraction des chaînes de caractères
            history = [row[0] for row in results]
            # Inversion de l'ordre pour avoir du plus ancien au plus récent
            history.reverse()
            return history
        except Exception as e:
            print(f"Erreur lors de la récupération de l'historique : {e}")
            return []
        
        
# ---- Classe 2 : Chargement Echonest ---- #
class MusicFeaturesLoader:
    def __init__(self, db_config):
        self.db_config = db_config
        self.scaler = MinMaxScaler()
        
    def load_data(self):
        """
        Charge les caractéristiques musicales depuis la base de données et applique une normalisation Min-Max
        """
        try:
            connection = psycopg2.connect(**self.db_config)
            query = """
                SELECT track_id, acousticness, danceability, energy, instrumentalness, liveness, speechness, tempo, valence
                FROM sae.Stats_echonest;
            """
            df = pd.read_sql_query(query, connection)
            connection.close()
            
            if df.empty: return None, None
            
            # Séparation des identifiants et des caractéristiques
            ids = df['track_id'].tolist()
            features = df.drop(columns=['track_id'])
            
            # Normalisation Min-Max
            features_norm = self.scaler.fit_transform(features)
            
            # Conversion en tenseur PyTorch
            tensor_db = torch.tensor(features_norm, dtype=torch.float32)
            
            return ids, tensor_db
        except Exception as e:
            print(f"Erreur lors du chargement des données musicales : {e}")
            return None, None
        
        
# ---- Classe 3 : Encodage des Recherches ---- #

class TextEncoder:
    def __init__(self):
        self.bert = SentenceTransformer('sentence-transformers/all-MiniLM-L6-v2')
        self.output_dim = 384
        
    def encode_sequence(self, text_list):
        """
        Encode une liste de chaînes de caractères en vecteurs denses
        """
        vectors = self.bert.encode(text_list, convert_to_tensor=True)
        return vectors

# ---- Classe 4 : Modèle GRU ---- #
class MusicGRUFromText(nn.Module):
    def __init__(self, input_dim, hidden_dim, output_dim):
        super(MusicGRUFromText, self).__init__()
        
        # On fait une couche de projection pour réduire la dimensionnalité
        self.projection = nn.Linear(input_dim, 128)
        self.gru = nn.GRU(input_size=128, hidden_size=hidden_dim, num_layers=1, batch_first=True)
        self.fc = nn.Linear(hidden_dim, output_dim)
        
    def forward(self, x):
        # Forme de x : (batch_size, seq_len, input_dim)
        
        #1. Projection de la dimensionnalité (Réduction)
        x = torch.relu(self.projection(x))
        
        #2. Passage dans la GRU
        out, _ = self.gru(x)
        
        #3. Récupération du dernier état caché
        out = out[:, -1, :]  # On prend la dernière sortie de la séquence
        
        #4. Sortie finale
        return self.fc(out)
    
    
# ---- Classe 5 : Intégration Complète ---- #
class RecommenderSystem:
    def __init__(self, db_config):
        self.repo = HistoryRepository(db_config)
        self.encoder = TextEncoder()
        
        #1. Chargement des données musicales
        print("Chargement des musiques...")
        self.music_loader = MusicFeaturesLoader(db_config)
        self.tracks_ids, self.music_db = self.music_loader.load_data()
        
        if self.music_db is None or len(self.tracks_ids) == 0:
            raise ValueError("La table echonest est vide ou n'a pas pu être chargée.")
        
        # Calcul de la dimension de sortie
        num_audio_features = self.music_db.shape[1]
        print(f"Base de données musicale chargée avec {len(self.tracks_ids)} pistes et {num_audio_features} caractéristiques audio.")
        
        #2. Configuration du modèle GRU
        self.model = MusicGRUFromText(input_dim=384, hidden_dim=128, output_dim=num_audio_features)
        self.model.eval()  # Mode lecture seule
        
    def recommend(self, user_id):
        """
        Génère des recommandations musicales pour un utilisateur 
        """
        # A. Récupération de l'historique des recherches
        history = self.repo.get_user_last_20_search(user_id)
        if not history:
            print("Aucun historique de recherche trouvé pour cet utilisateur.")
            return []
        
        # B. Encodage des recherches + Batching
        vectors = self.encoder.encode_sequence(history)
        input_tensor = vectors.unsqueeze(0)  # Ajout de la dimension batch
        
        # C. Prédiction du vecteur "Envie du moment"
        with torch.no_grad():
            # Le GRU sort un vecteur de taille 8 (ex : [0.5, 0.2, ..., 0.7])
            user_intent_vector = self.model(input_tensor)
            
        # D. Calcul des similarités cosinus avec echonest
        
        similarities = torch.nn.functional.cosine_similarity(user_intent_vector, self.music_db)
        
        # E. On prend les meilleurs indices
        top_k = 5
        top_indices = torch.topk(similarities, top_k).indices.tolist()
        
        # Convertion des indices en IDs de pistes
        recommended_track_ids = [self.tracks_ids[i] for i in top_indices]
        
        return recommended_track_ids
            
         
        
        