// import { closeSortMenu } from './scriptFilter.js';


document.addEventListener('DOMContentLoaded', function () {
    
    const body = document.querySelector('body');

    const trie_text = document.querySelector('.dropdown-toggle > p');
    const rechercheIMG = document.getElementsByClassName('rechercheLieuIMG');
    const SCREEN_MOBILE = 801; // Taille de l'écran pour le changement d'images

    const arrow_carte = document.getElementById("arrow_carte")

    const mapContainer = document.getElementById("map");

    arrow_carte.addEventListener("click", function () {
        mapContainer.classList.toggle("cache");
    });

    var map = L.map('map').setView([48.7321, -3.4591], 14);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);





    const categoriesFiltres = [
        'filtre-type',
        'filtre-lieu',
        'filtre-note'
    ];

    const filtres_actifs = Array(categoriesFiltres.length).fill().map(() => []);
    const filtres_temps = { start: null, end: null }; // Pour le filtre-temps
    const filtres_prix = { min: null, max: null };   // Pour le filtre-prix
    
    let offres;
    let ecran;
    let rechercheOffre;
    function updateOffres() {
        if (window.innerWidth > SCREEN_MOBILE) {
            offres = document.getElementsByClassName('carte_offre desktop-element');
            rechercheOffre = document.getElementById('rechercheOffre');
        } else {
            offres = document.getElementsByClassName('carte_offre phone-element');
            rechercheOffre = document.getElementById('rechercheOffreMobile');
        }

        ecran = window.innerWidth > SCREEN_MOBILE ? 'desktop' : 'mobile';
    }

    // Initial call to set the correct value
    updateOffres();

    Array.from(offres).forEach(offre => {
        offre.addEventListener('click', function () {
            const offreData = {
                id: offre.getAttribute('data-ido'),
                titre: offre.getAttribute('data-titre'),
                adresse: offre.getAttribute('data-adresse'),
                prix: offre.getAttribute('data-prix'),
                note: offre.getAttribute('data-note'),
                desc: offre.getAttribute('data-desc'),
                image: offre.getAttribute('data-image'),
                nbrAvis: offre.getAttribute('data-avis'),
            };
            ajouterHistorique(offreData);
        });
    });

    // Update the value on window resize
    window.addEventListener('resize', updateOffres);


    const dropdownMenu = document.querySelector('.dropdown-menu');
    const menuItems = dropdownMenu.querySelectorAll('li');


    // Gestion des filtres avec checkboxes
    function ajouterEcouteursCheckbox() {
        categoriesFiltres.forEach((categorie, index) => {
            const inputs = document.querySelectorAll(`.${ecran} .${categorie} input`);
            inputs.forEach(input => {
                input.addEventListener('change', function () {

                    if (input.checked) {
                        filtres_actifs[index].push(input.name);
                    } else {
                        const idx = filtres_actifs[index].indexOf(input.name);
                        if (idx > -1) filtres_actifs[index].splice(idx, 1);
                    }
                    afficherResultats(filtrage());
                });
            });
        });
    }

    // Gestion des filtres avec checkboxes pour "filtre-ouvert"
    function ajouterEcouteursCheckboxOuvert() {
        const checkboxes = document.querySelectorAll(`.${ecran} .filtre-ouvert input[type="checkbox"]`);
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                afficherResultats(filtrage());
            });
        });
    }

    ajouterEcouteursCheckboxOuvert();



    // Gestion des filtres de dates (calendriers)
    function ajouterEcouteursTemps() {
        const startDateInput = document.querySelector(`.${ecran} .filtre-temps-start`);
        const endDateInput = document.querySelector(`.${ecran} .filtre-temps-end`);
    
        startDateInput.addEventListener('change', function () {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
    
            // Validation des dates
            if (endDateInput.value && startDate > endDate) {
                startDateInput.value = endDateInput.value; // Corrige si nécessaire
            }
            filtres_temps.start = startDate;
            afficherResultats(filtrage());
        });
    
        endDateInput.addEventListener('change', function () {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
    
            // Validation des dates
            if (startDateInput.value && endDate < startDate) {
                endDateInput.value = startDateInput.value; // Corrige si nécessaire
            }
            filtres_temps.end = endDate;
            afficherResultats(filtrage());
        });
    }
    

    // Gestion des filtres de prix (double-slider)
    function ajouterEcouteursPrix() {
        
        const sliderMin = document.querySelector(`.${ecran} .filtre-prix-min`);
        const sliderMax = document.querySelector(`.${ecran} .filtre-prix-max`);
        const valueLeft = document.querySelector(`.${ecran} #value-left`);
        const valueRight = document.querySelector(`.${ecran} #value-right`);
    
        sliderMin.addEventListener('input', function () {
            const minValue = parseInt(sliderMin.value, 10);
            const maxValue = parseInt(sliderMax.value, 10);
    
            // Empêche que le curseur gauche dépasse le droit
            if (minValue > maxValue) {
                sliderMin.value = maxValue;
            }
            valueLeft.textContent = `${sliderMin.value}€`;
    
            filtres_prix.min = parseInt(sliderMin.value, 10);
            afficherResultats(filtrage());
        });
    
        sliderMax.addEventListener('input', function () {
            const minValue = parseInt(sliderMin.value, 10);
            const maxValue = parseInt(sliderMax.value, 10);
    
            // Empêche que le curseur droit passe en dessous du gauche
            if (maxValue < minValue) {
                sliderMax.value = minValue;
            }
            valueRight.textContent = `${sliderMax.value}€`;
    
            filtres_prix.max = parseInt(sliderMax.value, 10);
            afficherResultats(filtrage());
        });
    }
    

    // Fonction de filtrage
    function filtrage() {
        let resultats = Array.from(offres).filter(offre => {
            const checkboxesOK = filtres_actifs.every((filtresCategorie, index) => {
                if (filtresCategorie.length === 0) return true;
                return filtresCategorie.some(filtre => offre.classList.contains(filtre));
            });
    
            const tempsOK = (() => {
                if (!filtres_temps.start && !filtres_temps.end) return true;
                const offreDate = new Date(offre.getAttribute('data-date'));
                if (filtres_temps.start && offreDate < filtres_temps.start) return false;
                if (filtres_temps.end && offreDate > filtres_temps.end) return false;
                return true;
            })();
    
            const prixOK = (() => {
                if (filtres_prix.min === null && filtres_prix.max === null) return true;
                const offrePrix = parseInt(offre.getAttribute('data-prix'), 10);
                if (filtres_prix.min !== null && offrePrix < filtres_prix.min) return false;
                if (filtres_prix.max !== null && offrePrix > filtres_prix.max) return false;
                return true;
            })();
    
            // Vérification du filtre "ouvert/fermé"
            const ouvertOK = (() => {
                const radioOuvert = document.querySelector(`.${ecran} .filtre-ouvert input[type="checkbox"]:checked`);
                if (!radioOuvert) return true; // Pas de filtre sélectionné
                const filtreOuvert = radioOuvert.id;
                // Appliquer uniquement aux offres de "Restauration"
                if (offre.classList.contains('Restauration')) {
                    // Vérification du statut "ouvert"
                    if (filtreOuvert === 'ouvert' && offre.classList.contains('ferme')) return false;
                }
                return true;

            })();
    
            // Retourner si l'offre passe tous les filtres
            return checkboxesOK && tempsOK && prixOK && ouvertOK;
        });
    
        // Tri par défaut (priorité aux offres "aLaUne")
        resultats.sort((a, b) => b.classList.contains('aLaUne') - a.classList.contains('aLaUne'));
        const estNouveaute = Array.from(boutonNouveau).some(bouton => bouton.classList.contains('active'));
        if (estNouveaute) {
            resultats = resultats.filter(offre => offre.classList.contains('Nouveaute'));
        }
        const aLaUne = Array.from(boutonALaUne).some(bouton => bouton.classList.contains('active'));
        if (aLaUne) {
            resultats = resultats.filter(offre => offre.classList.contains('aLaUne'));
        }
        return resultats;
    }
    
    let sensInverse = false;

    function trie(offresVisibles) {
        
        if (window.innerWidth > SCREEN_MOBILE) {
            if (menuItems[0].classList.contains('active')) { // Tri par prix
                offresVisibles.sort((a, b) => {
                    return sensInverse ? a.getAttribute('data-prix') - b.getAttribute('data-prix') : b.getAttribute('data-prix') - a.getAttribute('data-prix');
                });
            }
            if (menuItems[1].classList.contains('active')) { // Tri par note
                offresVisibles.sort((a, b) => {
                    return sensInverse ? a.getAttribute('data-note') - b.getAttribute('data-note') : b.getAttribute('data-note') - a.getAttribute('data-note');
                });
            }
        } else if (window.innerWidth <= SCREEN_MOBILE) {
            if (boutonTriMobile[0].classList.contains('active')) { // Tri par prix
                offresVisibles.sort((a, b) => {
                    return sensInverse ? a.getAttribute('data-prix') - b.getAttribute('data-prix') : b.getAttribute('data-prix') - a.getAttribute('data-prix');
                });
            }
            if (boutonTriMobile[1].classList.contains('active')) { // Tri par note
                offresVisibles.sort((a, b) => {
                    return sensInverse ? a.getAttribute('data-note') - b.getAttribute('data-note') : b.getAttribute('data-note') - a.getAttribute('data-note');
                });
            }
        }
    }
    
    const sensTris = document.querySelectorAll(`.${ecran}.sensTris`);

    sensTris.forEach(sensTri => {
        sensTri.style.display = 'none';
    });

    const sensTriIMG = document.querySelector(`.${ecran}.sensTris img`);

    sensTris.forEach(sensTri => {
        sensTri.addEventListener('click', function (event) {
            event.stopPropagation(); // Empêche la propagation de l'événement
            if (trie_text.innerHTML === 'Trier par') return; // Ne rien faire si le texte est "Trier par"
            if (!sensTri.classList.contains('clicked')) {
                sensTri.classList.add('clicked');
                sensInverse = !sensInverse;
                sensTriIMG.classList.toggle('rotate-180');

                if (sensTri.classList.contains('desktop')) {                    
                    for (let i = 0; i < menuItems.length; i++) {
                        if (menuItems[i].classList.contains('active')) {
                            const link = menuItems[i].querySelector('a');
                            if (sensInverse) {
                                sensText = 'Décroissant';
                            } else {
                                sensText = 'Croissant';
                            }
                            trie_text.innerHTML = 'Trier par :' + ' ' + `<span style="color: var(--bleuFonce-rougeFonce); font-size: 0.8em;">${link.innerHTML} ${sensText}</span>`; // Change text with color
                        }
                    }
                } else {
                    for (let i = 0; i < boutonTriMobile.length; i++) {
    
                        if (boutonTriMobile[i].classList.contains('active')) {
                            boutonTriMobile[i].textContent = boutonTriMobile[i].textContent.replace(` ${sensText}`, '');
                            if (sensInverse) {
                                sensText = 'Décroissant';
                            } else {
                                sensText = 'Croissant';
                            }
                            boutonTriMobile[i].textContent += ` ${sensText}`;
                        } else {
                            boutonTriMobile[i].textContent = boutonTriMobile[i].textContent.replace(` ${sensText}`, '');
                        }
                        
                        trie_text.innerHTML = 'Trier par :' + ' ' + `<span style="color: var(--bleuFonce-rougeFonce); font-size: 0.8em;">${boutonTriMobile[i].textContent} ${sensText}</span>`; // Change text with color
                    }
                }


                afficherResultats(filtrage());
                setTimeout(() => {
                    sensTri.classList.remove('clicked');
                }, 300); // Délai pour éviter les clics multiples rapides
            }
        });
    });

    
    const boutonTriMobile = document.getElementsByClassName("menu-item-tri");
    let sensText = 'Croissant';

    for (let i = 0; i < menuItems.length; i++) {
        menuItems[i].addEventListener('click', function () {
            const link = menuItems[i].querySelector('a');
            if (menuItems[i].classList.contains('active')) {
                menuItems[i].classList.remove('active');
                
                menuItems[i].style.borderLeft = ''; // Remove left border
                link.style.color = ''; // Remove link color

                trie_text.innerHTML = 'Trier par' // Reset text;

                sensTris.forEach(sensTri => {
                    sensTri.style.display = 'none';
                });

            } else {
                for (let k = 0; k < menuItems.length; k++) {
                    menuItems[k].classList.remove('active');
                    menuItems[k].style.borderLeft = ''; // Remove left border
                    const linkK = menuItems[k].querySelector('a');
                    if (linkK) linkK.style.color = ''; // Remove link color

                }
                menuItems[i].classList.add('active');
                menuItems[i].style.borderLeft = '2px solid var(--bleuFonce-rougeFonce)'; // Add left border
                link.style.color = 'var(--bleuFonce-rougeFonce)'; // Change link color
                if (sensInverse) {
                    sensText = 'Décroissant';
                } else {
                    sensText = 'Croissant';
                }
                trie_text.innerHTML = 'Trier par :' + ' ' + `<span style="color: var(--bleuFonce-rougeFonce); font-size: 0.8em;">${link.innerHTML} ${sensText}</span>`; // Change text with color

                sensTris.forEach(sensTri => {
                    sensTri.style.display = 'flex';
                });
            }
            closeDropdown();
            afficherResultats(filtrage());
        });
    }

    for (let i = 0; i < boutonTriMobile.length; i++) {
        boutonTriMobile[i].addEventListener('click', function () {
            if (boutonTriMobile[i].classList.contains('active')) {
                boutonTriMobile[i].classList.remove('active');
                
                boutonTriMobile[i].style.borderLeft = ''; // Remove left border

                trie_text.innerHTML = 'Trier par' // Reset text;

                sensTris.forEach(sensTri => {
                    sensTri.style.display = 'none';
                });

                boutonTriMobile[i].textContent = boutonTriMobile[i].textContent.replace(` ${sensText}`, '');

                sensInverse = false;

            } else {
                for (let k = 0; k < boutonTriMobile.length; k++) {
                    boutonTriMobile[k].classList.remove('active');
                    boutonTriMobile[k].style.borderLeft = ''; // Remove left border
                    boutonTriMobile[k].textContent = boutonTriMobile[k].textContent.replace(` ${sensText}`, '');

                }
                boutonTriMobile[i].classList.add('active');
                boutonTriMobile[i].style.borderLeft = '2px solid var(--bleuFonce-rougeFonce)'; // Add left border

                let boutonTriText = boutonTriMobile[i].classList.contains('active') ? boutonTriMobile[i].textContent : '';

                if (sensInverse) {
                    sensText = 'Décroissant';
                } else {
                    sensText = 'Croissant';
                }
                trie_text.innerHTML = 'Trier par :' + ' ' + `<span style="color: var(--bleuFonce-rougeFonce); font-size: 0.8em;">${boutonTriText.textContent} ${sensText}</span>`; // Change text with color

                if (boutonTriMobile[i].classList.contains('active')) {
                    boutonTriMobile[i].textContent += ` ${sensText}`;
                } else {
                    boutonTriMobile[i].textContent = boutonTriMobile[i].textContent.replace(` ${sensText}`, '');
                }

                sensTris.forEach(sensTri => {
                    sensTri.style.display = 'flex';
                });
            }
            
            afficherResultats(filtrage());
            closeSortMenu();
        });
    }

    rechercheOffre.addEventListener('input', function () {

        let rechercheIMG
        if (window.innerWidth > SCREEN_MOBILE) {
            rechercheIMG = document.getElementById('rechercheOffreIMG');
        } else {
            rechercheIMG = document.getElementById('rechercheOffreIMGmobile');
        }
       
        if (rechercheOffre.value != '') {
            rechercheIMG.src = '../images/croix-noire.png';
            rechercheIMG.style.cursor = 'pointer';
        } else {
            rechercheIMG.src = '../images/search_icon.png';
            rechercheIMG.style.cursor = 'default';
        }
        afficherResultats(filtrage());

        Array.from(rechercheIMG).forEach(rechercheIMG => {
            rechercheIMG.addEventListener('click', function () {
                let rechercheOffre;
                if (window.innerWidth > SCREEN_MOBILE) {
                    rechercheOffre = document.getElementById('rechercheOffre');
                } else {
                    rechercheOffre = document.getElementById('rechercheOffreMobile');
                }
                // const rechercheOffre = document.querySelector('.rechercheOffre'); // Suppose qu'il y a une seule `rechercheOffre`
                if (rechercheOffre) {
                    rechercheOffre.value = ''; // Vide le champ
                }
                rechercheIMG.src = '../images/search_icon.png';
                rechercheIMG.style.cursor = 'default';
        
                afficherResultats(filtrage()); // Exécution des fonctions
            });
        });
    });

    // Stocker les marqueurs pour les supprimer avant d'en ajouter de nouveaux
    // Déclaration globale : on crée un groupe de clusters et on l'ajoute à la carte
    var markersCluster = L.markerClusterGroup({
        disableClusteringAtZoom: 18, // Au niveau du zoom 18, les clusters se désagrègent
        spiderfyOnMaxZoom: true,     // Permet de séparer les marqueurs en "araignée" au zoom maximum
        zoomToBoundsOnClick: true    // Zoom sur le cluster lorsqu'on clique dessus
    });
    map.addLayer(markersCluster);

    // Fonction pour afficher uniquement les offres filtrées
    function afficherResultats(offresVisibles) {
        trie(offresVisibles);
        
        const rechercheTexte = rechercheOffre.value.toLowerCase();
        offresVisibles = offresVisibles.filter(offre => {
            const offreTexte = offre.textContent.toLowerCase();
            const offreCategorie = offre.getAttribute('data-categorie').toLowerCase();
            const offreTitre = offre.getAttribute('data-titre').toLowerCase();
            const offreDescription = offre.getAttribute('data-desc').toLowerCase();
            return offreTexte.includes(rechercheTexte) || offreCategorie.includes(rechercheTexte) || offreTitre.includes(rechercheTexte) || offreDescription.includes(rechercheTexte);
        });

        offresVisibles.sort((a, b) => b.classList.contains('aLaUne') - a.classList.contains('aLaUne'));

        Array.from(offres).forEach(offre => {
            offre.style.display = offresVisibles.includes(offre) ? 'block' : 'none';
            offresVisibles.forEach((offreVisible, index) => {
                offreVisible.style.order = index;
            });
        });

        // On vide les anciens marqueurs du groupe global
        markersCluster.clearLayers();

        // Instanciation du géocodeur Google
        var geocoder = new google.maps.Geocoder();

        let isMobile = ecran === 'mobile';
        let isFO = body.classList.contains('FO');
        let etoiles = (isMobile || isFO) ? 'etoile_bleue' : 'etoile_rouge';

        offresVisibles.forEach(element => {
            geocoder.geocode({ 'address': element.getAttribute("data-adresse") }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();

                    if (latitude && longitude) {
                        // Création du marqueur et ajout dans le groupe de clusters global
                        var marker = L.marker([latitude, longitude]);
                        markersCluster.addLayer(marker);

                        // Création de la popup
                        var popup = L.popup({ autoClose: false, closeOnClick: false, closeButton: false });

                        // Affichage de la popup au survol du marqueur
                        marker.on('mouseover', function (e) {
                            let titre = element.getAttribute("data-titre");
                            let adresse = element.getAttribute("data-adresse");
                            let codeP = element.getAttribute("data-code-postal");
                            let numero_voie = element.getAttribute("data-numero-voie");
                            let voie = element.getAttribute("data-voie");
                            let ville = element.getAttribute("data-ville");
                            let image = element.getAttribute("data-image");
                            let note = parseFloat(element.getAttribute("data-note"));
                            let avis = parseInt(element.getAttribute("data-avis"));
                            let description = element.getAttribute("data-desc");
                            let prix = element.getAttribute("data-prix"); 
                            let isOpen = element.classList.contains("ferme") ? "🟢 Fermé" : "🟢 Ouvert";
                            let ido = element.getAttribute("data-ido");

                            // Conversion de la note en étoiles
                            let stars = "";
                            if (avis !== 0) {
                                for (let i = 0; i < Math.floor(note); i++) {
                                    stars += `<img src="../icons/${etoiles}.png" alt="Étoile" class="popup-star">`;
                                }
                                if (note % 1 >= 0.5) {
                                    stars += `<img src="../icons/demi_${etoiles}.png" alt="Demi Étoile" class="popup-star">`;
                                } else {
                                    if (note !== 5) {
                                        stars += `<img src="../icons/${etoiles}_vide.png" alt="Étoile Vide" class="popup-star">`;
                                    }
                                }
                                for (let i = Math.ceil(note); i < 5; i++) {
                                    stars += `<img src="../icons/${etoiles}_vide.png" alt="Étoile Vide" class="popup-star">`;
                                }
                            } else {
                                stars = "Aucun avis";
                            }
                            
                            let adresseText = "";
                            let buttonsText = "";
                            if (isMobile) {
                                adresseText += 
                                `<a href="https://www.google.com/maps/dir//${adresse}" target="_blank" class="popup-address-lien">
                                    <div class="popup-address">
                                        <p class="popup-voie">📍 ${numero_voie} ${voie}</p>
                                        <p class="popup-ville">${ville}, ${codeP}</p>
                                    </div>
                                </a>`;
                                buttonsText +=
                                `<div class="popup-extra-mobile-container">
                                    <a href="https://www.google.com/maps/dir//${adresse}" target="_blank">
                                        <div class="popup-extra-mobile">Ouvrir Maps <img src="../icons/gmaps.png"></div>
                                    </a>
                                    <a href="detail-offre.php?value=${ido}">
                                        <div class="popup-extra-mobile">Voir les détails</div>
                                    </a>
                                </div>`;
                            } else {
                                adresseText += 
                                `<div class="popup-address">
                                    <p class="popup-voie">📍 ${numero_voie} ${voie}</p>
                                    <p class="popup-ville">${ville}, ${codeP}</p>
                                </div>`;
                            }
                            
                            popup.setLatLng(e.latlng).setContent(`
                                <div class="popupCarte">
                                    <h3 class="popup-title">${titre}</h3>
                                    <div class="popup-container">
                                        <div class="popup-left">
                                            <img src="../images/${image}" class="popup-image" alt="${titre}">
                                        </div>
                                        <div class="popup-center">
                                            <p class="popup-desc">${description}</p>
                                        </div>
                                    </div>
                                    <div class="popup-right">
                                        ${adresseText}
                                        <div class="popup-right-open">
                                            <div class="popup-right-stars">
                                                <div class="popup-stars">${stars}</div>
                                                <p class="popup-price">${prix}€</p>
                                            </div>
                                            <p class="popup-status ${element.classList.contains('ferme') ? 'ferme' : 'ouvert'}">${isOpen}</p>
                                        </div>
                                    </div>
                                    ${buttonsText}
                                </div>
                            `);
                            marker.bindPopup(popup).openPopup();
                        });

                        // Fermer la popup au retrait de la souris
                        marker.on('mouseout', function () {
                            marker.closePopup();
                        });
                        
                        // Action au clic sur le marqueur
                        marker.on('click', function () {
                            const elementId = element.getAttribute('data-ido');
                            const elementToScroll = document.getElementsByClassName(elementId)[0];
                            if (elementToScroll) {
                                elementToScroll.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                // Animation de surbrillance
                                let tempTransition = elementToScroll.style.transition;
                                let tempOpacity = elementToScroll.style.opacity;
                                let tempBoxShadow = elementToScroll.style.boxShadow;
                                let tempTransform = elementToScroll.style.transform;
                                elementToScroll.style.transition = 'all 1s ease-in-out';
                                elementToScroll.style.transform = 'scale(1.05) translateY(-15px)';
                                elementToScroll.style.opacity = '1';
                                elementToScroll.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
                                setTimeout(() => {
                                    elementToScroll.style.transform = tempTransform;
                                    elementToScroll.style.boxShadow = tempBoxShadow;
                                }, 1000);
                                setTimeout(() => {
                                    elementToScroll.style.opacity = tempOpacity;
                                    elementToScroll.style.transition = tempTransition;
                                }, 2000);
                            }
                        });
                    }
                }
            });
        });
    }


    

    // Initialisation
    ajouterEcouteursCheckbox();
    ajouterEcouteursTemps();
    ajouterEcouteursPrix();

    const rechercheLieux = Array.from(document.getElementsByClassName('rechercheLieu'));
    const lieux = Array.from(document.getElementsByClassName('lieu'));

    rechercheLieux.forEach(rechercheLieu => {
        rechercheLieu.addEventListener('input', function () {
            const rechercheTexte = rechercheLieu.value.toLowerCase();

            if (rechercheTexte !== '') {
                const rechercheIMG = rechercheLieu.nextElementSibling; // Suppose que l'image est juste après l'input
                rechercheIMG.src = '../images/croix-noire.png';
                rechercheIMG.style.cursor = 'pointer';

                rechercheIMG.addEventListener('click', function () {
                    rechercheLieu.value = '';
                    rechercheIMG.src = '../images/search_icon.png';
                    rechercheIMG.style.cursor = 'default';

                    lieux.forEach(lieu => {
                        lieu.style.display = 'flex';
                        lieu.classList.remove('hidden');
                    });
                });

                lieux.forEach(lieu => {
                    const lieuTexte = lieu.textContent.toLowerCase();
                    if (lieuTexte.includes(rechercheTexte)) {
                        lieu.style.display = 'flex';
                        lieu.classList.remove('hidden');
                    } else {
                        lieu.style.display = 'none';
                        lieu.classList.add('hidden');
                    }
                });
            } else {
                lieux.forEach(lieu => {
                    lieu.style.display = 'flex';
                });

                const rechercheIMG = rechercheLieu.nextElementSibling;
                rechercheIMG.src = '../images/search_icon.png';
                rechercheIMG.style.cursor = 'default';
            }
        });
    });


    const boutonNouveau = document.getElementsByClassName('boutonNouveau');
    const boutonALaUne = document.getElementsByClassName('boutonALaUne');   
    // boutons <p> Nouveau et A la une

    function filtrerParBouton() {
        const offresVisibles = filtrage();
        const offresFiltrees = offresVisibles.filter(offre => {
            const estNouveaute = Array.from(boutonNouveau).some(bouton => bouton.classList.contains('active')) && offre.classList.contains('Nouveaute');
            const estALaUne = Array.from(boutonALaUne).some(bouton => bouton.classList.contains('active')) && offre.classList.contains('aLaUne');
            return estNouveaute || estALaUne || (!Array.from(boutonNouveau).some(bouton => bouton.classList.contains('active')) && !Array.from(boutonALaUne).some(bouton => bouton.classList.contains('active')));
        });
        afficherResultats(offresFiltrees);
    }

    Array.from(boutonNouveau).forEach(bouton => {
        bouton.addEventListener('click', function () {
            bouton.classList.toggle('active');
            // if (this.classList.contains('active')) {
            //     this.style.backgroundColor = 'var(--bleuFonce-rougeFonce)';
            //     this.querySelector('p').style.color = '#F5F5F5';
                
            // } else {
            //     this.style.backgroundColor = '#F5F5F5';
            //     this.querySelector('p').style.color = '#757575';
            // }
            filtrerParBouton();
        });
    });

    Array.from(boutonALaUne).forEach(bouton => {
        bouton.addEventListener('click', function () {
            bouton.classList.toggle('active');
            // if (this.classList.contains('active')) {
            //     this.style.backgroundColor = '#2C2C2C';
            //     this.querySelector('p').style.color = '#F5F5F5';
                
            // } else {
            //     this.style.backgroundColor = '#F5F5F5';
            //     this.querySelector('p').style.color = '#757575';
            // }
            filtrerParBouton();
        });
    });

    afficherResultats(filtrage()); // Pour afficher les offres au tout début (sans filtres) afin que les offres à la Une soient prioritaires



    // Fonctionnalité de recherche par mots-clés récents
    const suggestionsContainer = document.createElement('ul');
    suggestionsContainer.classList.add('suggestions');
    document.body.appendChild(suggestionsContainer);

    const MAX_MOTS_CLES = 5; // Nombre maximum de mots-clés récents à stocker
    const NB_MAX_LENGTH = 18; // Nombre maximum de caractères pour un mot-clé
    const MOTS_CLES_STORAGE_KEY = 'motsClesRecents'; // Nom de la clé pour localStorage

    // Charger les mots-clés récents depuis localStorage
    function chargerMotsClesRecents() {
        const motsCles = localStorage.getItem(MOTS_CLES_STORAGE_KEY);
        return motsCles ? JSON.parse(motsCles) : [];
    }

    // Sauvegarder les mots-clés récents dans localStorage
    function sauvegarderMotsClesRecents(motsCles) {
        localStorage.setItem(MOTS_CLES_STORAGE_KEY, JSON.stringify(motsCles));
    }

    // Ajouter un mot-clé dans les récents
    function ajouterMotCleRecent(motCle) {
        let motsCles = chargerMotsClesRecents();
        motCle = motCle.trim().toLowerCase();

        if (!motCle || motsCles.includes(motCle)) return;

        // Ajouter le nouveau mot-clé et limiter la taille de la liste
        motsCles.unshift(motCle);
        if (motsCles.length > MAX_MOTS_CLES) {
            motsCles.pop();
        }
        sauvegarderMotsClesRecents(motsCles);
    }

        
    // Écouter les événements de clic sur chaque suggestion (div)
    suggestionsContainer.addEventListener('mousedown', function (event) {
        // Si l'événement provient de la croix
        if (event.target.classList.contains('close-icon')) {
            const suggestionDiv = event.target.closest('li');
            const motCle = suggestionDiv.querySelector('span').textContent;
            
            // Empêcher la propagation de l'événement click au conteneur parent (le <li>)
            event.stopPropagation();

            // Retirer la suggestion de l'historique
            retirerSuggestion(motCle);
        }
    });

    // Modifier la fonction d'affichage des suggestions
    function afficherSuggestions(textRecherche) {
        // Assurez-vous que textRecherche est une chaîne de caractères
        if (typeof textRecherche !== 'string') {
            console.log("textRecherche n'est pas une chaîne valide :", textRecherche);
            textRecherche = '';
        }

        const motsCles = chargerMotsClesRecents(); // Charger les mots-clés récents
        suggestionsContainer.innerHTML = ''; // Réinitialiser le conteneur


        // Filtrer les mots-clés selon la recherche
        let motsFiltres;
        if (textRecherche !== '') {
            motsFiltres = motsCles.filter(motCle =>
                motCle.toLowerCase().includes(textRecherche.toLowerCase())
            );
        } else {
            motsFiltres = motsCles;
        }

        // Si aucune suggestion, cacher le conteneur
        if (motsFiltres.length === 0) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        // Réafficher le conteneur pour les suggestions
        suggestionsContainer.style.display = 'block';

        // Ajouter chaque mot-clé comme élément de liste
        motsFiltres.forEach(motCle => {
            // Troncature si nécessaire
            let motTemp;
            if (motCle.length > NB_MAX_LENGTH) {
                motTemp = motCle.substring(0, NB_MAX_LENGTH) + '...';
            }

            // Créer un élément <li>
            const li = document.createElement('li');
            
            // Créer un div pour la suggestion
            const suggestionDiv = document.createElement('div');
            suggestionDiv.classList.add('suggestion-item'); // Classe pour la suggestion

            // Créer un élément pour le texte de la suggestion
            const textDiv = document.createElement('span');
            textDiv.textContent = motTemp || motCle; // Utiliser le texte tronqué si nécessaire
            suggestionDiv.appendChild(textDiv);

            if (textDiv.textContent === 'boite de nuit') {
                animerCartesBoiteDeNuit();
            }

            // Créer l'élément pour l'image de la croix
            const closeIcon = document.createElement('img');
            closeIcon.src = '../images/croix-noire.png';  // Image de la croix
            closeIcon.alt = 'Supprimer';
            closeIcon.classList.add('close-icon');  // Classe pour le style de la croix
            suggestionDiv.appendChild(closeIcon);

            // Ajouter l'élément div au li
            li.appendChild(suggestionDiv);

            // Attacher un écouteur d'événement pour gérer le clic sur la suggestion
            li.addEventListener('mousedown', function () {
                rechercheOffre.value = motCle; // Mettre le mot-clé dans le champ de recherche
                let rechercheIMG
                if (window.innerWidth > SCREEN_MOBILE) {
                    rechercheIMG = document.getElementById('rechercheOffreIMG');
                } else {
                    rechercheIMG = document.getElementById('rechercheOffreIMGmobile');
                }
                rechercheIMG.src = '../images/croix-noire.png';
                rechercheIMG.style.cursor = 'pointer';
                afficherResultats(filtrage()); // Effectuer la recherche
                suggestionsContainer.style.display = 'none'; // Masquer les suggestions
            });

            // Gestion du clic sur la croix pour supprimer une suggestion
            closeIcon.addEventListener('mousedown', function (e) {
                e.stopPropagation(); // Empêcher le clic de déclencher d'autres actions (comme remplir le champ)
                e.preventDefault(); // Empêcher les comportements par défaut si nécessaires
                retirerSuggestion(motCle); // Supprime le mot clé de l'historique
                afficherSuggestions(rechercheOffre.value); // Met à jour les suggestions
            });


            // Ajouter l'élément <li> au conteneur
            suggestionsContainer.appendChild(li);
        });

        // Positionner dynamiquement le conteneur sous le champ de texte
        const rect = rechercheOffre.getBoundingClientRect();
        suggestionsContainer.style.top = `${rect.bottom}px`;
        suggestionsContainer.style.left = `${rect.left}px`;
        suggestionsContainer.style.width = `${rect.width}px`;
    }

    

    // Fonction pour retirer un mot-clé de l'historique
    function retirerSuggestion(motCle) {
        let motsCles = chargerMotsClesRecents();
        motsCles = motsCles.filter(mot => mot !== motCle); // Retirer le mot de l'historique
        sauvegarderMotsClesRecents(motsCles); // Sauvegarder l'historique mis à jour
    }
        

    // Cacher les suggestions
    function cacherSuggestions() {
        suggestionsContainer.style.display = 'none';
    }

    // Événement pour enregistrer un mot-clé à chaque recherche
    // Fonction pour ajouter le mot-clé lors de différents événements
    function ajouterMotCleDepuisRecherche() {
        let motCle = rechercheOffre.value.trim();

        if (motCle) {
            ajouterMotCleRecent(motCle);
        }
    }

    // Gestion de l'événement "Entrée" dans le champ de recherche
    rechercheOffre.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            ajouterMotCleDepuisRecherche();
            afficherResultats(filtrage());
            cacherSuggestions();
        }

    });

    rechercheOffre.addEventListener('blur', function () {
        // Utiliser un setTimeout pour différer l'exécution après le clic sur une suggestion ou la croix
        setTimeout(() => {
            const activeElement = document.activeElement;
    
            // Vérifiez si l'élément actif est lié aux suggestions (li ou croix)
            if (
                !activeElement.closest('#suggestionsContainer') &&
                !activeElement.classList.contains('close-icon')
            ) {
                // Ajouter la recherche récente seulement si ce n'est pas une suppression
                if (rechercheOffre.value.trim() !== '') {
                    ajouterMotCleDepuisRecherche();
                }
                cacherSuggestions();
            }
        }, 200); // 200 ms suffisent pour gérer les clics
    });
    

    // Gestion du clic sur la croix d'effacement
    let rechercheOffreIMG
    if (window.innerWidth > SCREEN_MOBILE) {
        rechercheOffreIMG = document.getElementById('rechercheOffreIMG');
    } else {
        rechercheOffreIMG = document.getElementById('rechercheOffreIMGmobile');
    }

    rechercheOffreIMG.addEventListener('click', function () {
        if (rechercheOffre.value.trim() !== '') {
            ajouterMotCleDepuisRecherche();
        }
        rechercheOffre.value = ''; // Réinitialiser la recherche
        rechercheOffreIMG.src = '../images/search_icon.png'; // Réinitialiser l'icône
        rechercheOffreIMG.style.cursor = 'default';
        afficherResultats(filtrage());
    });

    // Afficher les suggestions lors du focus sur le champ
    rechercheOffre.addEventListener('focus', function () {
        afficherSuggestions(rechercheOffre.value.trim());
    });

    // Mettre à jour les suggestions lors de la saisie
    rechercheOffre.addEventListener('input', function () {
        const textRecherche = rechercheOffre.value.trim();
        afficherSuggestions(textRecherche); // Toujours afficher les suggestions, même si le champ est vide
    });

    // Cacher les suggestions lorsqu'on clique ailleurs
    document.addEventListener('click', function (e) {
        if (!rechercheOffre.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            cacherSuggestions();
        }
    });

    // Initialisation des suggestions au chargement
    cacherSuggestions();




    // ************** CONNERIES BOITE DE NUIT ************** //

    function animerCartesBoiteDeNuit() {
        const cartes = document.querySelectorAll('.carte_offre');

        // Crée un nouvel élément audio
        const audio = new Audio('../play.mp3');

        // Joue la musique
        audio.play();

        // Ajoute un délai de 5 secondes avant de continuer
        setTimeout(() => {
            // Code à exécuter après 5 secondes
        }, 5000);

        cartes.forEach((carte, index) => {
             // Génère un délai aléatoire entre 1000ms (1s) et 3000ms (3s)
            const delay = Math.random() * 4000 + 2000;

            // Applique la classe 'lol' après le délai
            setTimeout(() => {
                carte.classList.add('lol');
            }, delay);

            // Applique une animation continue de mouvement aléatoire
            function animateCarte() {
                const randomX = Math.random() * 400 - 200; // Entre -200 et 200
                const randomY = Math.random() * 400 - 200; // Entre -200 et 200
                const duration = Math.random() * 1000 + 500; // Entre 500ms et 1500ms
                carte.animate([
                    { transform: `translate(${randomX}px, ${randomY}px)` }
                ], {
                    duration: duration,
                    easing: 'ease-in-out',
                    fill: 'forwards'
                }).onfinish = animateCarte; // Relance l'animation à la fin
            }
            animateCarte();
        });


    }

    // animerCartesBoiteDeNuit();

    /* ================================= */
    /* ======= HISTORIQUE OFFRES ======= */
    /* ================================= */
    

    
});

function ajouterHistorique(offre) {
    let historique = JSON.parse(localStorage.getItem("historique_offres")) || [];

    console.log(historique);

    // Vérifier si l'offre est déjà dans la liste, si oui, on la supprime avant de l'ajouter en premier
    historique = historique.filter(o => o.id !== offre.id);

    // Ajouter l'offre au début du tableau
    historique.unshift(offre);

    // Garder seulement les 10 dernières offres
    historique = historique.slice(0, 10);

    // Sauvegarder dans le localStorage
    localStorage.setItem("historique_offres", JSON.stringify(historique));
}

export { ajouterHistorique };