<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/styleConnexion.css">
    <title>Document</title>
</head>

<style>
    body{
        display: flex;
    }

    #overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Noir semi-transparent */
    z-index: 9; /* Derrière la popup */
    display: none; /* Caché par défaut */
    backdrop-filter: blur(5px); /* Effet de flou */
    }

    #popup_avis.desktop{
    position: fixed;
    width: fit-content;
    display: none;
    flex-direction: row;
    align-items: center;
    justify-content: space-around;
    background-color: #e4e4e4;
    top: 50%; /* Place le centre de la popup au milieu de l'écran verticalement */
    left: 50%;
    transform: translate(-50%, -50%); /* Ajuste pour centrer */
    border-radius: 12px;
    width: 50vw;
    height: 45vh;
    padding: 25px;
    z-index: 10;
    }
    


    #croix.desktop {
    width: 15px;
    height: 15px;
    object-fit: cover;
    cursor: pointer;
    position: relative;
    bottom: 56%;
    left: 4%;
    
    padding: 1.5px;
    background-color: var(--bleuFonce-rougeFonce);
    border: 1px solid var(--blanc);
    border-radius: 25px;
    }

    #logo.desktop{
        display: block;
    width: 300px;
    }

    .form-group.desktop {
    margin-bottom: 15px;
    }

    #group.desktop{
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 50%;
    height: 90%;
    }

    .form-group.desktop label {
    font-family: 'TripSansRegular';
    display: block;
    text-align: left;
    }

    .form-group.desktop input {
    width: 95%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    }

    #debut.desktop{
    text-align: center;
    color: #002292;
    margin: 0;
    font-size: 1.3em;
    font-family: "TripSansRegular";

    }
    #fin.desktop{
    text-align: center;
    color: #3136ED;
    margin: 0;
    font-size: 2em;
    font-family: "TripSansBold";
    }

    #connexion.desktop {
    cursor: pointer; /* CuVisiterseur en forme de main */
    border: none;
    border-radius: 8px;
    background-color: var(--turquoise-orange);
    font-size: 16px;
    width: 100%;
    margin-top: 10px;
    height: 35px;
    color: white;
    }

    .links.desktop a{
    font-family: 'TripSansRegular';
    margin-top: 5px;
    text-decoration: none;
    }

    #connexion_popup.desktop {
        padding: 10px 30px;
        cursor: pointer;
        border: none;
        border-radius: 8px;
        background-color: #3136ED;
        font-size: 16px;
        width: 100%;
        margin-top: 10px;
        color: white;
    }












    #popup_avis.mobile{
    position: fixed;
    width: fit-content;
    display: none;
    flex-direction: row;
    align-items: center;
    justify-content: space-around;
    background-color: #e4e4e4;
    top: 50%; /* Place le centre de la popup au milieu de l'écran verticalement */
    left: 50%;
    transform: translate(-50%, -50%); /* Ajuste pour centrer */
    border-radius: 12px;
    width: 50vw;
    height: 45vh;
    padding: 25px;
    z-index: 10;
    }
    


    #croix.mobile {
    width: 15px;
    height: 15px;
    object-fit: cover;
    cursor: pointer;
    position: relative;
    left: 8%;
    bottom: 56%;
    padding: 1.5px;
    background-color: var(--bleuFonce-rougeFonce);
    border: 1px solid var(--blanc);
    border-radius: 25px;
    }

    #logo.mobile{
    display: none;
    }

    .form-group.mobile {
    margin-bottom: 15px;
    }

    #group.mobile{
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
    height: 90%;
    padding-left: 18px;
    }

    .form-group.mobile label {
    font-family: 'TripSansRegular';
    display: block;
    text-align: left;
    }

    .form-group.mobile input {
    width: 95%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    }

    #debut.mobile{
    text-align: center;
    color: #002292;
    margin: 0;
    font-size: 1.3em;
    font-family: "TripSansRegular";

    }
    #fin.mobile{
    text-align: center;
    color: #3136ED;
    margin: 0;
    font-size: 2em;
    font-family: "TripSansBold";
    }

    #connexion.mobile {
    cursor: pointer; /* CuVisiterseur en forme de main */
    border: none;
    border-radius: 8px;
    background-color: var(--turquoise-orange);
    font-size: 16px;
    width: 100%;
    margin-top: 10px;
    height: 35px;
    color: white;
    }

    .links.mobile a{
    font-family: 'TripSansRegular';
    margin-top: 5px;
    text-decoration: none;
    }

    #connexion_popup.mobile {
        padding: 10px 30px;
        cursor: pointer;
        border: none;
        border-radius: 8px;
        background-color: #3136ED;
        font-size: 16px;
        width: 100%;
        margin-top: 10px;
        color: white;
    }





    #popup_avis.montrer {
    display: flex;
    }

    #overlay.montrer  {
    display: block;
    }
    

    


</style>
<body>
<div id="overlay" class="montrer"></div>
    <div id="popup_avis" class="montrer mobile">
        
        <img id="logo" class="mobile" src="../logo/logo_reduit_vert.png">
        <div id="group" class="mobile">
            <div id="text">
                <p id="debut" class="mobile">Vous souhaitez faire part de votre avis ?</p><p id="fin" class="mobile"> Connectez-vous</p>

            </div>
            <form action="connexion.php" method="post">

                <div class="form-group mobile">
                    <!--Identifiant correspond au pseudo ou codePro, pas à l'idC-->
                    <label for="identifiant">Identifiant compte</label>
                    <input id="identifiant" name="identifiant" placeholder="Identifiant" required>
                </div>

                <div class="form-group mobile">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                </div>

                <button type="submit" id="connexion_popup" class="mobile">Se connecter</button>

                <div class="links">
                    <!--<a href="#" class="mdpOublie">Mot de passe oublié ?</a>-->
                    <a href="creation_compte_formulaire.php" >S'inscrire</a>
                </div>

            </form>
        </div>
        <img id="croix" src="../images/croix.png" alt="" class="mobile ">
    </div>
</body>
</html>