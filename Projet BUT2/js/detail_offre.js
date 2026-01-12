document.addEventListener("DOMContentLoaded", function () {
    console.log("test");
    if(document.getElementById("etoile_note1") !== null){

        const etoile_plein = "../icons/etoile_bleue.png";
        const etoile_vide= "../icons/etoile_bleue_vide.png";
        let etoile1 = document.getElementById("etoile_note1");
        let etoile2 = document.getElementById("etoile_note2");
        let etoile3 = document.getElementById("etoile_note3");
        let etoile4 = document.getElementById("etoile_note4");
        let etoile5 = document.getElementById("etoile_note5");

        let nbr_etoile = document.getElementById("nbr_etoile");

        // initialisation étoile
        let initNote = document.getElementById("nbr_etoile");
        if(initNote.value === ''){
            etoile1.src=(etoile_vide);
            etoile2.src=(etoile_vide);
            etoile3.src=(etoile_vide);
            etoile4.src=(etoile_vide);
            etoile5.src=(etoile_vide);

            etoile1.addEventListener("mouseover", modif_etoile);
            etoile2.addEventListener("mouseover", modif_etoile);
            etoile3.addEventListener("mouseover", modif_etoile);
            etoile4.addEventListener("mouseover", modif_etoile);
            etoile5.addEventListener("mouseover", modif_etoile);

            etoile1.addEventListener("mouseout", modif_etoile);
            etoile2.addEventListener("mouseout", modif_etoile);
            etoile3.addEventListener("mouseout", modif_etoile);
            etoile4.addEventListener("mouseout", modif_etoile);
            etoile5.addEventListener("mouseout", modif_etoile);
        }else if(initNote.value === "1"){
            etoile1.src=(etoile_plein);
            etoile2.src=(etoile_vide);
            etoile3.src=(etoile_vide);
            etoile4.src=(etoile_vide);
            etoile5.src=(etoile_vide);
        }else if(initNote.value === "2"){
            etoile1.src=(etoile_plein);
            etoile2.src=(etoile_plein);
            etoile3.src=(etoile_vide);
            etoile4.src=(etoile_vide);
            etoile5.src=(etoile_vide);
        }else if(initNote.value === "3"){
            console.log(3);
            etoile1.src=(etoile_plein);
            etoile2.src=(etoile_plein);
            etoile3.src=(etoile_plein);
            etoile4.src=(etoile_vide);
            etoile5.src=(etoile_vide);
        }else if(initNote.value === "4"){
            etoile1.src=(etoile_plein);
            etoile2.src=(etoile_plein);
            etoile3.src=(etoile_plein);
            etoile4.src=(etoile_plein);
            etoile5.src=(etoile_vide);
        }else{
            etoile1.src=(etoile_plein);
            etoile2.src=(etoile_plein);
            etoile3.src=(etoile_plein);
            etoile4.src=(etoile_plein);
            etoile5.src=(etoile_plein);
        }


        etoile1.addEventListener("click", modif_etoile);
        etoile2.addEventListener("click", modif_etoile);
        etoile3.addEventListener("click", modif_etoile);
        etoile4.addEventListener("click", modif_etoile);
        etoile5.addEventListener("click", modif_etoile);


        function modif_etoile(event){
            let etoileid = event.target.id;
            
            if(event.type === "mouseover"){
                if(etoileid === "etoile_note1"){
                    etoile1.src=(etoile_plein);
                }else if(etoileid === "etoile_note2"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                }else if(etoileid === "etoile_note3"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_plein);
                }else if(etoileid === "etoile_note4"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_plein);
                    etoile4.src=(etoile_plein);
                }else{
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_plein);
                    etoile4.src=(etoile_plein);
                    etoile5.src=(etoile_plein);
                }
            }else if(event.type==="mouseout"){
                if(etoileid === "etoile_note1"){
                    etoile1.src=(etoile_vide);
                }else if(etoileid === "etoile_note2"){
                    etoile1.src=(etoile_vide);
                    etoile2.src=(etoile_vide);
                }else if(etoileid === "etoile_note3"){
                    etoile1.src=(etoile_vide);
                    etoile2.src=(etoile_vide);
                    etoile3.src=(etoile_vide);
                }else if(etoileid === "etoile_note4"){
                    etoile1.src=(etoile_vide);
                    etoile2.src=(etoile_vide);
                    etoile3.src=(etoile_vide);
                    etoile4.src=(etoile_vide);
                }else{
                    etoile1.src=(etoile_vide);
                    etoile2.src=(etoile_vide);
                    etoile3.src=(etoile_vide);
                    etoile4.src=(etoile_vide);
                    etoile5.src=(etoile_vide);
                }
            }else{
                if(etoileid === "etoile_note1"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_vide);
                    etoile3.src=(etoile_vide);
                    etoile4.src=(etoile_vide);
                    etoile5.src=(etoile_vide);
                    nbr_etoile.value = 1;
                }else if(etoileid === "etoile_note2"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_vide);
                    etoile4.src=(etoile_vide);
                    etoile5.src=(etoile_vide);
                    nbr_etoile.value = 2;
                }else if(etoileid === "etoile_note3"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_plein);
                    etoile4.src=(etoile_vide);
                    etoile5.src=(etoile_vide);
                    nbr_etoile.value = 3;
                }else if(etoileid === "etoile_note4"){
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_plein);
                    etoile4.src=(etoile_plein);
                    etoile5.src=(etoile_vide);
                    nbr_etoile.value = 4;
                }else{
                    etoile1.src=(etoile_plein);
                    etoile2.src=(etoile_plein);
                    etoile3.src=(etoile_plein);
                    etoile4.src=(etoile_plein);
                    etoile5.src=(etoile_plein);
                    nbr_etoile.value = 5;
                }

                etoile1.removeEventListener("mouseover", modif_etoile);
                etoile2.removeEventListener("mouseover", modif_etoile);
                etoile3.removeEventListener("mouseover", modif_etoile);
                etoile4.removeEventListener("mouseover", modif_etoile);
                etoile5.removeEventListener("mouseover", modif_etoile);

                etoile1.removeEventListener("mouseout", modif_etoile);
                etoile2.removeEventListener("mouseout", modif_etoile);
                etoile3.removeEventListener("mouseout", modif_etoile);
                etoile4.removeEventListener("mouseout", modif_etoile);
                etoile5.removeEventListener("mouseout", modif_etoile);
            }
        }
    }

    let boutons_supp = document.getElementsByName('bouton_supp');

    function supp_avis(event) {
        let form = event.target.closest('form');
        let confirmation = confirm("Voulez-vous supprimer votre avis ?");
        if (confirmation) {
            form.submit();
            form.reset();
        }
    }

    boutons_supp.forEach(bouton => {
        bouton.addEventListener("click", supp_avis);
    });

    // document.addEventListener("DOMContentLoaded", () => {
    //     const signalButtons = document.querySelectorAll('.btn-signaler'); // Sélectionne tous les boutons "Signaler"
    //     const popup = document.getElementById('popup');
    //     const closeBtn = popup.querySelector('.close-btn');
    //     const progressBar = popup.querySelector('.progress-bar div');

    //     let timeout;

    //     signalButtons.forEach((button) => {
    //         button.addEventListener('click', () => {
    //             // Réinitialiser l'animation de la barre
    //             progressBar.style.animation = 'none';
    //             progressBar.offsetHeight; // Déclenche un reflow pour redémarrer l'animation
    //             progressBar.style.animation = ''; // Restaurer l'animation


    //             popup.classList.add('active');
    //             clearTimeout(timeout);
    //             timeout = setTimeout(() => {
    //                 popup.classList.remove('active');
    //             }, 4000);
    //         });
    //     });

    //     closeBtn.addEventListener('click', () => {
    //         popup.classList.remove('active');
    //         clearTimeout(timeout);
    //     });
    // });


    function toggle_reponse(id) {
        var form = document.getElementById("form_reponse_" + id);
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "inline";
        } else {
            form.style.display = "none";
        }
    }




    const likeButton = document.querySelectorAll('[id^="likeButton"]');
    const dislikeButton = document.querySelectorAll('[id^="dislikeButton"]');
    const likesCount = document.querySelectorAll('[id^="likesCount"]');
    const dislikesCount = document.querySelectorAll('[id^="dislikesCount"]');

    likeButton.forEach(button => {
        button.addEventListener("click", async (event) => {
            // Use event.currentTarget to get the button element, not a child element
            const datas = event.currentTarget.id.split('_');
            console.log(datas);
            const data = {
                id_compte: datas[1],
                id_avis: datas[2],
            };

            try {
                const response = await fetch('../pages/update_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    const result = await response.json();
                    console.log(result);
                    const likesCount = button.closest('.like-dislike').querySelector(`[id^="likesCount_${datas[2]}"]`);
                    const dislikesCount = button.closest('.like-dislike').querySelector(`[id^="dislikesCount_${datas[2]}"]`);
                    if (result.nbr_likes !== undefined) {
                        likesCount.textContent = result.nbr_likes;
                        dislikesCount.textContent = result.nbr_dislikes;
                    }
                } else {
                    console.error('Erreur HTTP:', response.status);
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
            }
        });
    });

    dislikeButton.forEach(button => {
        button.addEventListener("click", async (event) => {
            const datas = event.currentTarget.id.split('_');
            console.log(datas);
            const data = {
                id_compte: datas[1],
                id_avis: datas[2],
            };

            try {
                const response = await fetch('../pages/update_dislike.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    const result = await response.json();
                    console.log(result);
                    const dislikesCount = button.closest('.like-dislike').querySelector(`[id^="dislikesCount_${datas[2]}"]`);
                    const likesCount = button.closest('.like-dislike').querySelector(`[id^="likesCount_${datas[2]}"]`);
                    if (result.nbr_dislikes !== undefined) {
                        dislikesCount.textContent = result.nbr_dislikes;
                        likesCount.textContent = result.nbr_likes;
                    }
                } else {
                    console.error('Erreur HTTP:', response.status);
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
            }
        });
    });


    const signalerAvisBtn = document.querySelectorAll('[id^="btn_signaler_avis"]');
    console.log(signalerAvisBtn);
    const signalerReponseBtn = document.querySelectorAll('[id^="btn_signaler_reponse"]');

    const popupWarn = document.getElementById('popup');
    const closeBtn = popupWarn.querySelector('.close-btn');
    const progressBar = popupWarn.querySelector('.progress-bar div');


    document.querySelectorAll('.btn-signaler').forEach(button => {
        // Récupérer l'état initial depuis localStorage
        const buttonId = button.id; // Utiliser l'ID unique du bouton pour l'identifier
        const isAlreadySignaled = localStorage.getItem(`signal_${buttonId}`) === 'true';

        if (isAlreadySignaled) {
            // Désactiver le bouton et mettre à jour le style s'il a déjà été signalé
            button.disabled = true;
            button.style.cursor = 'not-allowed';
            button.style.opacity = '0.5';
            button.querySelector('p').textContent = 'Déjà signalé';
        }

        // Ajouter l'écouteur d'événement pour le clic
        button.addEventListener("click", async () => {
            const datas = button.id.split('_');
            console.log(datas);
            console.log(event.target.id);

            const data = {
                id_compte: datas[3],
                id_avis: datas[4],
            };

            try {
                let confirmation = confirm("Voulez-vous signaler cet avis ?");
                if (confirmation) {
                    const response = await fetch('../pages/update_signal_avis.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    if (response.ok) {
                        let timeout;

                        // Désactiver le bouton et mettre à jour le style
                        button.disabled = true;
                        button.style.cursor = 'not-allowed';
                        button.style.opacity = '0.5';
                        button.querySelector('p').textContent = 'Déjà signalé';

                        // Sauvegarder l'état dans localStorage
                        localStorage.setItem(`signal_${buttonId}`, 'true');

                        // Réinitialiser l'animation de la barre
                        progressBar.style.animation = 'none';
                        progressBar.offsetHeight; // Déclenche un reflow pour redémarrer l'animation
                        progressBar.style.animation = ''; // Restaurer l'animation

                        // Afficher le popup de confirmation
                        popupWarn.classList.add('active');
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            popupWarn.classList.remove('active');
                        }, 4000);
                    } else {
                        console.error('Erreur HTTP:', response.status);
                    }
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
            }
        });
    });

    // Gestion du bouton de fermeture du popup
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            popupWarn.classList.remove('active');
        });
    }


    signalerReponseBtn.forEach(button => {

        
        button.addEventListener("click", async () => {

            datas = button.id.split('_');
            console.log(datas);
            console.log(event.target.id)
            const data = {
                id_compte: datas[3],
                id_avis: datas[4],
            };
        
            try {
                
                const response = await fetch('../pages/update_signal_reponse.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
        
                if (response.ok) {
                    let timeout;

                // Désactiver le bouton et mettre à jour le style
                button.disabled = true;
                button.style.cursor = 'not-allowed';
                button.style.opacity = '0.5';
                button.querySelector('p').textContent = 'Déjà signalé';

                
                // Réinitialiser l'animation de la barre
                progressBar.style.animation = 'none';
                progressBar.offsetHeight; // Déclenche un reflow pour redémarrer l'animation
                progressBar.style.animation = ''; // Restaurer l'animation

                popupWarn.classList.add('active');
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    popupWarn.classList.remove('active');
                }, 4000);
                    
                    
                } else {
                    console.error('Erreur HTTP:', response.status);
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
            }
        });
        },

        closeBtn.addEventListener('click', () => {
            popupWarn.classList.remove('active');
            clearTimeout(timeout);
        })
    );

    /* popup blacklist*/

    const blacklistBtn = document.querySelectorAll('.btn-blacklist');
    const blacklistBtnRev = document.querySelectorAll('.btn-blacklist-rev');
    const blacklistBtnIrr = document.querySelectorAll('.btn-blacklist-irr');

    const modal = document.getElementById('modal');
    const modal_warning = document.getElementById('modal-warning');

    const input_semaine = document.getElementById('semaine');
    const warning_semaine = document.getElementById('warning-semaine');
    const submit_blacklist = document.getElementById('modal-submit-button');

    // popup de confimation finale
    const confirm_blacklist = document.getElementById('modal-warning-submit-button');

    if (input_semaine !== null) {
        input_semaine.style.maxWidth = "3em";
    }


    //TEST DES DONNES DU FORMULAIRE
    function check_actif() {
        var valide = true;
        let semaine = input_semaine.value;
        if (semaine === "" || isNaN(semaine) || semaine < 1 || semaine % 1 != 0) {
            warning_semaine.style.visibility = 'visible';
            valide = false;
        }

        return valide;
    }

    const onClickOutside = (e) => {
        if ((!modal.contains(e.target) && !modal_warning.contains(e.target)) && (modal.style.display == "block"|| modal_warning.style.display == "block")) {
            modal.style.display = "none";
            modal_warning.style.display = "none";
            document.getElementById('overlay').style.display = "none";
            window.removeEventListener("click",onClickOutside);
        }
    };

    // Bouton pour menu blacklistage
    blacklistBtn.forEach(button => {
        var menu = button.nextElementSibling;
        button.addEventListener("click", async () => {
            if (menu.style.display == 'none') {
                menu.style.display = 'flex';
            }else{
                menu.style.display = 'none';
            }
        });
        },
    );

    // Bouton pour blacklistage temporaire
    blacklistBtnRev.forEach(button => {
        button.addEventListener("click", async () => {
            input_semaine.value = "1";
            warning_semaine.style.visibility = 'hidden';
            
            window.addEventListener("click",onClickOutside, true);
            
            modal.style.display = 'block';
            document.getElementById('overlay').style.display = "block";
            datas = button.id.split('_');

            button.parentElement.style.display = 'none';
        });
        },
    );

    // Bouton pour blacklistage permanent
    blacklistBtnIrr.forEach(button => {
        button.addEventListener("click", async () => {
            
            datas = button.id.split('_');
            button.parentElement.style.display = 'none';
            window.addEventListener("click",onClickOutside, true);
            input_semaine.value = "";
            modal_warning.style.display = "block";
            document.getElementById('overlay').style.display = "block";
        });
        },
    );

    if (input_semaine !== null && submit_blacklist !== null) {
        // input pour la durée de blacklistage
        input_semaine.addEventListener('input', async () =>{
            if (!check_actif()) {
                submit_blacklist.disabled = true;
            }else{
                submit_blacklist.disabled = false;
            }
        })

        // Bouton de soumission de la fenêtre modale
        submit_blacklist.addEventListener('click', async () => {
            if (check_actif()) {
                warning_semaine.style.visibility = 'hidden';
                modal_warning.style.display = "block";
            }
        });

        confirm_blacklist.addEventListener('click', async () => {
            var data = {
                id_avis: datas[3],
                duree: input_semaine.value,
                blacklist: "true"
            };
    
            try{
                //cache le truc
                modal.style.display = 'none';
                modal_warning.style.display = "none";
                document.getElementById('overlay').style.display = "none";
    
                // met à jour la bdd pour blacklister
                const response = await fetch('../pages/update_blacklist_avis.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                if (response.ok) {
                    console.log('Réponse reçue!');
                } else {
                    console.error('Erreur HTTP:', response.status);
                }
            } catch (error) {
                console.error('Erreur réseau:', error);
            }
        });

        // bouton de fermeyture de la fenêtre modale
        document.getElementById('modal-close').addEventListener('click', function() {
            modal.style.display = 'none';
            document.getElementById('overlay').style.display = "none";
        });

        // bouton de fermeyture de la fenêtre modale
        document.getElementById('modal-warning-close').addEventListener('click', function() {
            modal.style.display = 'none';
            modal_warning.style.display = "none";
            document.getElementById('overlay').style.display = "none";
        });
    }

    

    

    

    // Confirmation de blackliste

    /*
    Popup confirmation signalement
    document.addEventListener("DOMContentLoaded", () => {
        const popup = document.getElementById('popup');
        const closeBtn = popup.querySelector('.close-btn');
        const progressBar = popup.querySelector('.progress-bar div');

        let timeout;

        signalButtons.forEach((button) => {
            button.addEventListener('click', () => {
                // Réinitialiser l'animation de la barre
                progressBar.style.animation = 'none';
                progressBar.offsetHeight; // Déclenche un reflow pour redémarrer l'animation
                progressBar.style.animation = ''; // Restaurer l'animation

                popup.classList.add('active');
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    popup.classList.remove('active');
                }, 4000);
            });
        });

        closeBtn.addEventListener('click', () => {
            popup.classList.remove('active');
            clearTimeout(timeout);
        });
    });
    */
    const btnBlacklist = document.getElementById('btn-blacklist');

    if (btnBlacklist) {
        const nbrAvisBlacklist = btnBlacklist.getAttribute('data-nbr-avis-blacklist');

        btnBlacklist.addEventListener('click', function () {
            // Récupère tous les avis blacklistés
            const blacklistedAvis = document.querySelectorAll('.blacklisted');

            // Vérifie si les avis blacklistés sont actuellement affichés
            const isShowing = document.body.classList.toggle('show-blacklisted');

            // Ajouter la classe "clicked" au bouton lorsqu'il est cliqué
            this.classList.toggle('clicked');

            // Change le texte du bouton
            this.textContent = isShowing
                ? `Masquer avis blacklistés (${nbrAvisBlacklist} / 3)`
                : `Afficher avis blacklistés (${nbrAvisBlacklist} / 3)`;

            // Affiche ou masque les avis blacklistés
            blacklistedAvis.forEach(avis => {
                avis.style.display = isShowing ? 'block' : 'none';
            });
        });
    }


    const overlay = document.getElementById('overlay');
    const popup = document.getElementById('popup_avis');
    const logo = document.getElementById("logo");
    const group = document.getElementById("group");
    const debutTitre = document.getElementById("debut");
    const finTitre = document.getElementById("fin");
    const groupFormulaire = document.querySelectorAll(".form-group");
    const connexionBouton = document.getElementById("connexion_popup");
    const croix = document.getElementById('croix');
    const boutonPublier = document.getElementById("bouton_publier");

    function updateBodyClass() {
        // const body = document.body;
        
        if (window.innerWidth > 800) {
            popup.classList.add('desktop');
            popup.classList.remove('mobile');

            logo.classList.add('desktop');
            logo.classList.remove('mobile');


            group.classList.add('desktop');
            group.classList.remove('mobile');

            debutTitre.classList.add('desktop');
            debutTitre.classList.remove('mobile');

            finTitre.classList.add('desktop');
            finTitre.classList.remove('mobile');

            groupFormulaire.forEach(groupF => {
                // Action à réaliser pour chaque élément avec la classe 'group'
                groupF.classList.add('desktop');
                groupF.classList.remove('mobile');
            });

            connexionBouton.classList.add('desktop');
            connexionBouton.classList.remove('mobile');

            croix.classList.add('desktop');
            croix.classList.remove('mobile');
        } else {
            popup.classList.add('mobile');
            popup.classList.remove('desktop');

            logo.classList.add('mobile');
            logo.classList.remove('desktop');

            group.classList.add('mobile');
            group.classList.remove('desktop');

            debutTitre.classList.add('mobile');
            debutTitre.classList.remove('desktop');

            finTitre.classList.add('mobile');
            finTitre.classList.remove('desktop');

            groupFormulaire.forEach(groupF => {
                // Action à réaliser pour chaque élément avec la classe 'group'
                groupF.classList.add('mobile');
                groupF.classList.remove('desktop');
            });

            connexionBouton.classList.add('mobile');
            connexionBouton.classList.remove('desktop');

            croix.classList.add('mobile');
            croix.classList.remove('desktop');
        }
    }

    // Initialisation au chargement de la page
    updateBodyClass();

    boutonPublier.addEventListener('click', function() {
        popup.classList.add("montrer");
        overlay.classList.add("montrer");
    });
    
    document.getElementById('croix').addEventListener('click', function() {
        popup.classList.remove("montrer"); // Désactive l'effet
        overlay.classList.remove("montrer");
    });
    
    overlay.addEventListener('click', function () {
        popup.classList.remove("montrer"); // Désactive l'effet
        overlay.classList.remove("montrer");
    });

    // Récupérer le formulaire
    const form = document.getElementById('formConnexion');
    const nbr_etoile = document.getElementById("nbr_etoile");
    const titreAvisRedige = document.getElementById('titre_avis');
    const contenuAvisRedige = document.getElementById('ecrire_avis');
    const contextAvisRedige = document.getElementById('options-contexte');

    const etoile = document.getElementById('etoile');
    const titreAvisRecu = document.getElementById('titreAvis');
    const contenuAvisRecu = document.getElementById('contenuAvis');
    const contextAvisRecu = document.getElementById('contextAvis');



    // Ajouter un gestionnaire pour l'événement submit
    form.addEventListener('submit', function(event) {
        titreAvisRecu.value = titreAvisRedige.value;
        contenuAvisRecu.value = contenuAvisRedige.value;
        contextAvisRecu.value = contextAvisRedige.value;
        if(nbr_etoile.value !== ''){
            etoile.value = nbr_etoile.value;
        } else {
            nbr_etoile.value = '1';
        }
        
        
    });

    // Écoute des changements de taille de la fenêtre
    window.addEventListener('resize', updateBodyClass);
});



