let bouton_supp = document.querySelectorAll('input[type="button"]');

function supp_avis(event){
    let form = event.target.closest('form');
    let confirmation = confirm("Voulez vous supprimer votre avis ?");
    if (confirmation){
        form.submit();
        form.reset();
    }
}

bouton_supp.forEach(bouton=>{
    bouton.addEventListener("click", supp_avis);
});