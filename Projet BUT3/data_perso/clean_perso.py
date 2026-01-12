import csv
import os
from pathlib import Path

# --- Fonction utilitaire ---
def ensure_output_dir(path="data_perso/output/"):
    Path(path).mkdir(parents=True, exist_ok=True)


# --- Nettoyage : suppression des lignes vides ---
def remove_empty_rows(input_csv:str, output_csv:str, column_name:str):
    ensure_output_dir()
    with open(input_csv, 'r', newline='', encoding='utf-8') as infile, \
         open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
        reader = csv.DictReader(infile)
        writer = csv.DictWriter(outfile, fieldnames=reader.fieldnames)
        writer.writeheader()
        for row in reader:
            if column_name in row and row[column_name].strip():
                writer.writerow(row)


# --- Filtrage des lignes selon une valeur ---
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


# --- Suppression de colonnes ---
def remove_columns(input_csv:str, output_csv:str, columns_to_remove:dict):
    ensure_output_dir()
    with open(input_csv, 'r', newline='', encoding='utf-8') as infile, \
         open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
        reader = csv.DictReader(infile)
        columns_to_keep = [c for c in reader.fieldnames if c not in columns_to_remove]
        writer = csv.DictWriter(outfile, fieldnames=columns_to_keep)
        writer.writeheader()
        for row in reader:
            writer.writerow({c: row[c] for c in columns_to_keep})


# --- Conservation de colonnes spécifiques ---
def keep_columns(input_csv:str, output_csv:str, columns_to_keep:dict):
    ensure_output_dir()
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
        mapping_dict = {}

    # Calculer le prochain ID disponible
    next_id = max(mapping_dict.values(), default=0) + 1

    with open(input_csv, 'r', newline='', encoding='utf-8-sig') as infile:
        reader = csv.DictReader(infile)

        # Normaliser le nom de la seule colonne
        fieldnames = [f.strip().strip('"').strip("'") for f in reader.fieldnames]
        if len(fieldnames) != 1:
            raise ValueError(f"Le fichier doit contenir une seule colonne, trouvé : {fieldnames}")

        column_name = fieldnames[0]
        print(f"[DEBUG] Colonne détectée : {column_name!r}")

        with open(output_csv, 'w', newline='', encoding='utf-8') as outfile:
            fieldnames_out = ['id_user', f'id_{column_name}']
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
                    if item not in mapping_dict:
                        mapping_dict[item] = next_id
                        next_id += 1
                        print(f"[DEBUG] Nouvelle valeur mappée : {item!r} -> ID {mapping_dict[item]}")
                    writer.writerow({'id_user': user_id, f'id_{column_name}': mapping_dict[item]})

    return mapping_dict


# --- EXEMPLES D'UTILISATION --- #

# Suppression des lignes où la réponse à une question est vide
# remove_empty_rows('input.csv', 'output.csv', 'Souhaitez-vous répondre à des questions plus personnelles ?')

# Filtrage : garder seulement ceux qui ont répondu "oui"
# filter('input.csv', 'refuse.csv', 'Souhaitez-vous répondre à des questions plus personnelles ?', "non")

# Colonnes à supprimer (fausses questions ou inutiles)
l_fausse_question = [
    "Souhaitez vous accepter le traitement de vos données ?",
    "Influence culturelle",
    " [Ligne 1]",
    "Colonne 12",
    "Reconnaissez-vous avoir lu et accepté ces conditions.",
    "Colonne 20",
    "dzdzd",
    "Souhaitez-vous envoyer le questionnaire ?",
    "Colonne 16",
    "Colonne 17",
    "Colonne 15"
]

# Suppression de colonnes inutiles
remove_columns('data_perso/input.csv', 'data_perso/output/output.csv', l_fausse_question)

# Extraction des questions ouvertes
l_question_ouverte = [
    "Quel est votre morceau préféré en ce moment ? ",
    "Quel est votre artiste ou groupe préféré ?"
]
# keep_columns('data_perso/input.csv', 'data_perso/output/ouvert.csv', l_question_ouverte)

