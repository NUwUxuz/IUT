            /////////////////////////////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////// GESTION CHECKBOX //////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////

            function checkOnlyOne(checkbox) {
                // Sélectionner toutes les cases à cocher dans le formulaire
                var checkboxes = document.querySelectorAll('#une-relief input[type="checkbox"]');
                
                // Parcourir chaque case à cocher
                checkboxes.forEach(function(item) {
                    if (item !== checkbox) {
                        // Désélectionner les autres cases à cocher
                        item.checked = false;
                    }
                });
            }

            ////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////
            // document.addEventListener('DOMContentLoaded', function() {

                // Gestion de la catégorie et de l'affichage des champs en fonction des catégories
                const categorieSelect = document.getElementById('categorie-offre');
                const dureeContainer = document.getElementById('duree-container');
                const ageContainer = document.getElementById('age-container');
                const langueContainer = document.getElementById('langue-container');
                const capaciteContainer = document.getElementById('capacite-container');
                const nbattractionContainer = document.getElementById('nbattraction-container');
                const carteImageContainer = document.getElementById('map-image');
                const categoriePrixContainer = document.getElementById('categorie-prix-container');
                const repasContainer = document.getElementById('repas-container');
                const labelElement = document.querySelector('#map-image label');
                const repasMapContainer = document.getElementById('repas-map');
                const prestationIncluseContainer = document.getElementById('pinclus-container');
                const prestationExcluseContainer = document.getElementById('pexclus-container');

                // Récupérer tous les éléments avec la classe 'tag'
                const tags = document.querySelectorAll('#tags-items-container label');
                const tagsToHideRestauration = ['Culturel', 'Patrimoine', 'Histoire', 'Urbain', 'Nature', 'Pleine air', 'Sport', 'Nautique', 'Gastronomie', 'Musée', 'Atelier', 'Musique', 'Famille', 'Cinéma', 'Cirque', 'Son et lumière', 'Humour'];
                const tagsToHideOther = ['Français', 'Fruit de mer', 'Asiatique', 'Indienne', 'Italienne', 'Gastronomique', 'Restauration rapide', 'Crêperie', 'Végétarienne', 'Végétalienne', 'Kebab'];
                const tagsItemsContainer = document.getElementById('tags-items-container');
                const tagContainer = document.getElementById('tag-container');

                // Sélectionne les éléments nécessaires pour les 3 images
                const boutonSuppression = document.getElementById('file-delete');
                const filetitres = document.querySelectorAll('.file-upload');
                const labelsBoutonAjout = document.querySelectorAll('.custom-file-upload'); // Les labels d'ajout d'image
                const iconsBoutonAjout = document.querySelectorAll('.custom-file-upload img'); // Sélectionne l'image dans le label
                const selectedImages = document.querySelectorAll('.selected-image'); // Récupère toutes les images
                const statusImages = document.querySelectorAll('.status-image'); // Récupère toutes les images
                const texteBoutonAjout = document.querySelectorAll('.upload-text'); // Les textes des boutons d'ajout
                const errorMessageSupprimer = document.getElementById('error-message-supprimer');
                const preventionMessage = document.getElementById('message-prevention');

                var currentImageCount = 0; // Nombre d'images actuellement affichées
                var selectedImageIndex = -1; // Index de l'image sélectionnée pour la suppression
                var currentImageSelectedIndex;
                var nextIndex = 0;

                // Sélectionne les éléments nécessaires pour la map
                const mapFileUpload = document.getElementById('map-file-upload');
                const mapFileDelete = document.getElementById('map-file-delete');
                const mapImageContainer = document.getElementById('map-image-container').querySelector('img');
                const mapTextBouton = document.getElementById("map-upload-text"); 
                const mapIconBouton = document.querySelector('#map-custom-file-upload img');
                
                const mapStatus = document.getElementById("status-map"); 

                ////////////////////////////////////////////////////////////////////////////////////////
                ///////////////////////////////// GESTION DES CHAMPS PAR CATEGORIE /////////////////////
                ////////////////////////////////////////////////////////////////////////////////////////

                // Fonction pour gérer l'affichage des champs
                function handleCategorieChange() {
                    var selectedCategorie = categorieSelect.value;

                    if (selectedCategorie === 'activite') {
                        dureeContainer.style.display = 'flex';
                        document.getElementById('duree').required = true;
                        nbattractionContainer.style.display = 'none';
                        document.getElementById('nbattraction').required = false;
                        ageContainer.style.display = 'flex';
                        document.getElementById('age').required = true;
                        capaciteContainer.style.display = 'none';
                        document.getElementById('capacite').required = false;
                        langueContainer.style.display = 'none';
                        carteImageContainer.style.display = 'none';
                        repasContainer.style.display = 'none';
                        repasMapContainer.style.marginBottom = '0';
                        prestationIncluseContainer.style.display = 'flex';
                        prestationExcluseContainer.style.display = 'flex';
                    }

                    if (selectedCategorie === 'visite') {
                        dureeContainer.style.display = 'flex';
                        document.getElementById('duree').required = true;
                        nbattractionContainer.style.display = 'none';
                        document.getElementById('nbattraction').required = false;
                        ageContainer.style.display = 'none';
                        document.getElementById('age').required = false;
                        capaciteContainer.style.display = 'none';
                        document.getElementById('capacite').required = false;
                        langueContainer.style.display = 'flex';
                        carteImageContainer.style.display = 'none';
                        repasContainer.style.display = 'none';
                        repasMapContainer.style.marginBottom = '0';
                        prestationIncluseContainer.style.display = 'none';
                        prestationExcluseContainer.style.display = 'none';
                    }

                    if (selectedCategorie === 'spectacle') {
                        dureeContainer.style.display = 'flex';
                        document.getElementById('duree').required = true;
                        nbattractionContainer.style.display = 'none';
                        document.getElementById('nbattraction').required = false;
                        ageContainer.style.display = 'none';
                        document.getElementById('age').required = false;
                        capaciteContainer.style.display = 'flex';
                        document.getElementById('capacite').required = true;
                        langueContainer.style.display = 'none';
                        carteImageContainer.style.display = 'none';
                        repasContainer.style.display = 'none';
                        repasMapContainer.style.marginBottom = '0';
                        prestationIncluseContainer.style.display = 'none';
                        prestationExcluseContainer.style.display = 'none';
                    }

                    if (selectedCategorie === 'parcAttraction') {
                        dureeContainer.style.display = 'none';
                        document.getElementById('duree').required = false;
                        nbattractionContainer.style.display = 'flex';
                        document.getElementById('nbattraction').required = true;
                        ageContainer.style.display = 'flex';
                        document.getElementById('age').required = true;
                        capaciteContainer.style.display = 'none';
                        document.getElementById('capacite').required = false;
                        langueContainer.style.display = 'none';
                        carteImageContainer.style.display = 'flex';
                        repasMapContainer.style.marginBottom = '40px';
                        labelElement.textContent = 'Plan du parc';
                        repasContainer.style.display = 'none';
                        prestationIncluseContainer.style.display = 'none';
                        prestationExcluseContainer.style.display = 'none';
                    }

                    //pour la restauration, gestion des tags
                    Array.from(tags).forEach(tag => {
                        var tagText = tag.textContent;

                        if (selectedCategorie === 'restauration') {
                            if (tagsToHideRestauration.includes(tagText)) {
                                tag.style.display = 'none';
                                // Récupérer l'attribut 'for' du label
                                let titreId = tag.htmlFor; // équivalent à label.getAttribute('for')

                                // Récupérer l'titre correspondant via l'ID
                                let checkbox = document.getElementById(titreId);
                                checkbox.checked = false;


                            } else {
                                tag.style.display = 'flex';
                            }
                            tagsItemsContainer.style.height = '77.3px';
                            tagContainer.style.height = '107.3px';
                            categoriePrixContainer.style.display = 'flex';
                            document.getElementById('categorie-prix').required = true;
                            dureeContainer.style.display = 'none';
                            document.getElementById('duree').required = false;
                            nbattractionContainer.style.display = 'none';
                            document.getElementById('nbattraction').required = false;
                            ageContainer.style.display = 'none';
                            document.getElementById('age').required = false;
                            capaciteContainer.style.display = 'none';
                            document.getElementById('capacite').required = false;
                            langueContainer.style.display = 'none';
                            carteImageContainer.style.display = 'flex';
                            labelElement.textContent = 'Menu du restaurant';
                            repasContainer.style.display = 'flex';
                            repasMapContainer.style.marginBottom = '40px';
                            prestationIncluseContainer.style.display = 'none';
                            prestationExcluseContainer.style.display = 'none';
                        } else {
                            if (tagsToHideOther.includes(tagText)) {
                                tag.style.display = 'none';
                                // Récupérer l'attribut 'for' du label
                                let titreId = tag.htmlFor; // équivalent à label.getAttribute('for')

                                // Récupérer l'titre correspondant via l'ID
                                let checkbox = document.getElementById(titreId);
                                checkbox.checked = false;
                            } else {
                                tag.style.display = 'flex';
                            }
                            tagsItemsContainer.style.height = '116px';
                            tagContainer.style.height = '146px';
                            categoriePrixContainer.style.display = 'none';
                            document.getElementById('categorie-prix').required = false;
                            repasContainer.style.display = 'none';
                            
                        }
                    });

                    

                    
                }

                // Attacher l'événement 'change' à la sélection de catégorie
                categorieSelect.addEventListener('change', handleCategorieChange);

                // Appeler la fonction au chargement initial pour gérer l'état par défaut
                handleCategorieChange();

                ////////////////////////////////////////////////////////////////////////////////
                ///////////////////////// GESTION DES TAGS /////////////////////////////////////
                ////////////////////////////////////////////////////////////////////////////////

                // Transformer la collection en tableau et ajouter un écouteur d'événements à chaque élément
                Array.from(tags).forEach(tag => {
                    tag.addEventListener('click', function() {
                        this.classList.toggle('selectionne');  // Ajoute/enlève la classe 'selectionne'
                    });
                });

                //////////////////////////////////////////////////////////////////////////////////
                //////////////////////////// GESTION DES IMAGES //////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////////
                               

                // Fonction pour mettre à jour le texte de la balise <p> et changer l'icône
                function updateLabel(bouton, iconBouton, text, iconSrc) {
                    bouton.textContent = text; // Mettre à jour le texte de la balise <p>
                    iconBouton.src = iconSrc; // Mettre à jour la source de l'image
                }


                // Fonction pour afficher/masquer les bons boutons
                function toggleFileUpload() {
                    // Réinitialisation : masquer tous les boutons
                    labelsBoutonAjout.forEach(label => (label.style.display = 'none'));

                    if (selectedImageIndex !== -1) {
                        // Si une image est sélectionnée pour modification
                        updateLabel(
                            texteBoutonAjout[selectedImageIndex],
                            iconsBoutonAjout[selectedImageIndex],
                            'Modifier l\'image' ,
                            '../icons/changer.svg'
                        );
                        labelsBoutonAjout[selectedImageIndex].style.display = 'flex';
                    } else {
                        // Aucune image sélectionnée
                        if (currentImageCount < 3) {
                            updateLabel(
                                texteBoutonAjout[nextIndex],
                                iconsBoutonAjout[nextIndex],
                                'Ajouter une image' ,
                                '../icons/plus.svg'
                            );
                            labelsBoutonAjout[nextIndex].style.display = 'flex';
                        }
                    }
                }

                //fonction pour rechercher une image vide (à partir de la première)
                function rechercheImageVide(){
                    let foundEmptySlot = false;
                    // Trouver la première case vide pour nextIndex
                    for (var i = 0; i < selectedImages.length; i++) {
                        if (!foundEmptySlot && selectedImages[i].getAttribute('src') === '../images/rien.png') {
                            foundEmptySlot = true;
                            return i;
                        }
                    }
                    
                }

                nextIndex = rechercheImageVide(); // mise a jour dès le début
                if(nextIndex !== 0){
                    currentImageCount = nextIndex;
                    preventionMessage.style.display = 'none';
                    boutonSuppression.style.display = 'flex';
                }

                //focntion pour rechercher une image pleine (à partir de la dernière)
                function rechercheImagePleine(){
                    let foundFullSlot = false;
                        // Trouver la première case non-vide pour la supprimer (à partir de la fin)
                        for (var i = 2; i > -1; i--) {
                            if (!foundFullSlot && selectedImages[i].getAttribute('src') !== '../images/rien.png') {                             
                                foundFullSlot = true;
                                return i;
                            }
                        }
                        
                }
                
                toggleFileUpload();

                // Gérer la sélection des images en cliquant dessus
                document.getElementById('image-container').addEventListener('click', function(e) {  
                    if (e.target.classList.contains('selected-image')) {
                        currentImageSelectedIndex = Array.from(selectedImages).indexOf(e.target);
                        if (selectedImageIndex === currentImageSelectedIndex){ // vérifie si l'image était déja selectionné avant
                            e.target.classList.remove('selected');
                            selectedImageIndex = -1;
                            errorMessageSupprimer.style.display = 'none';
                        } else {
                            selectedImageIndex = currentImageSelectedIndex;
                            selectedImages.forEach(img => img.classList.remove('selected'));
                            e.target.classList.add('selected');
                            errorMessageSupprimer.style.display = 'none';
                        }
                        
                        toggleFileUpload()
                    }
                });





                // Fonction pour afficher une image dans la première case vide
                filetitres.forEach(filetitre => {
                    filetitre.addEventListener('change', function() {
                        if (filetitre.files.length > 0) {
                            var file = filetitre.files[0]; // Prend le fichier unique
                            var reader = new FileReader(); // Crée un lecteur de fichier
                        
                            reader.readAsDataURL(file); // Lire l'image sous forme de base64

                            reader.onload = function(e) {
                                if (selectedImageIndex !== -1) { // si une image est selectionné
                                    if(selectedImages[selectedImageIndex].getAttribute('src') === '../images/rien.png'){
                                        currentImageCount++;
                                        selectedImages[selectedImageIndex].src = e.target.result;
                                        selectedImages[selectedImageIndex].classList.remove('selected');
                                        //MODIFIER OFFRE : change statut image
                                        if (statusImages.length !== 0) {
                                            statusImages[selectedImageIndex].value = 'modifier';
                                        }
                                        selectedImageIndex = -1;

                                        // Trouver la première case vide pour nextIndex
                                        nextIndex = rechercheImageVide();
                                        boutonSuppression.style.display = 'flex';
                                        errorMessageSupprimer.style.display = 'none'
                                        preventionMessage.style.display = 'none';
                                    } else { //sélectionné mais une image existe déjà
                                        selectedImages[selectedImageIndex].src = e.target.result;
                                        selectedImages[selectedImageIndex].classList.remove('selected');
                                        selectedImageIndex = -1;
                                        // Trouver la première case vide pour nextIndex
                                        nextIndex = rechercheImageVide();
                                    }                        
                                }else{                          
                                    selectedImages[nextIndex].src = e.target.result;
                                    
                                    currentImageCount++;
                                    //MODIFIER OFFRE : change statut image
                                    if (statusImages.length !== 0) {
                                        statusImages[nextIndex].value = 'modifier';
                                    }
                                    if (currentImageCount > nextIndex){
                                        nextIndex = currentImageCount;
                                    } else {
                                        nextIndex++;
                                    }
                                    boutonSuppression.style.display = 'flex';
                                    errorMessageSupprimer.style.display = 'none'
                                    preventionMessage.style.display = 'none';
                                }
                                toggleFileUpload(); // Vérifier si on doit masquer le bouton d'ajout                       
                            };
                            
   
                        }             
                    });
                });


                // Gérer la suppression des images
                boutonSuppression.addEventListener('click', function() {
                    if (selectedImageIndex === -1) {
                        // Trouver la première case non-vide pour la supprimer (à partir de la fin)
                        let i = rechercheImagePleine();
                        selectedImages[i].src = '../images/rien.png';
                        filetitres[i].value = '';
                        currentImageCount--;
                        //MODIFIER OFFRE : change statut image
                        if (statusImages.length !== 0) {
                            statusImages[i].value = 'supprime';
                        }
                        if (currentImageCount === 0 ){
                            boutonSuppression.style.display = 'none';
                            errorMessageSupprimer.style.display = 'none';
                            preventionMessage.style.display = 'block';
                        }
                        nextIndex = rechercheImageVide();
                        toggleFileUpload()
                        
                    } else {
                        // Supprimer l'image sélectionnée
                        var img = selectedImages[selectedImageIndex];
                        if (img.getAttribute('src') !== '../images/rien.png'){
                            img.src = '../images/rien.png';
                            filetitres[selectedImageIndex].value = '';
                            //MODIFIER OFFRE : change statut image
                            if (statusImages.length !== 0) {
                                statusImages[selectedImageIndex].value = 'supprime';
                            }
                            img.classList.remove('selected');
                            currentImageCount--;
                            selectedImageIndex = -1;
                            errorMessageSupprimer.style.display = 'none'
                        } else {
                            errorMessageSupprimer.style.display = 'block'; 
                        }
                            
                        // Masquer le bouton de suppression s'il n'y a plus d'images
                        if (currentImageCount === 0) {
                            boutonSuppression.style.display = 'none';
                            errorMessageSupprimer.style.display = 'none';
                            preventionMessage.style.display = 'block';
                        }
                        nextIndex = rechercheImageVide();
                        toggleFileUpload(); // Vérifier si on doit afficher le bouton d'ajout après suppression
                    }
                });

                

                ////////////////////////////////////////////////////////////////////////////////////////////
                ////////////////////////// GESTION MAP /////////////////////////////////////////////////////
                ////////////////////////////////////////////////////////////////////////////////////////////
                
                //mettre a jour
                if(mapImageContainer.getAttribute('src') !== '../images/rien.png'){
                    mapTextBouton.textContent = 'Modifier';
                    mapIconBouton.src = '../icons/changer.svg';
                    mapFileDelete.style.display = 'flex';
                }

                // Fonction pour ajouter une image
                mapFileUpload.addEventListener('change', function() {
                    
                    if (mapFileUpload.files.length > 0) {
                        var file = mapFileUpload.files[0];
                        var reader = new FileReader();
                        mapFileDelete.style.display = 'flex';
                        

                        reader.onload = function(e) {
                            mapImageContainer.src = e.target.result;
                        };

                        reader.readAsDataURL(file);
                        mapTextBouton.textContent = 'Modifier';
                        mapIconBouton.src = '../icons/changer.svg';

                        //MODIFIER OFFRE : change statut image
                        if (mapStatus !== null) {
                            mapStatus.value = 'modifier';
                        }
                    }
                });

                // Fonction pour supprimer une image
                mapFileDelete.addEventListener('click', function() {
                    mapTextBouton.textContent = 'Ajouter';
                    mapIconBouton.src = '../icons/plus.svg';
                    mapImageContainer.src = '../images/rien.png'; // Réinitialiser l'image
                    mapFileDelete.style.display = 'none';
                    mapFileUpload.value = '';

                    //MODIFIER OFFRE : change statut image
                    if (mapStatus !== null) {
                        mapStatus.value = 'supprime';
                    }
                });
            // });

            ////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////// GESTION DES ERREURS//////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////
            const submitButton = document.getElementById('submit-button');
            const titre = document.getElementById("titre");
            const resume = document.getElementById("resume");
            const description = document.getElementById("description");
            const site = document.getElementById("site");
            const adresse = document.getElementById("adresse");
            const complement = document.getElementById("complement");
            const codeP = document.getElementById("codeP");
            const ville = document.getElementById("ville");
            const accessibilite = document.getElementById("accessibilite");
            const prix = document.getElementById("prix");
            const duree = document.getElementById("duree");
            const age = document.getElementById("age");
            const nbattraction = document.getElementById("nbattraction");
            const capacite = document.getElementById("capacite");
            const pinclus = document.getElementById("pinclus");
            const pexclus = document.getElementById("pexclus");
            const langue = document.getElementById("langue");

            function validateTitle(event) {
                const caracteresInterdits = /[\/\\<>{}\[\]=+@:#`*;!?]/;
                const caracteresInterditsListe = "/ \\ < > { } [ ] = + @ : # ` * ; ! ?";
                
                const messageErreur = document.getElementById("error-message-titre");

                if (titre.value.trim() === "") {
                    messageErreur.textContent = "Le titre est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton
                } else if (caracteresInterdits.test(titre.value)) {
                    let caractereInterdit = titre.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère " ${caractereInterdit} " est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateResume(event) {
                const caracteresInterdits = /[\\<>{}\[\]=+`]/;
                const caracteresInterditsListe = "\\ < > { } [ ] = + `";

                const messageErreur = document.getElementById("resume-error-message");

                if (resume.value.trim() === "") {
                    messageErreur.textContent = "Le résumé est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton
                } else if (caracteresInterdits.test(resume.value)) {
                    let caractereInterdit = resume.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateDescription(event) {
                const caracteresInterdits = /[\\<>{}\[\]=+`]/;
                const caracteresInterditsListe = "\\ < > { } [ ] = + `";

                const messageErreur = document.getElementById("description-error-message");

                if (description.value.trim() === "") {
                    messageErreur.textContent = "La description est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton
                } else if (caracteresInterdits.test(description.value)) {
                    let caractereInterdit = description.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateSite(event) {
                const messageErreur = document.getElementById("site-error-message");
                const submitButton = document.getElementById("submitButton");
            
                // Expression régulière pour vérifier une URL
                const urlRegex = /^(https?:\/\/)?([\w\-]+\.)+[\w]{2,}(\/[\w\-._~:/?#[\]@!$&'()*+,;=]*)?$/i;
            
                if (site.value.trim() === "") {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Autorise le champ vide
                } else if (!urlRegex.test(site.value)) {
                    messageErreur.textContent = "Veuillez entrer une URL valide (ex: https://www.example.com)";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true;
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false;
                }
            }
            

            function validateAdresse(event) {
                const caracteresInterdits = /[\/\\<>{}\[\]=+@:#`*,]/;
                const caracteresInterditsListe = "/ \\ < > { } [ ] = + @ : # ` * ,";
                const formatValide = /^\d+\s+[A-Za-zÀ-ÖØ-öø-ÿ]/;

                const messageErreur = document.getElementById("adresse-error-message");

                if (adresse.value.trim() === "") {
                    messageErreur.textContent = "L'adresse est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton
                } else if (!formatValide.test(adresse.value)) {
                    messageErreur.textContent = "Format incorrect. Exemple de format attendu : '12 rue de l'exemple'";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else if (caracteresInterdits.test(adresse.value)) {
                    let caractereInterdit = adresse.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateComplement(event) {
                const caracteresInterdits = /[\/\\<>{}\[\]=+@:#`*]/;
                const caracteresInterditsListe = "/ \\ < > { } [ ] = + @ : # ` *";

                const messageErreur = document.getElementById("complement-error-message");

                if (complement.value.trim() === "") {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false;
                } else if (!/^[A-Za-zÀ-ÖØ-öø-ÿ]/.test(complement.value)) {
                    messageErreur.textContent = "Le compléments d'adresse soit commencer par une lettre";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else if (caracteresInterdits.test(complement.value)) {
                    let caractereInterdit = complement.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateCodeP(event) {
                const caracteresNonNumeriques = /[^0-9]/;

                const messageErreur = document.getElementById("postal-code-error-message");
                const inputLength = codeP.value.length; // Longueur du contenu dans le champ de saisie

                // Vérifie si le champ est vide
                if (codeP.value.trim() === "") {
                    messageErreur.textContent = "Le code postal est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (caracteresNonNumeriques.test(codeP.value)) {
                    messageErreur.textContent = "Seuls les chiffres sont autorisés";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else if (inputLength !== 5) {
                    messageErreur.textContent = "Le code postal doit contenir exactement 5 chiffres";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si pas exactement 5 chiffres
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateVille(event) {
                const caracteresNonLettres = /[^a-zA-Z\s]/;

                const messageErreur = document.getElementById("city-error-message");

                // Vérifie si le champ est vide
                if (ville.value.trim() === "") {
                    messageErreur.textContent = "La ville est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (caracteresNonLettres.test(ville.value)) {
                    messageErreur.textContent = "Seuls les lettres sont autorisées";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateAcces(event) {
                const caracteresInterdits = /[\\<>{}\[\]=+`]/;
                const caracteresInterditsListe = "\\ < > { } [ ] = + `";

                const messageErreur = document.getElementById("accessibility-error-message");

                if (accessibilite.value.trim() === "") {
                    messageErreur.textContent = "L'accessibilité est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton
                } else if (caracteresInterdits.test(accessibilite.value)) {
                    let caractereInterdit = accessibilite.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validatePrix(event) {
                const caracteresNonNumeriques = /[^0-9.,]/;

                const messageErreur = document.getElementById("prix-error-message");

                // Vérifie si le champ est vide
                if (prix.value.trim() === "") {
                    messageErreur.textContent = "Le prix minimum est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (caracteresNonNumeriques.test(prix.value)) {
                    messageErreur.textContent = "Seuls les chiffres, la virgule et le point sont autorisés";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else if (prix.value.endsWith(",") || prix.value.endsWith(".")) {
                    messageErreur.textContent = "Le prix ne doit pas se terminer par une virgule ou un point";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si le prix se termine par un point ou une virgule
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateDuree(event) {
                const formatHeure = /^([0-9][0-9]):([0-5][0-9])$/; 
                const messageErreur = document.getElementById("duree-error-message");
            
                // Vérifie si le champ est vide
                if (duree.value.trim() === "") {
                    messageErreur.textContent = "La durée est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (!formatHeure.test(duree.value)) {
                    messageErreur.textContent = "Le format doit être HH:MM";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si le format est incorrect
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateAge(event) {
                const caracteresNonNumeriques = /[^0-9]/;
                const messageErreur = document.getElementById("age_min-error-message");
                const inputLength = age.value.length; // Longueur du contenu dans le champ de saisie
            
                // Vérifie si le champ est vide
                if (age.value.trim() === "") {
                    messageErreur.textContent = "L'âge minimum est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (caracteresNonNumeriques.test(age.value)) {
                    messageErreur.textContent = "Seuls les chiffres sont autorisés";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si le format est incorrect
                } else if (inputLength > 2) {
                    messageErreur.textContent = "L'âge minimum ne peut pas contenir plus de deux chiffres";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si plus de 2 chiffres   
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateNbAttraction(event) {
                const caracteresNonNumeriques = /[^0-9]/;
                const messageErreur = document.getElementById("nbr_attractions-error-message");
            
                // Vérifie si le champ est vide
                if (nbattraction.value.trim() === "") {
                    messageErreur.textContent = "Le nombre d'attraction est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (caracteresNonNumeriques.test(nbattraction.value)) {
                    messageErreur.textContent = "Seuls les chiffres sont autorisés";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si le format est incorrect
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateCapacite(event) {
                const caracteresNonNumeriques = /[^0-9]/;
                const messageErreur = document.getElementById("capacite-error-message");
            
                // Vérifie si le champ est vide
                if (capacite.value.trim() === "") {
                    messageErreur.textContent = "Le nombre de places est obligatoire";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si vide
                } else if (caracteresNonNumeriques.test(capacite.value)) {
                    messageErreur.textContent = "Seuls les chiffres sont autorisés";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si le format est incorrect
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validatePinclus(event) {
                const caracteresInterdits = /[\\<>{}\[\]=+`]/;
                const caracteresInterditsListe = "\\ < > { } [ ] = + `";

                const messageErreur = document.getElementById("pinclus-error-message");

                if (pinclus.value.trim() === "") {
                    messageErreur.style.display = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Désactive le bouton
                } else if (!/^[A-Za-zÀ-ÖØ-öø-ÿ]/.test(pinclus.value)) {
                    messageErreur.textContent = "Les prestations incluses doivent commencer par une lettre";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else if (caracteresInterdits.test(pinclus.value)) {
                    let caractereInterdit = pinclus.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validatePexclus(event) {
                const caracteresInterdits = /[\\<>{}\[\]=+`]/;
                const caracteresInterditsListe = "\\ < > { } [ ] = + `";

                const messageErreur = document.getElementById("pexlus-error-message");

                if (pexclus.value.trim() === "") {
                    messageErreur.style.display = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = true; // Désactive le bouton
                } else if (caracteresInterdits.test(pexclus.value)) {
                    let caractereInterdit = pexclus.value.match(caracteresInterdits)[0];
                    messageErreur.textContent = `Le caractère "${caractereInterdit}" est interdit. Caractères spéciaux interdits : ${caracteresInterditsListe}`;
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else if (!/^[A-Za-zÀ-ÖØ-öø-ÿ]/.test(pexclus.value)) {
                    messageErreur.textContent = "Les prestations exluses doivent commencer par une lettre";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            function validateLangue(event) {
                const caracteresNonLettres = /[^a-zA-Z\s]/;

                const messageErreur = document.getElementById("langue-error-message");

                // Vérifie si le champ est vide
                if (langue.value.trim() === "") {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Désactive le bouton si vide
                } else if (caracteresNonLettres.test(langue.value)) {
                    messageErreur.textContent = "Seuls les lettres sont autorisées";
                    messageErreur.style.display = "block";
                    submitButton.disabled = true; // Désactive le bouton si un caractère interdit est présent
                } else {
                    messageErreur.textContent = "";
                    messageErreur.style.display = "none";
                    submitButton.disabled = false; // Active le bouton si tout est correct
                }
            }

            



            // Validation lorsque l'utilisateur quitte le champ de saisie (événement blur)
            document.getElementById('titre').addEventListener('input', validateTitle);
            document.getElementById('resume').addEventListener('input', validateResume);
            document.getElementById('description').addEventListener('input', validateDescription);
            document.getElementById('site').addEventListener('input', validateSite);
            document.getElementById('adresse').addEventListener('input', validateAdresse);
            document.getElementById('complement').addEventListener('input', validateComplement);
            document.getElementById('codeP').addEventListener('input', validateCodeP);
            document.getElementById('ville').addEventListener('input', validateVille);
            document.getElementById('accessibilite').addEventListener('input', validateAcces);
            document.getElementById('prix').addEventListener('input', validatePrix);
            document.getElementById('duree').addEventListener('input', validateDuree);
            document.getElementById('age').addEventListener('input', validateAge);
            document.getElementById('nbattraction').addEventListener('input', validateNbAttraction);
            document.getElementById('capacite').addEventListener('input', validateCapacite);
            document.getElementById('pinclus').addEventListener('input', validatePinclus);
            document.getElementById('pexclus').addEventListener('input', validatePexclus);
            document.getElementById('langue').addEventListener('input', validateLangue);

            ////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////   ALERT ABANDON    ////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////

            function confirmer(event) {
                event.preventDefault(); // Empêche la navigation automatique
                if (window.location.pathname.split("/").pop() == 'creation_offre.php'){
                    if (confirm("Vos informations ne seront pas sauvegardées, êtes-vous sûr?")) {
                        window.location.replace("tableau_de_bord.php");
                    }
                } else {
                    if (confirm("Vos modifications ne seront pas sauvegardées, êtes-vous sûr?")) {
                        window.location.replace("tableau_de_bord.php");
                    }
                }
                
            }


            document.addEventListener("DOMContentLoaded", function () {
                // Sélectionne tous les liens <a> du <nav>
                const navLinks = document.querySelectorAll("nav a");
            
                // Ajoute un événement 'click' sur chaque lien
                navLinks.forEach(link => {
                    // Vérifie que ce n'est pas le bouton de déconnexion (qui a déjà son propre `onclick`)
                    if (!link.onclick) {
                        link.addEventListener("click", function (event) {
                            confirmer(event, this.href); // Passe l'URL du lien à la fonction
                        });
                    }
                });
            });