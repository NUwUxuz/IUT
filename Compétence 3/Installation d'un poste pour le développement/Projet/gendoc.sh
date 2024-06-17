# Initialisation des variables
is_major=0
is_minor=0
is_build=0

# Vérification des paramètres
if [ $# -ne 1 ]; then
    >&2 echo "ERREUR. Paramètre non valide."
    >&2 echo "$0 --[major|minor|build]"
    exit 1
elif [ "$1" == "--major" ]; then
    is_major=1
elif [ "$1" == "--minor" ]; then
    is_minor=1
elif [ "$1" == "--build" ]; then
    is_build=1
else
    >&2 echo "ERREUR. Paramètre non valide."
    >&2 echo "$0 --[major|minor|build]"
    exit 1
fi

# Récupération des informations dans le fichier config
source config

# Récupération du nom du client
tmps="$CLIENT"

# Remplacement des espaces par des underscores
nom_client=$(tr " " "_" <<< "$tmps")

# Récupération de la date
IFS='.' read -r -a version_array <<< "$VERSION"

# Récupération des informations de version
major=${version_array[0]}
minor=${version_array[1]}
build=${version_array[2]}

# Incrémentation de la version
if [ $is_major -eq 1 ]; then
    major=$((major+1))
elif [ $is_minor -eq 1 ]; then
    minor=$((minor+1))
elif [ $is_build -eq 1 ]; then
    build=$((build+1))
fi

# Création de la nouvelle version
version_finale="${major}.${minor}.${build}"

# Remplacement de la version dans le fichier config
sed -i "s/VERSION=.*/VERSION=$version_finale/" config

# Création de l'archive Docker
docker volume create sae103;
docker run -d --name sae103-forever -v sae103:/work clock;

# Copie des fichiers dans le conteneur
docker cp ./bin/* sae103-forever:/work;
docker cp ./*.c sae103-forever:/work;
docker cp ./*.C sae103-forever:/work;
docker cp ./*.md sae103-forever:/work; 
docker cp ./config sae103-forever:/work;

echo "Conversion en HTML en cours..."
# Génération de la documentation utilisateur
docker run --rm -v sae103:/work -ti sae103-php php -f /work/gendoc-user.php > doc-user-${version_finale}.html
docker container cp doc-user-${version_finale}.html sae103-forever:/work
rm doc-user-${version_finale}.html

# Génération de la documentation technique
docker run --rm -v sae103:/work -ti sae103-php php -f /work/gendoc-tech.php > doc-tech-${version_finale}.html
docker container cp doc-tech-${version_finale}.html sae103-forever:/work
rm doc-tech-${version_finale}.html

# Conversion en PDF des fichiers HTML
echo "Conversion en PDF en cours..."
docker run --rm -ti -v sae103:/work sae103-html2pdf "html2pdf doc-tech-${version_finale}.html doc-tech-${version_finale}.pdf"
docker run --rm -ti -v sae103:/work sae103-html2pdf "html2pdf doc-user-${version_finale}.html doc-user-${version_finale}.pdf"

# Compression dans une archive des fichiers PDF
tar czvf archive_doc_user_et_tech.tar.gz doc-tech-${version_finale}.pdf doc-user-${version_finale}.pdf

# Copie de l'archive en local
docker container cp sae103-forever:/work/archive_doc_user_et_tech.tar.gz ./

# Suppression des fichiers temporaires
tar tzvf archive_doc_user_et_tech.tar.gz

# Arrêt du conteneur Docker
docker stop sae103-forever

# Suppression des fichiers temporaires et du volume Docker
docker volume rm sae103
