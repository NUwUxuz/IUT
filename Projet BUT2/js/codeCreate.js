
const copyButton = document.getElementById("copy_secret");
const copyDiv = document.getElementById("copyDiv");

const remakeQR = document.getElementById("recreate_code");
const qrCode = document.getElementById("qr_code");

function copyToClipboard(item) {
    navigator.clipboard.writeText(item);
}

copyButton.addEventListener("click", function (e){
    var text = e.target.value;
    console.log(text);
    copyToClipboard(text);
})

function showCopyPopup() {
    var popup = document.getElementById("copyPopup");
    popup.classList.toggle("showSmallPopup");
}

copyButton.addEventListener("click", async () =>{
    showCopyPopup();
    setTimeout(showCopyPopup, 2000);
})

function post(path, params, method='post') {
    //fonction qui effectue un post sur une page
    const form = document.createElement('form');
    form.method = method;
    form.action = path;
  
    for (const key in params) {
      if (params.hasOwnProperty(key)) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
  
        form.appendChild(hiddenField);
      }
    }
  
    document.body.appendChild(form);
    form.submit();
  }
  

remakeQR.addEventListener("click", async () =>{
    remakeQR.childNodes[0].classList.toggle("imageRotation");

    post('', {reset: 'true'});

})