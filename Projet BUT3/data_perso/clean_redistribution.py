import pandas as pd
import hashlib
import random
import string
from datetime import datetime
import os
from pathlib import Path
import csv
import re

folder_path = "./final"

# Crée le dossier final s'il n'existe pas
if not os.path.exists(folder_path):
    os.makedirs(folder_path)

def random_hash():
    return hashlib.sha256(str(random.random()).encode()).hexdigest()

def random_string(n=8):
    return "".join(random.choices(string.ascii_lowercase + string.digits, k=n))

def interval_to_birth_year(interval):
    if pd.isna(interval):
        return None
    interval = str(interval)
    parts = interval.replace("ans", "").strip().split("-")
    if len(parts) != 2:
        return None
    try:
        min_age = int(parts[0].strip())
        max_age = int(parts[1].strip())
    except ValueError:
        return None

    # âge aléatoire dans l'intervalle
    random_age = random.randint(min_age, max_age)
    current_year = datetime.now().year
    return current_year - random_age

def filter(input_csv:str, output_csv:str, column_name:str, valeurs: list):
    """
    Parcourt un CSV et vide les cellules de la colonne 'column_name'
    si leur valeur figure dans 'valeurs' (insensible à la casse et aux espaces).
    Les lignes ne sont pas supprimées.
    """
    ensure_output_dir()
    

    with open(input_csv, 'r', newline='', encoding='utf-8') as infile, \
         open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
        
        reader = csv.DictReader(infile)
        writer = csv.DictWriter(outfile, fieldnames=reader.fieldnames)
        writer.writeheader()

        valeurs_normalisées = [v.strip().lower() for v in valeurs]

        for row in reader:
            if column_name in row:
                cell_value = row[column_name].strip().lower()
                if cell_value in valeurs_normalisées:
                    # On vide la cellule si la valeur est à exclure
                    row[column_name] = ''
            writer.writerow(row)

def explode_single_column_csv_to_ids(input_csv, output_csv, mapping_dict=None, delimiter=','):
    """
    Transforme un CSV à une colonne en un CSV plat (user, id_valeur).
    - mapping_dict (optionnel) : dict {valeur: id}
    - Si une valeur n'existe pas dans le dict, un nouvel id est créé automatiquement.
    - Retourne le dictionnaire final mis à jour.
    """
    Path(output_csv).parent.mkdir(parents=True, exist_ok=True)

    # Initialiser le dictionnaire si absent
    if mapping_dict is None:
        dict = {}
    else :
        dict = mapping_dict
    # Calculer le prochain ID disponible
    next_id = max(dict.values(), default=0) + 1

    with open(input_csv, 'r', newline='', encoding='utf-8-sig') as infile:
        reader = csv.DictReader(infile)

        # Normaliser le nom de la seule colonne
        fieldnames = [f.strip().strip('"').strip("'") for f in reader.fieldnames]
        if len(fieldnames) != 1:
            raise ValueError(f"Le fichier doit contenir une seule colonne, trouvé : {fieldnames}")

        column_name = fieldnames[0]

        with open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
            fieldnames_out = ['user_id', f'id_{column_name}']
            writer = csv.DictWriter(outfile, fieldnames=fieldnames_out)
            writer.writeheader()

            for user_id, row in enumerate(reader, start=1):
                # Trouver la clé réelle dans le dictionnaire
                for key in row.keys():
                    if key.strip().strip('"').strip("'") == column_name:
                        cell_value = row[key]
                        break
                else:
                    raise KeyError(f"Impossible de trouver la colonne {column_name} dans la ligne {row}")

                if not cell_value:
                    continue

                # Nettoyer la cellule
                cell_value = cell_value.strip().strip('"').strip("'")
                items = [item.strip() for item in cell_value.split(delimiter) if item.strip()]

                for item in items:
                    # Vérifier si la valeur a déjà un ID
                    if item not in dict:
                        dict[item] = next_id
                        next_id += 1
                    writer.writerow({'user_id': user_id, f'id_{column_name}': dict[item]})

    return dict

def ensure_output_dir(path="./output/"):
    Path(path).mkdir(parents=True,exist_ok=True)

def keep_columns(input_csv:str, output_csv:str, columns_to_keep:dict):
    ensure_output_dir()
    ()
    with open(input_csv, 'r', newline='', encoding='utf-8') as infile, \
         open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
        reader = csv.DictReader(infile)
        valid_columns = [c for c in columns_to_keep if c in reader.fieldnames]
        writer = csv.DictWriter(outfile, fieldnames=valid_columns)
        writer.writeheader()

        single_column = len(valid_columns) == 1
        col_name = valid_columns[0] if single_column else None

        for row in reader:
            if single_column:
                
                value = row[col_name].strip()
                
                
                writer.writerow({col_name: value})
               
            else:
                writer.writerow({c: row[c] for c in valid_columns})

def safe_filename(name: str) -> str:
    """Nettoie un nom de fichier en remplaçant les caractères interdits."""
    # Supprime ou remplace les caractères interdits
    name = name.strip().replace(' ', '_')
    # Enlève tous les caractères non alphanumériques sauf `_` et `-`
    name = re.sub(r'[^A-Za-z0-9_\-]', '', name)
    return name

