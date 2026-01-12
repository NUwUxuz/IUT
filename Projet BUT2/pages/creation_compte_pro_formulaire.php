<?php ini_set('display_errors', 1);
error_reporting(E_ALL); 
error_reporting(0); if (!empty($_POST)) {


    include("../sql/connect_params.php");
    try{
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);

        if (isset($_POST["type-entreprise"])) {
            $form = $_POST["type-entreprise"];
        }

        if ($form == "privee") {
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $denomination = $_POST["denomination"];
            $raison_sociale = $_POST["denomination"];
            $email = $_POST["email"];
            $telephone = $_POST["telephone"];
            $code_postal = $_POST["codepostal"];
            $ville = $_POST["ville"];
            $temp = explode(" ",$_POST["adresse"]);     //On explode l'adresse
            $num_adresse = $temp[0];                                       //On récupère le numéro de la voie qui sera toujours la premiere valeur
            array_shift($temp);                                    //On supprime le numéro de la voie
            $adresse = implode(" ", $temp);              //On implode le reste de l'adresse (combine un array en string)
            $siren = $_POST["num_SIREN"];
            $siren = preg_replace('/\s+/', '', $siren);  // Supprime tous les espaces, tabulations, sauts de ligne, etc.
            $mdp = $_POST["password"];
            $iban = $_POST["iban"];
            $bic = $_POST["bic"];
            $codePro = time();

            if (!empty($_POST["iban"])) {
                $stmt = $dbh->prepare(
                    "INSERT INTO pact.secteur_prive(codePro, denomination_sociale, iban, bic, siren, raison_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) 
                    VALUES (:codePro, :denomination, :iban, :bic, :siren, :raison_sociale, :nom, :prenom, :email, :telephone, :mdp, :ville, :numero_voie, :voie, :code_postal, :complement)"
                );
                $stmt->bindParam(':iban', $iban, PDO::PARAM_STR);
                $stmt->bindParam(':bic', $bic, PDO::PARAM_STR);
            } else {
                $stmt = $dbh->prepare(
                    "INSERT INTO pact.secteur_prive(codePro, denomination_sociale, iban, bic, siren, raison_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) 
                    VALUES (:codePro, :denomination, '', '', :siren, :raison_sociale, :nom, :prenom, :email, :telephone, :mdp, :ville, :numero_voie, :voie, :code_postal, :complement)"
                );
            }
            $stmt->bindParam(':codePro', $codePro, PDO::PARAM_INT);
            $stmt->bindParam(':denomination', $denomination, PDO::PARAM_STR);
            $stmt->bindParam(':siren', $siren, PDO::PARAM_STR);
            $stmt->bindParam(':raison_sociale', $raison_sociale, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
            $stmt->bindParam(':mdp', $mdp, PDO::PARAM_STR);
            $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
            $stmt->bindParam(':numero_voie', $num_adresse, PDO::PARAM_INT);
            $stmt->bindParam(':voie', $adresse, PDO::PARAM_STR);
            $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_INT);
            $stmt->bindValue(':complement', '', PDO::PARAM_STR);
            

        } else if ($form == "publique") {
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $denomination = $_POST["denomination"];
            $email = $_POST["email"];
            $telephone = $_POST["telephone"];
            $code_postal = $_POST["codepostal"];
            $ville = $_POST["ville"];
            $temp = explode(" ",$_POST["adresse"]);
            $num_adresse = $temp[0];
            array_shift($temp);
            $adresse = implode(" ", $temp);
            $mdp = $_POST["password"];
            $codePro = time();

            $stmt = $dbh->prepare(
                "INSERT INTO pact.secteur_public(codePro, denomination_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) 
                VALUES (:codePro, :denomination, :nom, :prenom, :email, :telephone, :mdp, :ville, :numero_voie, :voie, :code_postal, :complement)"
            );
            
            $stmt->bindParam(':codePro', $codePro, PDO::PARAM_INT);
            $stmt->bindParam(':denomination', $denomination, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
            $stmt->bindParam(':mdp', $mdp, PDO::PARAM_STR);
            $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
            $stmt->bindParam(':numero_voie', $num_adresse, PDO::PARAM_INT);
            $stmt->bindParam(':voie', $adresse, PDO::PARAM_STR);
            $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_INT);
            $stmt->bindValue(':complement', '', PDO::PARAM_STR);
        }


        $stmt->execute();

        $idC = $dbh->lastInsertId();


        if ($stmt!=[]) {
            setcookie("user", $idC, time() + 3600, '/');

            if ($form == "privee") {
                setcookie("type_compte", 'pro_prive', time() + 3600, '/');
            }else if ($form == "publique"){
                setcookie("type_compte", 'pro_public', time() + 3600, '/');
            }
            header('Location:tableau_de_bord.php');   
        }
        $dbh = null;
    }catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }


} ?>

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

