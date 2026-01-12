import numpy as np
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity



def filtrage_collaboratif():

    # User ID
    # Sexe
    # Age
    # Situation
    # Genre Top
    # Stats User ?



    # Executer creation_test_csv.py avant de lancer ce script pour obtenir les CSV récupérer ci-dessous
    info_user = pd.read_csv("recommendation_ethan/test_data_user_big.csv")
    info_genre_top = pd.read_csv("recommendation_ethan/test_data_genre_big.csv")

    info_user_important = info_user[['user_id','user_gender','birth_year','situation']]





    # Calcul l'interval de l'age d'une personne par intervalle de 10 (2025-2016,2015-2006...)
    def calcul_interval_age(birth_year):
        return (2025-birth_year) // 10

    # Test
    # calcul_interval_age(1999)



    # Remplacement de l'âge par la tranche d'âge
    info_user_important['age_interval'] = info_user_important['birth_year'].apply(calcul_interval_age)
    info_user_important = info_user_important.drop(columns=['birth_year'])


    # Conversion des données qualitative en données quantitative
    info_user_important['user_gender'] = info_user_important['user_gender'].map({'h': 0, 'f': 1, 'a': 2})

    info_user_important['situation'] = info_user_important['situation'].map({'etudiant': 0, 'recherche': 1, 'emploi': 2, 'retraite': 3})


    # print(df)


    # Créer une matrice avec le score de tous les genres des utilisateurs
    def creer_vecteur_genre(matrice):
        matrice_genre = matrice.pivot_table(
            index="user_id",
            columns="genre_id",
            values="genre_rate",
            fill_value=0
        )
        return matrice_genre
    vecteur_genre = creer_vecteur_genre(info_genre_top)

    # Fusion des matrices user et genre
    df = pd.merge(info_user_important, vecteur_genre, on='user_id', how='left')


    df = df.drop(columns=['user_id'])
    print(vecteur_genre)


    # Matrice avec toutes les infos users, utilise plus d'info, mais est moins précis
    matrice_similarite_all = cosine_similarity(df)
    print("Similarité tous attributs")
    print(matrice_similarite_all)
    print("\n")

    # Matrice avec seulement les genres, est plus précis mais ignore les autres caractéristiques
    matrice_similarite_genre = cosine_similarity(vecteur_genre)
    print("Similarité genres écoutés")
    print(matrice_similarite_genre)
    print("\n")





    # Multiplie chaque valeurs des 2 matrices entre elles, pour avoir un poids plus élevé sur les genres écoutés (! pas une multiplication de matrice !)
    # Très peu efficace avec beaucoup de genres, ne sera pas utilisé
    def matrice_finale1(m1,m2):
        matrice = np.zeros((len(m1),len(m1)))
        for i in range (len(m1)):
            for j in range (len(m1[0])):
                matrice[i][j] = m1[i][j] * m2[i][j]
        return matrice

    matrice_similarite_finale_1 = matrice_finale1(matrice_similarite_all,matrice_similarite_genre)
    print("Similarité matrices all et genres multiplié")
    print(matrice_similarite_finale_1)
    print("\n")


    # Donne un poids plus élevé au genres écoutés directement, préféré à la méthode précédente
    vecteur_genre.loc[:, vecteur_genre.columns != "user_id"] = vecteur_genre.loc[:, vecteur_genre.columns != "user_id"] * len(vecteur_genre)     # df.loc permet de manipuler les lignes et colonnes d'un dataframe, ici on exclut la colonne user_id
    merge_matrice = pd.merge(info_user_important, vecteur_genre, on='user_id', how='left')
    merge_matrice = merge_matrice.drop(columns=['user_id'])     # On drop la colonne user_id car elle est aussi utilisé lorsque l'on réalise la similarité cosine, et avec un ID de 100 ou plus, son influence est énorme
    matrice_similarite_finale_2 = cosine_similarity(merge_matrice)
    print("Similarité poids genres plus élevé")
    print(matrice_similarite_finale_2)




    # Détermine tous les utilisateurs qui sont similaire, renvoit une matrice avec tous les utilisateurs similaire à un utilisateur
    def trouve_similarite_eleve(matrice):
        matrice_temp = matrice
        liste_similarite = [[] for _ in range(len(matrice))]
        for i in range (len(matrice_temp)):
            for j in range (len(matrice_temp[0])):
                if matrice_temp[i][j] >= 0.8 and i != j:
                    print("Similarité élevée entre les users " + str(i+1) + " et " + str(j+1))
                    liste_similarite[i].append(j+1)
            print("\n")
        return liste_similarite

    finit = trouve_similarite_eleve(matrice_similarite_finale_2)
    # print(finit)

    dico_finit = {i: similarite for i, similarite in enumerate(finit) if similarite}

    # Affichage brute
    print(dico_finit)

    # Affichage propre
    for user, similaire in dico_finit.items():
        print("Utilisateur ", user + 1, " : ", similaire)


filtrage_collaboratif()




# The Clamato Juice Incident