def donne_table_association(input: str, colonne: str,
                            mapping_dict_: dict = None,
                            only_map: bool = False,
                            nom_fic: str = "tempo",
                            nom_col: str = "tempo"):
    """
    Génère les tables d'association et renvoie un mapping final
    pour la colonne choisie dans un CSV.
    """

    # Sécurisation des dictionnaires
    mapping_full_input = mapping_dict_.copy() if mapping_dict_ else {}
    mapping_final_input = mapping_dict_.copy() if mapping_dict_ else {}

    # Définition des fichiers temporaires
    tmp_file = Path(f"./final/{nom_fic}_temp.csv")
    filtered_file = Path(f"./final/{nom_fic}_fil.csv")
    id_file = Path(f"./final/{nom_fic}.csv")
    id_filtered_file = Path(f"./final/{nom_fic}_final.csv")

    nom_colonne_id = f"{nom_col}_id"

    # Étape 1 : extraction de la colonne
    keep_columns(input, tmp_file, [colonne])

    # Étape 2 : filtrage des valeurs non désirées
    filter(tmp_file, filtered_file, colonne, ["Ne souhaite pas répondre"])

    # Étape 3 : génération du mapping (ID ↔ valeurs)
    mapping_full = explode_single_column_csv_to_ids(
        filtered_file,
        id_file,
        mapping_full_input
    )

    # Étape 4 : mode only_map → filtrage sur les clés du dictionnaire fourni
    if only_map:
        allowed_keys = set(mapping_final_input.keys())
        # valeurs à retirer
        to_remove = [key for key in mapping_full.keys() if key not in allowed_keys]
        to_remove_id =[str(mapping_full[key]) for key in to_remove]
        # Filtrage du fichier d’IDs
        filter(id_file, id_filtered_file, nom_colonne_id, to_remove_id)
        # Supprimer id_file, ne garder que le fichier final filtré
        if id_file.exists():
            id_file.unlink()
        id_file = id_filtered_file  # On renomme pour garder la cohérence

    # Étape 5 : suppression des fichiers temporaires
    for f in [tmp_file, filtered_file]:
        if f.exists():
            f.unlink()

    if id_file.exists():
        df = pd.read_csv(id_file)
        if len(df.columns) >= 2:
            df.columns.values[1] = nom_colonne_id
            df.to_csv(id_file, index=False)

    # Retourne le mapping et le fichier final conservé
    return mapping_full


# --- CHARGEMENT DES CSV TABLE ---
clean = pd.read_csv("./clean/clean.csv")

colonnes = {"genre" : ["genre_top_user", "Quels genres écoutez-vous le plus ?", "genre_title"],
"period" : ["score_period", "Période(s) écoutée(s) ", "period_interval"],
"platform" : ["user_platform", "Sur quelle plateforme écoutez vous de la musique ?", "platform_name"],
"mood" : ["score_mood", "Sous quel(s) mood(s) écoutez-vous de la musique ?", "mood_name"],
"context" : ["user_context", "Dans quel contexte écoutez-vous vos musiques ?", "context_name"],
"artist" : ["user_artist_favorite", "Quel est votre artiste ou groupe préféré ?", "artist_name"],
"track" : ["track_user_favorite", "Quel est votre morceau préféré en ce moment ? ", "track_title"]}


# --------------------------------------------
# FUSION POUR LA TABLE "user"
# --------------------------------------------

user_df = pd.DataFrame()
n = len(clean)

# Email fictif et pseudo
user_df["email"] = [f"user{i}@anon.com" for i in range(n)]
user_df["user_login"] = [f"user_{i:04d}" for i in range(n)]
user_df["user_mdp"] = [hashlib.sha256(f"password{i}".encode()).hexdigest()[:32] for i in range(n)]

# Colonnes issues du CSV
if "Quel est votre sexe ?" in clean.columns:
    def encode_gender(g):
        if pd.isna(g):
            return "O"
        g = g.strip().lower()
        if g == "homme":
            return "H"
        elif g == "femme":
            return "F"
        else:
            return "O"
    
    user_df["user_gender"] = clean["Quel est votre sexe ?"].apply(encode_gender)

if "Quel est votre âge ?" in clean.columns:
    def birth_year_to_date(interval):
        year = interval_to_birth_year(interval)
        if year is None:
            return None
        return f"{year}-01-01"
    
    user_df["birth_year"] = clean["Quel est votre âge ?"].apply(birth_year_to_date)

if "Quelle est votre situation  ? " in clean.columns:
    user_df["situation_name"] = clean["Quelle est votre situation  ? "]

if "À quelle fréquence écoutez-vous de la musique ?" in clean.columns:
    user_df["frequency_interval"] = clean["À quelle fréquence écoutez-vous de la musique ?"]

# Export CSV
user_df.to_csv("./final/user.csv", index=False)
print("user.csv créé")


# --------------------------------------------
# TABLES ET RELATIONS
# --------------------------------------------

for table in colonnes:
    map = donne_table_association(
        input='./clean/clean.csv',
        colonne=colonnes[table][1],
        nom_fic=colonnes[table][0],
        nom_col=table
    )

    # trier le dictionnaire par valeurs
    sorted_map = dict(sorted(map.items(), key=lambda x: x[1]))

    # créer un DataFrame
    df = pd.DataFrame({
        f"{colonnes[table][2]}": list(sorted_map.keys())
    })

    # sauvegarder le CSV
    df.to_csv(f"./final/{table}.csv", index=False)
    print(f"{table}.csv créé")