<body>
    <!-- Bouton de retour -->
    <div class="bouton-retour-page-création-pro" onclick="history.back();">
        <button class="FS">
            <img src="../icons/arrow-left-w.svg" alt="Bouton Image"> Retour
        </button>
    </div>

    <!-- Liens de navigation pour choisir le formulaire -->
    <div class="container-page-creation-compte">
        <div class="menu-links-crea-compte">
            <div class="inscription-pro TS-bold" id="link-pro">
                <h1 class="TS-bold">Inscription pro</h1>
            </div>
        </div>

        <!-- Formulaire pour l'inscription professionnelle privée -->
        <div class="form-container-page-creation-compte" id="form-pro-privee">
            <form name="privee" action="creation_compte_pro_formulaire.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="type" value="pro_prive">
                <div class="form-group-crea-compte">
                    <div class="left-column-crea-compte">
                        <label for="nom" class="FS">Nom<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="Polo" id="nom-pro" name="nom" required>
                        
                        <label for="prenom" class="FS">Prénom<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="Marco" id="prenom-pro" name="prenom" required>

                        <label for="denomination" class="FS">Dénomination Sociale<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="Les voyages de Marco Polo" id="denomination" name="denomination" required>

                        <label for="email" class="FS">E-mail<span class="required">*</span></label>
                        <input type="email" placeholder="marco.polo@gmail.com" id="email-pro" name="email" required>
                        
                        <label for="num_SIREN" class="FS">Numéro de SIREN<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="ex : 362 521 879" id="num_SIREN-pro" name="num_SIREN" required>

                        <div class="choix-entreprise-crea-compte FS">
                            <label for="type-entreprise">
                                Type d'entreprise<span class="required">*</span>
                            </label>
                            <div id="type-entreprise" class="radio-group-crea-compte FS">
                                <input type="radio" name="type-entreprise" value="publique" class="radio-button-crea-compte">
                                <span class="radio-label-crea-compte">Entreprise publique</span>

                                <input type="radio" name="type-entreprise" value="privee" class="radio-button-crea-compte" checked>
                                <span class="radio-label-crea-compte">Entreprise privée
                            </div>
                        </div>

                        <div class="element_prive1-crea-compte FS" id="elements-privee1">
                            <label for="iban" class="FS">IBAN :</label>
                            <input type="text" id="iban" name="iban" placeholder="FRXX XXXX XXXX XXXX XXXX XXXX XXX">
                        </div>
                    </div>

                    <div class="right-column-crea-compte">
                        <label for="telephone-pro" class="FS">Téléphone<span class="required">*</span></label>
                        <input type="tel" class="FS" placeholder="0637816458" id="telephone-pro" name="telephone" required>

                        <label for="adresse" class="FS">Adresse postale<span class="required">*</span></label>
                        <input type="text" class="FS" placeholder="5 rue de la gare" id="adresse-pro" name="adresse" required>

                        <div class="row-crea-compte">
                            <div class="input-wrapper-crea-compte">
                                <label for="codepostal" class="FS">Code postal<span class="required">*</span></label>
                                <input type="text" class="FS" placeholder="22300" id="codepostal-pro" name="codepostal" required>
                            </div>

                            <div class="input-wrapper-crea-compte">
                                <label for="ville" class="FS">Ville<span class="required">*</span></label>
                                <input type="text" class="FS" placeholder="Lannion" id="ville-pro" name="ville" required>
                            </div>
                        </div>
                        
                        <label for="password" class="FS">Mot de passe<span class="required">*</span></label>
                        <input type="password" class="FS" placeholder="Entrez votre mot de passe" id="password-pro" name="password" required>

                        <label for="confirm_password" class="FS">Confirmer le Mot de passe<span class="required">*</span></label>
                        <input type="password" class="FS" placeholder="Confirmer votre mot de passe" id="confirm_password-pro" name="confirm_password" required>

                        <div class="element_prive2-crea-compte FS" id="elements-privee2">
                            <label for="bic" class="FS">BIC :</label>
                            <input type="text" id="bic" name="bic" placeholder="ABCDFRPPXXX">
                        </div>

                        
                    </div>
                </div>
                <div class="form-info-crea-compte">
                    <p class="FS">Le BIC et l'IBAN ne sont pas obligatoire pour poursuivre. Vous pourrez les ajouter ou les modifier ultérieurement dans votre espace personnel.</p>
                </div>
                <div class="checkbox-crea-compte FS">
                    <input type="checkbox" id="terms" name="terms" required>
                    <span class="FS-bold">
                        Accepter les 
                        <a href="conditions_d_utilisation.php" target="_blank">conditions générales d'utilisation de PACT</a>
                        <span class="required">*</span>
                    </span>
                </div>


                <button type="submit" class="bouton-envoi-formulaire-crea-compte-page-pro TS-bold">Créer un compte</button>
            </form>
            
            <div class="redirection-links-crea-compte FS">
                <a href="connexion.php" class="Connexion">Déjà inscrit(e) ? Connectez-vous.</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialisation : afficher le formulaire privé par défaut
            showForm('form-pro-privee');

            
        });

        function showForm(formId) {
            // Masquer tous les formulaires
            const forms = document.querySelectorAll('.form-container-page-creation-compte');
            forms.forEach(form => form.classList.remove('active'));

            // Afficher le formulaire correspondant
            const activeForm = document.getElementById(formId);
            if (activeForm) {
                activeForm.classList.add('active');
            }
        }

        const radios = document.querySelectorAll('input[name="type-entreprise"]');
        const elementsPrivee1 = document.getElementById('elements-privee1');
        const elementsPrivee2 = document.getElementById('elements-privee2');
        const formInfo = document.querySelector('.form-info');

        // Fonction pour gérer l'affichage et la désactivation
        function toggleElements() {
            const selectedValue = document.querySelector('input[name="type-entreprise"]:checked').value;

            if (selectedValue === 'publique') {
                elementsPrivee1.style.display = 'none';
                elementsPrivee2.style.display = 'none';
                formInfo.style.display = 'none';

                // Désactiver les champs
                elementsPrivee1.querySelectorAll('input').forEach(el => el.disabled = true);
                elementsPrivee2.querySelectorAll('input').forEach(el => el.disabled = true);
            } else {
                elementsPrivee1.style.display = 'block';
                elementsPrivee2.style.display = 'block';
                formInfo.style.display = 'block';

                // Activer les champs
                elementsPrivee1.querySelectorAll('input').forEach(el => el.disabled = false);
                elementsPrivee2.querySelectorAll('input').forEach(el => el.disabled = false);
            }
        }

        // Ajout d'un écouteur d'événements sur chaque bouton radio
        radios.forEach(radio => {
            radio.addEventListener('change', toggleElements);
        });

        // Appeler la fonction au chargement pour initialiser l'état
        toggleElements();


        //Vérification des données avec regex
        function validateFormMembre() {
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

        // Afficher le formulaire
        showForm('form-pro-privee');
    </script>
</body>
</html>
