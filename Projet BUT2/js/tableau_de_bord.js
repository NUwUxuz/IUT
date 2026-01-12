function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
  }

/*MODAL*/

//TEST DES DONNES DU FORMULAIRE
function check_actif() {
    var valide = true;
    if (!document.getElementById('condition1').checked) {
        valide = false;
    }
    if (document.getElementById('option-modal').style.display !== 'none') {
        if (document.getElementById('semaine') == null) {
            valide = false;
        }
        if (!check_date_option()) {
            valide = false;
        }
    }
    if (!document.getElementById('condition2').checked) {
        valide = false;
    }
    
    return valide;
}

document.getElementById('modal').style.display = 'none';

document.getElementById('modal-close').addEventListener('click', function() {
    document.getElementById('modal').style.display = 'none';
    // Remet offre hors ligne
    var idOffre = document.getElementById('modal-id').value;
    var toggle = document.querySelectorAll('input[idoffre="'+idOffre+'"]')[0];
    toggle.click();
});

document.getElementById('modal-submit-button').addEventListener('click', async () => {
    if (check_actif()) {
        if (document.getElementById('option-modal').style.display !== 'none') {
            var data = [document.getElementById('modal-id').value, document.getElementById('date_lancement').value, document.getElementById('semaine').value];
            const response = await fetch('../pages/update_option.php', {
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
        }
        
        document.getElementById('modal').style.display = 'none';

    }
});

//MODAL DATEs

var date_lancement = document.getElementById("date_lancement");
var error = document.getElementById('error');
date_lancement.addEventListener("input", function() {
    var d = new Date(date_lancement.value);
    //returns true if monday
    var today = new Date();
    today.setHours(0,0,0,0)
    if (d.getDay()!==1 || d.getTime()< today) {
        error.textContent = "Date de lancement doit être un Lundi prochain";
    }else{
        error.textContent = "";
    }
});

//Contenu modal
/*
document.querySelector("#tarif-modal > p").innerHTML = "Tarif mensuel : X/HT X/TTC";
document.querySelector("#tarif-modal > p + p").innerHTML = "Tarif prévisonnel : X/HT X/TTC";
document.querySelector("#option-modal > p").innerHTML = "Vous avez choisit l'option X, avec un tarif hebdomadaire de X€";
document.getElementById('option-modal').style.display = 'flex';
*/
function check_date_option(){
    var date_lancement = document.getElementById("date_lancement");
    var res;

    var d = new Date(date_lancement.value);
    var today = new Date();
    today.setHours(0,0,0,0)
    //returns true if monday
    if (d.getDay()!==1 || d.getTime()< today) {
        res = false;
    }else{
        res = true;
    }
    return res;
}

//////////////////////////////////////////////////////////////

/*
let toggles = document.querySelectorAll('input[type="checkbox"]');

function setCheckboxValue(form) {
    // Si la case est cochée, envoyez 'unchecked', sinon envoyez 'checked'
    var checkbox = form.querySelector('input[name="status"]');
    if (!checkbox.checked) {
        checkbox.value = 'checked'; // Modifiez la valeur si elle est décochée
    } else {
        checkbox.value = 'unchecked'; // Valeur par défaut si elle est cochée
    }
}


function appuieToggle(event) {
    let toggle = event.target;
    let section = toggle.closest('section');
    let form = toggle.closest('form');  // Trouver le formulaire associé

    if (toggle.checked) {
        let confirmation = confirm("Êtes-vous sûr de vouloir passer cette offre en ligne ?");
        if (confirmation) {
            toggle.checked = true;
            section.style.setProperty("opacity", 1);
            form.submit();  // Soumettre le formulaire si confirmé
        } else {
            toggle.checked = false;
            section.style.setProperty("opacity", 0.5);
        }
    } else {
        let confirmation = confirm("Êtes-vous sûr de vouloir passer cette offre hors ligne ?");
        if (confirmation) {
            toggle.checked = false;
            section.style.setProperty("opacity", 0.5);
            form.submit();  // Soumettre le formulaire si confirmé
        } else {
            toggle.checked = true;
            section.style.setProperty("opacity", 1);
        }
    }    
}

// Associer l'événement à chaque toggle
toggles.forEach(toggle => {
    if(!toggle.checked){
        toggle.closest('section').style.setProperty("opacity", 0.5);
    }
    toggle.addEventListener("click", appuieToggle);
});
*/



let toggles = document.querySelectorAll('input[type="checkbox"]');

function validateAndSubmitForm(event) {
    event.preventDefault(); // Empêche la soumission par défaut du formulaire

    let form = event.target.closest('form');
    let toggle = form.querySelector('input[type="checkbox"]');
    let section = toggle.closest('section');

    // Vérification de l'IBAN et BIC avant la soumission du formulaire
    // var ibanBicIncomplets = true;  // Modifiez selon votre logique
    var ibanBicIncomplets = form.querySelector('input[name="status"]').getAttribute('iban-bic-data');
    var typeCompte = 'pro_prive';  // Modifiez selon votre logique

    if (typeCompte === 'pro_prive' && ibanBicIncomplets) {
        alert("Veuillez remplir vos coordonnées bancaires (IBAN et BIC) pour mettre cette offre en ligne.");
        return false; // Empêche la soumission du formulaire
    }

    // Si la case est cochée, envoyez 'unchecked', sinon envoyez 'checked'
    if (!toggle.checked) {
        toggle.value = 'checked'; // Modifiez la valeur si elle est décochée
    } else {
        toggle.value = 'unchecked'; // Valeur par défaut si elle est cochée
    }

    if (toggle.checked) {
        let confirmation = confirm("Êtes-vous sûr de vouloir passer cette offre en ligne ?");
        if (confirmation) {
            toggle.checked = true;
            section.style.setProperty("opacity", 1);
            form.submit();  // Soumettre le formulaire si confirmé
        } else {
            toggle.checked = false;
            section.style.setProperty("opacity", 0.5);
        }
    } else {
        let confirmation = confirm("Êtes-vous sûr de vouloir passer cette offre hors ligne ?");
        if (confirmation) {
            toggle.checked = false;
            section.style.setProperty("opacity", 0.5);
            form.submit();  // Soumettre le formulaire si confirmé
        } else {
            toggle.checked = true;
            section.style.setProperty("opacity", 1);
        }
    }
}

// Associer l'événement à chaque toggle
/*toggles.forEach(toggle => {
    if(!toggle.checked){
        toggle.closest('section').style.setProperty("opacity", 0.5);
    }
    toggle.addEventListener("click", function(event) {
        event.preventDefault(); // Empêche la soumission par défaut du formulaire
        validateAndSubmitForm(event);
    });
});*/


toggles.forEach(toggle => {
    let section = toggle.closest('section');


    if (toggle.checked) {
        section.style.opacity = 1;
    } else {
        section.style.opacity = 0.5;
    }

    toggle.addEventListener("click", async () => {

        datas = event.target.id.split('_');
        const data = {
            id_offre: datas[1],
            status_offre: datas[2],
        };

        let section = toggle.closest('section');
        let form = event.target.closest('form');

        var ibanBicIncomplets = form.querySelector('input[name="status"]').getAttribute('iban-bic-data');
        var typeCompte = getCookie("type_compte"); 

        if (typeCompte === 'pro_prive' && ibanBicIncomplets && data['status_offre']  !== '0') {
            alert("Veuillez remplir vos coordonnées bancaires (IBAN et BIC) pour mettre cette offre en ligne.");
            // Empêche l'animation
            toggle.checked = false;
            return false;
        }else{
            if (typeCompte === 'pro_prive' && toggle.checked) {

                document.getElementById('modal').style.display = 'block';
                document.getElementById('modal-id').value = data['id_offre'];
                document.querySelector('#modal > form').reset();

                var offerData = form.querySelector('#previsionnel').getAttribute('mHT-mTTC-HT-TTC-opHT-opTTC-nomOP').split("_");
                let dataO = {
                    mHT: offerData[0],
                    mTTC: offerData[1],
                    HT: offerData[2],
                    TTC: offerData[3],
                    opHT: offerData[4],
                    opTTC: offerData[5],
                    nomOP: offerData[6]
                };
                
                document.querySelector("#tarif-modal > p").innerHTML = "Tarif mensuel : " + dataO['mHT'] + "€/HT " + dataO['mTTC'] + "€/TTC";
                document.querySelector("#tarif-modal > p + p").innerHTML = "Tarif prévisonnel : " + dataO['HT'] + "€/HT " + dataO['TTC'] +"€/TTC";
                if (dataO['opHT'] === "") {
                    document.getElementById('option-modal').style.display = 'none';
                    
                }else{
                    document.getElementById('option-modal').style.display = 'flex';
                    document.querySelector("#option-modal > p").innerHTML = "Vous avez choisit l'option " + '"' + dataO['nomOP'] + '"' +", avec un tarif hebdomadaire de " + dataO['opHT'] + "€/HT "+ dataO['opTTC'] + "€/TTC";
                    document.getElementById('option-modal').style.display = 'flex';
                }
            }else{
                document.getElementById('modal').style.display = 'none';
            }
            // Si la case est cochée, envoyez 'unchecked', sinon envoyez 'checked'
            if (!toggle.checked) {
                toggle.value = 'checked'; // Modifiez la valeur si elle est décochée
            } else {
                toggle.value = 'unchecked'; // Valeur par défaut si elle est cochée
            }


            if (!toggle.checked) {
                toggle.value = 'checked'; // Modifiez la valeur si elle est décochée
            } else {
                toggle.value = 'unchecked'; // Valeur par défaut si elle est cochée
            }
        
            if (toggle.checked) {
                toggle.checked = true;
                section.style.setProperty("opacity", 1);
                data['status_offre'] = '1';
            } else {
                toggle.checked = false;
                section.style.setProperty("opacity", 0.5);
                data['status_offre'] = '0';
            }
        
            try {
                
                const response = await fetch('../pages/update_tableau_de_bord.php', {
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

        }

        
    });
    }
);

