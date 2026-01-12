<?php ini_set('display_errors', 1);
error_reporting(E_ALL); 
error_reporting(0); if (!empty($_POST)) { 
    
    include("../sql/connect_params.php");
    try{ 
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);

        if($_POST["type"]=="membre"){

            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $pseudo = $_POST["pseudo"];
            $email = $_POST["email"];
            $telephone = $_POST["telephone"];
            $code_postal = $_POST["codepostal"];
            $ville = $_POST["ville"];
            $temp = explode(" ",$_POST["adresse"]);     //On explode l'adresse
            $num_adresse = $temp[0];                    //On récupère le numéro de la voie qui sera toujours la premier valeur
            array_shift($temp);                         //On supprime le numéro de la voie
            $adresse = implode(" ", $temp);             //On implode le reste de l'adresse (combine un array en string)
            $mdp = $_POST["password"];

            $stmt_check = $dbh->prepare("SELECT COUNT(*) FROM pact.membre WHERE pseudo = :pseudo");
            $stmt_check->bindParam(':pseudo', $pseudo);
            $stmt_check->execute();
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                echo "Le pseudonyme choisit existe déjà, veuillez en choisir un autre";
            }

            else {
                $stmt = $dbh->prepare(
                    "INSERT INTO pact.membre(pseudo, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) 
                    VALUES (:pseudo, :nom, :prenom, :email, :telephone, :mdp, :ville, :numero_voie, :voie, :code_postal, :complement)"
                );
                
                $stmt->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
                $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
                $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
                $stmt->bindParam(':mdp', $mdp, PDO::PARAM_STR);
                $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmt->bindParam(':numero_voie', $num_adresse, PDO::PARAM_INT);
                $stmt->bindParam(':voie', $adresse, PDO::PARAM_STR);
                $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_INT);
                $stmt->bindParam(':complement', $complement, PDO::PARAM_STR);
            }

        }
        $stmt->execute();

        $idC = $dbh->lastInsertId();

        if ($stmt!=[]) {
            setcookie("user", $idC, time() + 3600, '/');
            setcookie("type_compte", 'membre', time() + 3600, '/');
            header('Location:accueil.php');
        }


    }catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $dbh = null;
    ?>
    <?php } ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <?php 
    if(isset($_COOKIE['type_compte'])){
        if ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") { ?>
            <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/rouge.ico"> <?php   
        }  else { ?>
            <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/bleu.ico"> <?php
        }
    } else { ?>
        <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/bleu.ico"> <?php
    }?>
</head>


<div class="phone-element">
        <?php include_once 'header.php' ?>
</div>

