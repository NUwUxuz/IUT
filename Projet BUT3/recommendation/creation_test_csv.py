# Générer par Copilot
# A EXECUTER AVANT LE CALCUL DE SIMILARITE POUR OBTENIR CSV, UTILISE POUR filtrage_collaboratif.py


import pandas as pd
import numpy as np

# Paramètres
n_users = 139   # nombre d'utilisateurs

# Génération des données utilisateurs
users = pd.DataFrame({
    "user_id": range(1, n_users+1),
    "liked_tracks": np.random.randint(1, 100, size=n_users),
    "email": ["user"+str(i)+"@test.com" for i in range(1, n_users+1)],
    "image": ["img"+str(i)+".jpg" for i in range(1, n_users+1)],
    "pseudo": ["pseudo"+str(i) for i in range(1, n_users+1)],
    "user_login": ["login"+str(i) for i in range(1, n_users+1)],
    "user_mdp": ["mdp"+str(i) for i in range(1, n_users+1)],
    "user_gender": np.random.choice(["h","f"], size=n_users),
    "birth_year": np.random.randint(1970, 2010, size=n_users),
    "created_at": pd.date_range("2020-01-01", periods=n_users, freq="D"),
    "situation": np.random.choice(["etudiant","recherche","emploi","retraite"], size=n_users),
    "frequency_interval": np.random.choice(["quotidien","hebdo","mensuel"], size=n_users),
    "last_calculated": pd.date_range("2021-01-01", periods=n_users, freq="D")
})

# Sauvegarde
users.to_csv("recommendation_ethan/test_data_user_big.csv", index=False)



# Paramètres
n_genres = [i for i in range(20)]  # liste de genres
rows = []

for user in range(1, n_users+1):
    chosen = np.random.choice(n_genres, size=np.random.randint(2,5), replace=False)
    rates = np.random.rand(len(chosen))
    rates = rates / rates.sum()  # normalisation à 1

    for g, r in zip(chosen, rates):
        rows.append([user, g, round(r, 3)])


user_genres = pd.DataFrame(rows, columns=["user_id","genre_id","genre_rate"])

# Sauvegarde

user_genres.to_csv("recommendation_ethan/test_data_genre_big.csv", index=False)
