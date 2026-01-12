// Fonction pour afficher la popup
function showPopup() {
    alert("Popup fonctionne !");
}

// Vérification de l'IBAN et BIC avant la soumission du formulaire
function checkBankDetails() {
    var ibanBicIncomplets = true;  // Modifiez selon votre logique
    var typeCompte = 'pro_prive';  // Modifiez selon votre logique

    console.log("iban_bic_incomplets : " + ibanBicIncomplets);
    console.log("type_compte : " + typeCompte);

    if (typeCompte === 'pro_prive' && ibanBicIncomplets) {
        alert("Veuillez remplir vos coordonnées bancaires (IBAN et BIC) pour valider les informations.");
        return false; // Empêche la soumission du formulaire
    }
    return true; // Autorise la soumission du formulaire
}

// Fonction de validation combinée
function validateForm(form) {
    showPopup();
    return checkBankDetails();
}