<body>
    <!-- Bouton de retour -->
    <div class="bouton-retour-page-création-membre">
        <button class="FS" onclick="history.back();">
            <img src="../icons/arrow-left-w.svg" alt="Bouton Image"> Retour
        </button>
    </div>

    <!-- Liens de navigation pour choisir le formulaire -->
    <div class="container-page-creation-compte">
        <div class="menu-links-crea-compte">
            <div class="inscription-membre TS-bold" id="link-membre">
                <h1 class="TS-bold">Inscription</h1>
            </div>
        </div>


        <!-- Formulaire pour l'inscription membre -->
        <div class="form-container-page-creation-compte" id="form-membre">
            <form name="membre" action="creation_compte_formulaire.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="type" value="membre">
                <div class="form-group-crea-compte">
                    <div class="left-column-crea-compte">
                        <label for="nom" class="FS">Nom<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="Colomb" id="nom-membre" name="nom" required>
                        <label class="erreur" id="erreurnom"></label>
                        
                        <label for="prenom" class="FS">Prénom<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="Christophe" id="prenom-membre" name="prenom" required>
                        <label class="erreur" id="erreurprenom"></label>
                        
                        <label for="pseudo" class="FS">Pseudonyme<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="Christophorus" id="pseudo-membre" name="pseudo" required>
                        <label class="erreur" id="erreurpseudo"></label>

                        <label for="adresse" class="FS">Adresse postale<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="5 rue de  la gare" id="adresse-membre" name="adresse" required>
                        <label class="erreur" id="erreuradresse"></label>

                        <div class="row-crea-compte">
                            <div class="input-wrapper-crea-compte">
                                <label for="codepostal" class="FS">Code postal<span class="required">*</span></label>
                                <input type="text" class="FS" placeholder="22300" id="codepostal-membre" name="codepostal" required>
                                <label class="erreur" id="erreurcodep"></label>
                            </div>

                            <div class="input-wrapper-crea-compte">
                                <label for="ville" class="FS">Ville<span class="required">*</span></label>
                                <input type="text" class="FS" placeholder="Lannion" id="ville-membre" name="ville" required>
                                <label class="erreur" id="erreurville"></label>
                            </div>
                        </div>
                    </div>
                    <div class="right-column-crea-compte">
                        <label for="email" class="FS">E-mail<span class="required">*</span></label>
                        <input type="email" class="FS" placeholder="christophe.colomb@gmail.com" id="email-membre" name="email" required>
                        <label class="erreur" id="erreuremail"></label>

                        <label for="telephone" class="FS">Téléphone<span class="required">*</span></label>
                        <input type="tel" class="FS" placeholder="0641379428" id="telephone-membre" name="telephone" required>
                        <label class="erreur" id="erreurtelephone"></label>

                        <label for="password" class="FS">Mot de passe<span class="required">*</span></label>
                        <input type="password" class="FS" placeholder="Entrez votre mot de passe" id="password-membre" name="password" required>
                        <label class="erreur" id="erreurmdp"></label>

                        <label for="confirm_password" class="FS">Confirmer le Mot de passe<span class="required">*</span></label>
                        <input type="password" class="FS" placeholder="Confirmer votre mot de passe" id="confirm_password-membre" name="confirm_password" required>
                        <label class="erreur" id="erreurconmdp"></label>
                        
                        <div class="checkbox-crea-compte">
                            <input type="checkbox" id="terms" name="terms" required>
                            <span class="FS-bold">
                                Accepter les 
                                <a href="conditions_d_utilisation.php" target="_blank">conditions générales d'utilisation de PACT</a>
                                <span class="required">*</span>
                            </span>
                        </div>



                    </div>
                </div>
                    <button type="submit" class="bouton-envoi-formulaire-crea-compte-page-membre TS-bold">Créer un compte</button>
            </form>
            <div class="redirection-links-crea-compte">
                <a href="connexion.php" class="Connexion">Déjà inscrit(e) ? Connectez-vous.</a>
                <a href="creation_compte_pro_formulaire.php" class="Redirection_pro">Vous êtes un(e) professionnel(le) ? Cliquez ici.</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('link-membre').addEventListener('click', function () {
            showForm('form-membre');
        });

        function showForm(formId) {
            // Masquer tous les formulaires
            const forms = document.querySelectorAll('.form-container-page-creation-compte');
            forms.forEach(form => form.classList.remove('active'));

            // Afficher le formulaire correspondant
            const activeForm = document.getElementById(formId);
            activeForm.classList.add('active');
        }

        //Vérification des données avec regex
        function validateFormMembre() {
            let check = true;
            let checkNom = true;
            let checkPrenom = true;
            let checkPseudo = true;
            let checkCodeP = true;
            let checkVille = true;
            let checkAdresse = true;
            let checkEmail = true;
            let checkTelephone = true;
            let checkMDP = true;
            let checkConMDP = true;

            if (!/^[a-z]{2,20}$/i.test(document.forms["membre"]["nom"].value)) { 
                document.getElementById('erreurnom').innerHTML="Minimum 2 caractère et max 20 caractère";
                checkNom = false;
            }
            else {
                document.getElementById('erreurnom').innerHTML="";
                checkNom = true;
            }
            if (!/^[a-z]{2,20}$/i.test(document.forms["membre"]["prenom"].value)) {
                document.getElementById('erreurprenom').innerHTML="Minimum 2 caractère et max 20 caractère";
                checkPrenom = false;
            }
            else {
                document.getElementById('erreurprenom').innerHTML="";
                checkPrenom = true;
            }
            if (!/^[a-z]{2,20}$/i.test(document.forms["membre"]["pseudo"].value)) { 
                document.getElementById('erreurpseudo').innerHTML="Minimum 2 caractère et max 20 caractère et pas de caractères spéciaux";
                checkPseudo = false;
            }
            else {
                document.getElementById('erreurpseudo').innerHTML="";
                checkPseudo = true;
            }
            if (!/^[0-9]{5}$/i.test(document.forms["membre"]["codepostal"].value)) { 
                document.getElementById('erreurcodep').innerHTML="5 chiffre requis";
                checkCodeP = false;
            }
            else {
                document.getElementById('erreurcodep').innerHTML="";
                checkCodeP = true;
            }
            if (!/^[a-z]{2,20}$/i.test(document.forms["membre"]["ville"].value)) { 
                document.getElementById('erreurville').innerHTML="Minimum 2 caractère et max 20 caractère";
                checkVille = false;
            }
            else {
                document.getElementById('erreurville').innerHTML="";
                checkVille = true;
            }
            if (!/^.{2,30}$/i.test(document.forms["membre"]["adresse"].value)) { 
                document.getElementById('erreuradresse').innerHTML="Minimum 2 caractère et max 30 caractère";
                checkAdresse = false;
            }
            else {
                document.getElementById('erreuradresse').innerHTML="";
                checkAdresse = true;
            }
            if (!/^.{5,50}$/i.test(document.forms["membre"]["email"].value)) { 
                document.getElementById('erreuremail').innerHTML="Minimum 5 caractère et max 50 caractère";
                checkEmail = false;
            }
            else {
                document.getElementById('erreuremail').innerHTML="";
                checkEmail = true;
            }
            if (!/^0[0-9]{9}$/i.test(document.forms["membre"]["telephone"].value)) { 
                document.getElementById('erreurtelephone').innerHTML="Minimum 10 chiffre requis et commence par un 0";
                checkTelephone = false;
            }
            else {
                document.getElementById('erreurtelephone').innerHTML="";
                checkTelephone = true;
            }
            if (!/^.{5,25}$/i.test(document.forms["membre"]["password"].value)) { 
                document.getElementById('erreurmdp').innerHTML="Minimum 5 caractère et max 25 caractère";
                checkMDP = false;
            }
            else {
                document.getElementById('erreurmdp').innerHTML="";
                checkMDP = true;
            }
            if (!/^.{5,25}$/i.test(document.forms["membre"]["confirm_password"].value)) { 
                document.getElementById('erreurconmdp').innerHTML="Minimum 5 caractère et max 25 caractère";
                checkConMDP = false;
            }
            else {
                document.getElementById('erreurconmdp').innerHTML="";
                checkConMDP = true;
            }
            if (checkNom == true && checkPrenom == true && checkPseudo == true && checkCodeP == true && checkVille == true && checkAdresse == true && checkEmail == true && checkTelephone == true && checkMDP == true && checkConMDP == true) {
                return true;
            }
            else {
                return false;
            }
        }

        //Empêche l'envoie des données si le form possède des erreurs
        document.forms["membre"].addEventListener('submit', function (event) {
            if (!validateFormMembre()) {
                event.preventDefault();
            }
        });

        // Afficher par défaut le premier formulaire
        showForm('form-membre');
    </script>

    <?php include_once'footerNav.php' ?>
    
</body>
</html>
