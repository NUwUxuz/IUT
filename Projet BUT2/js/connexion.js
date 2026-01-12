const submitButton = document.getElementById('submit-button');
const form = document.getElementsByClassName('login-form')[0];
const identifiant = document.getElementById('identifiant');

const divConnexion = document.getElementsByClassName('form-container-connexion')[0];

var nombreEssais = 0;

submitButton.addEventListener("click", async () => {
    // vérifie si secret OTP existe
    var xmlhttp = new XMLHttpRequest();
    var res = "";
    xmlhttp.onload = function() {
        res = this.responseText;
        //console.log("Secret existe : " + res);
    }
    xmlhttp.open("GET", "connexion.php?id=" + identifiant.value, false); // not async
    xmlhttp.send();
    if(res == "true" && document.getElementsByClassName("modal").length === 0){

        //création de pop-up de l'otp
        let otpDiv = document.createElement("div");
        let otpForm = document.createElement("form");
        otpForm.classList.add(".form-group-connexion");

        let otpTitle = document.createElement("h2");

        var otpError = document.createElement("p");
        otpError.style.color="red";
        otpError.id="otpError";
        otpError.innerHTML = "Donner un nombre valide";
        otpError.style.userSelect = "none";
        otpError.style.opacity = "0"; 

        let otpDivButtons = document.createElement("div");
        otpDivButtons.id = "boutons-modal";

        let otpSubmitButton = document.createElement("button");
        otpSubmitButton.innerHTML = "Valider";
        otpSubmitButton.classList.add("btn-notif");
        otpSubmitButton.disabled = true;
        otpSubmitButton.id = "modal-submit";
        otpSubmitButton.type = "button";

        let otpCancelButton = document.createElement("button");
        otpCancelButton.innerHTML = "Annuler";
        otpCancelButton.classList.add("btn-notif");
        otpCancelButton.id = "modal-close";
        otpCancelButton.type = "button";

        otpDivButtons.appendChild(otpSubmitButton);
        otpDivButtons.appendChild(otpCancelButton);

        otpDiv.classList.add("modal");
        otpTitle.classList.add("titre-modal");
        otpTitle.innerHTML = "Saisir votre code OTP";

        otpTitle.innerHTML = "Saisir votre code OTP";

        divConnexion.insertBefore(otpDiv, form.nextSibling);
        otpDiv.appendChild(otpTitle);
        otpDiv.appendChild(otpForm);
        let divInput = document.createElement("div");

        otpForm.appendChild(divInput);
        //création du form
        for (let i = 0; i < 6; i++) {
            const input = document.createElement('input'); // ajout de chaque input
            input.type = 'text';
            input.className = 'code-input';
            input.maxLength = 1;
            divInput.appendChild(input);
        }
        otpForm.appendChild(otpError);
        otpForm.appendChild(otpDivButtons);
        const fields = document.querySelectorAll('.code-input');

        //paste event pour code OTP
        fields.forEach((field) => {
            field.addEventListener('paste', (event) => {
                event.preventDefault();
                const pasteData = (event.clipboardData || window.clipboardData).getData('text').trim();

                if (/^\d{6}$/.test(pasteData)) {
                    // distribue les chiffres dans l'input fields
                    [...pasteData].forEach((char, idx) => {
                        if (fields[idx]) fields[idx].value = char;
                    });
                    fields[5].focus(); // focus dernier
                    verifyCodeOTP(Array.from(fields).map((field) => field.value).join('')); // vérifie code otp
                }
            });
        });

        //cookie pour l'erreur
        var cookieErreur = document.cookie
        .split("; ")
        .find((row) => row.startsWith("otp="))
        ?.split("=")[1];

        if (identifiant.value === cookieErreur) { // vérifie si c'est le compte qui est en timeout pour l'otp
            otpError.style.opacity = "1";
            otpError.innerHTML = "Trop d'essais réesayer plus tard";
            otpSubmitButton.disabled = true;
            fields.forEach(field =>{
                field.disabled = "disabled";
            });
        }

        // handle input
        fields.forEach((field, index) => {
            field.addEventListener('input', () => {
                if (field.value && index < fields.length - 1) {
                    fields[index + 1].focus();
                }
                checkCompletion(); // Check if all fields are filled
            });

            //change le focus du curseur si on appuie sur retour et que la case est vide
            field.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !field.value && index > 0) {
                    fields[index - 1].focus();
                }
            });
        });

        divInput.id="code-form";

        const checkCompletion = () => {
            const codeOTP = Array.from(fields).map((field) => field.value).join('');
            verifyCodeOTP(codeOTP);
            
        };

        // event listener pour boutons
        otpSubmitButton.addEventListener("click", checkOTP);

        otpCancelButton.addEventListener("click", async () => {
            otpDiv.remove();
        });

    }else{
        form.submit();
    }
    
})

//fonction de verif du code OTP à chaque input
function verifyCodeOTP(codeOTP) {

    res = true;
    let otpError = document.getElementById('otpError');
    let otpSubmitButton = document.getElementById('modal-submit');

    if (isNaN(codeOTP)) {
        otpError.style.opacity = "1";
        otpError.innerHTML = "Donner un nombre valide";
        otpSubmitButton.disabled = true;
        res = false;
    }else if(codeOTP.length < 6){
        otpSubmitButton.disabled = true;
    }else{
        otpError.style.opacity = "0";
        if (codeOTP.length == 6) {
            otpSubmitButton.disabled = false;
        }
    }
    return res;
}

// vérification suite à soumission du code OTP
function checkOTP(){
    var fields = document.querySelectorAll('.code-input');
    var codeOTP = Array.from(fields).map((field) => field.value).join('');
    let otpError = document.getElementById('otpError');

    var res = true;
    //véride si OTP est correct
    var xmlhttp = new XMLHttpRequest();
    var rep = "";
    xmlhttp.onload = function() {
        rep = this.responseText;
    }
    
    xmlhttp.open("GET", "connexion.php?id=" + identifiant.value + "&otp=" + codeOTP, false); // not async
    xmlhttp.send();
    res = rep == "true" ? true : false;

    if (res) {
        document.getElementsByClassName("modal")[0].remove(); // supprime la fenêtre modale
        form.submit();
    }else{
        fields.forEach(field =>{
            field.value = "";
        });
        otpError.innerHTML = "Code OTP invalide";
        otpError.style.opacity = "1";
        nombreEssais= nombreEssais + 1;
    }
    // si nombre d'essais est supérieur à 3
    if (nombreEssais>=3) {
        fields.forEach(field =>{
            field.disabled = "disabled";
        });
        otpError.innerHTML = "Trop d'essais réessayer plus tard";
        document.getElementById('modal-submit').disabled = "disabled";

        var now = new Date();
        var time = now.getTime();
        var expireTime = time +  5*60*1000; // expire après 5 minutes
        now.setTime(expireTime);
        document.cookie = 'otp='+identifiant.value+';expires='+now.toUTCString()+';path=/';
    }
    return res;
}