# Extraction des choix multiples
l_choix_multiple = [
    "Quels genres écoutez-vous le plus ?",
    "Période(s) écoutée(s) ",
    "Langue(s) écoutée(s)",
    "Sur quelle plateforme écoutez vous de la musique ?",
    "Sous quel(s) mood(s) écoutez-vous de la musique ?",
    "Dans quel contexte écoutez-vous vos musiques ?"
]
keep_columns('data_perso/input.csv', 'data_perso/output/multiple.csv', l_choix_multiple)



# --- TON CAS FINAL --- #

l_choix_multiple_periode = ["Période(s) écoutée(s) "]

# Étape 1 : on extrait uniquement cette colonne
keep_columns('data_perso/input.csv','data_perso/output/multiple_periode_temp.csv',l_choix_multiple_periode)

# Étape 2 : on filtre pour enlever les "Ne souhaite pas répondre"
filter(
    'data_perso/output/multiple_periode_temp.csv', 'data_perso/output/multiple_periode.csv', "Période(s) écoutée(s) ",["Ne souhaite pas répondre"]
)

# Étape 3 : (optionnel) supprimer le fichier temporaire
os.remove('data_perso/output/multiple_periode_temp.csv')


explode_single_column_csv_to_ids(
    'data_perso/output/multiple_periode.csv',
    'data_perso/output/multiple_periode_flat.csv'
)

mapping = {
    "2020’s": 1,
    "2010’s": 2
}

mapping_final = explode_single_column_csv_to_ids(
    'data_perso/output/multiple_periode.csv',
    'data_perso/output/multiple_periode_flat_ids.csv',
    mapping_dict=mapping
)

# print(mapping_final)

à_enlever = ['7','8']



# Étape 2 : on filtre pour enlever les "Ne souhaite pas répondre,'musique classique'"
filter('data_perso/output/multiple_periode_flat_ids.csv', 'data_perso/output/multiple_periode_flat_ids_temp.csv', 'id_Période(s) écoutée(s)',à_enlever)

# Étape 3 : (optionnel) supprimer le fichier temporaire
os.remove('data_perso/output/multiple_periode_flat_ids_temp.csv')





#--------------clean colonne------------
colonnes = ["Quels genres écoutez-vous le plus ?",
 "Période(s) écoutée(s) ",
 "Langue(s) écoutée(s)",
 "Sur quelle plateforme écoutez vous de la musique ?",
 "À quelle fréquence écoutez-vous de la musique ?",
 "Souhaitez-vous répondre à des questions plus personnelles ?",
 "Quel est votre âge ?",
 "Sous quel(s) mood(s) écoutez-vous de la musique ?",
 "Quelle est la durée moyenne des sons que vous écoutez ?",
 "Dans quel contexte écoutez-vous vos musiques ?",
 "Quel est votre artiste ou groupe préféré ?",
 "Quel est votre morceau préféré en ce moment ? ",
 "La musique influence-t-elle votre humeur ?",
 "Quelle est votre situation  ? ",
 "Quel est votre sexe ?"]


import re
import os

def safe_filename(name: str) -> str:
    """Nettoie un nom de fichier en remplaçant les caractères interdits."""
    # Supprime ou remplace les caractères interdits
    name = name.strip().replace(' ', '_')
    # Enlève tous les caractères non alphanumériques sauf `_` et `-`
    name = re.sub(r'[^A-Za-z0-9_\-]', '', name)
    return name

for col in colonnes:
    col_nom = safe_filename(str(col))

    nom_fichier_tmp = f"data_perso/table/{col_nom}_temp.csv"
    nom_fichier = f"data_perso/table/{col_nom}.csv"
    
    keep_columns("data_perso/output/output.csv", nom_fichier_tmp, col)
    
    filter(
        nom_fichier_tmp,
        nom_fichier,
        col,
        ["Ne souhaite pas répondre"]
    )
  

