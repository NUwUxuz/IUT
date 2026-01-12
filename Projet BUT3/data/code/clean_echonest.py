import pandas as pd
import re

def clean_html(text):
    if isinstance(text, str):
        return re.sub(r'<.*?>', '', text)
    return text

# Lecture du fichier echonest.csv avec multi-index sur les colonnes
df = pd.read_csv("./data/data_base/raw_echonest.csv", header=[0, 1, 2])

# Fusion des noms de colonnes multi-index en une seule chaîne
df.columns = [
    '_'.join([str(a), str(b), str(c)]).strip('_').replace(' ', '_')
    for a, b, c in df.columns
]

# Nettoyage du HTML dans toutes les cellules
for col in df.columns:
    df[col] = df[col].map(clean_html)

# Suppression des lignes vides et des doublons
df = df.dropna(how='all')
df = df.drop_duplicates()
print(df.columns)
# Liste des colonnes utiles à conserver (à adapter selon ton besoin)



cols_utiles = [
    
    'Unnamed:_0_level_0_Unnamed:_0_level_1_Unnamed:_0_level_2',
    "echonest_audio_features_acousticness",
    "echonest_audio_features_danceability",
    "echonest_audio_features_energy",
    "echonest_audio_features_instrumentalness",
    "echonest_audio_features_liveness",
    "echonest_audio_features_speechiness",
    "echonest_audio_features_tempo",
    "echonest_audio_features_valence",
    "chonest_ranks_song_currency_rank",
    "echonest_ranks_song_hotttnesss_rank"
    ]

# cols_utiles = df.columns
# On ne garde que les colonnes existantes dans le fichier
cols_existantes = [c for c in cols_utiles if c in df.columns]
echonest = df[cols_existantes].copy()

# Supprime les lignes où une des colonnes demandées est vide
echonest = echonest.dropna(subset=cols_existantes)

# Nettoyage supplémentaire si besoin
for col in echonest.columns:
    echonest[col] = echonest[col].map(clean_html)
echonest = echonest.drop_duplicates(subset=['Unnamed:_0_level_0_Unnamed:_0_level_1_Unnamed:_0_level_2'])

# Renommage des colonnes pour un nom plus lisible
echonest = echonest.rename(columns={
    'Unnamed:_0_level_0_Unnamed:_0_level_1_Unnamed:_0_level_2': 'track_id',
    "echonest_audio_features_acousticness":"acousticness" , 
    "echonest_audio_features_danceability":"danceability",
    "echonest_audio_features_energy":"energy",
    "echonest_audio_features_instrumentalness":"instrumentalness",
    "echonest_audio_features_liveness": "liveness",
    "echonest_audio_features_speechiness":"speechness",
    "echonest_audio_features_tempo": "tempo",
    "echonest_audio_features_valence":"valence",
    'echonest_ranks_song_currency_rank': 'currency',
    'echonest_ranks_song_hotttnesss_rank': 'hotness'
    })

# Sauvegarde du fichier nettoyé
echonest.to_csv("./data/code/final/echonest_clean.csv", index=False)
print(echonest.columns)