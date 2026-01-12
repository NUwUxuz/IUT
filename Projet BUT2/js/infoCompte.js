const overlay = document.getElementById('overlay');
const popup = document.getElementById('popup_suppr_compte');
const group = document.getElementById("group");
const croix = document.getElementById('croix_small');
const boutonSupprimer = document.getElementById("delete-btn-compte");


function updateBodyClass() {
    const body = document.body;
    
    if (window.innerWidth > 800) {
        popup.classList.add('desktop');
        popup.classList.remove('mobile');


        group.classList.add('desktop');
        group.classList.remove('mobile');



        croix.classList.add('desktop');
        croix.classList.remove('mobile');
    } else {
        popup.classList.add('mobile');
        popup.classList.remove('desktop');


        group.classList.add('mobile');
        group.classList.remove('desktop');



        croix.classList.add('mobile');
        croix.classList.remove('desktop');
    }
}


updateBodyClass();


boutonSupprimer.addEventListener('click', function() {
    popup.classList.add("montrer");
    overlay.classList.add("montrer");
});

document.getElementById('croix_small').addEventListener('click', function() {
    popup.classList.remove("montrer"); // Désactive l'effet
    overlay.classList.remove("montrer");
});

overlay.addEventListener('click', function () {
    popup.classList.remove("montrer"); // Désactive l'effet
    overlay.classList.remove("montrer");
});



// Changement photo profil

let modif_photo_profil = document.getElementById("photo-profil");
let fileInput = document.getElementById("insert-image");
let boutonConfirmer = document.getElementById("info-compte-bouton-confirmer");
let selectedFile = null;

modif_photo_profil.addEventListener("click", function() {
    fileInput.click();
});

fileInput.addEventListener("change", function(event) {
    let file = event.target.files[0];
    if (file) {
        let reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("photo-profil").src = e.target.result;
        };
        reader.readAsDataURL(file);
        selectedFile = file; // Stocker temporairement le fichier
    }
});

boutonConfirmer.addEventListener("click", function(event) {

    if (selectedFile) {
            let formData = new FormData();
        formData.append("image-profil", selectedFile);

        fetch("update-photo-profil.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Image mise à jour avec succès !");
            } else {
                alert("Erreur lors de l'upload !");
            }
        })
        .catch(error => console.error("Erreur:", error));

    }
});


function openPopup() {
    document.getElementById('popup').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}
function closePopup() {
    document.getElementById('popup').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

async function checkPassword() {
    let password1 = document.getElementById('password-popup');
    let password2 = document.getElementById('password2-popup');
    const data = {
        password1: password1.value,
        password2: password2.value
    };
    if (data['password1'] === data['password2']) {
        console.log('test');
        try{
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({data})
            });

            if(response.ok){
                console.log('testbon');
                window.location.href = "../pages/codeCreate.php";
            }else{
                console.log('testnon');
                console.error('Erreur HTTP:', response.status);
            }
        }catch{
            console.error('Erreur réseau:', error);
        }
    }
}
