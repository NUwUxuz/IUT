<?php
include('../sql/connect_params.php');
use OTPHP\TOTP;

if (isset($_REQUEST["id"]) && $_REQUEST["id"]!=null) {
    require (dirname(__DIR__). '/vendor/autoload.php');
    $id = $_REQUEST["id"];
    try{
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);

        $stmt = $dbh->prepare(
            "SELECT idC FROM pact._membre WHERE pseudo = :pseudo;"
        );
        $stmt->bindParam(':pseudo', $id, PDO::PARAM_STR);
        $stmt->execute();
        $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $idC = isset($results[0]['idC']) ? $results[0]['idC'] : null;
        if ($idC==null && is_int($id)) {
            $stmt = $dbh->prepare(
                "SELECT idC FROM pact._professionnel WHERE codePro = :codePro;"
            );
            $stmt->bindParam(':codePro', $id, PDO::PARAM_INT);

            $stmt->execute();
            $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $idC = isset($results[0]['idC']) ? $results[0]['idC'] : null;
        }
        if ($idC!=null) {
            //get secret
            $stmt = $dbh->prepare("SELECT secretOtp FROM pact._compte WHERE idC = :idc");
            $stmt->bindParam(':idc', $idC);
            $stmt->execute();
            $secret = $stmt->fetch(PDO::FETCH_ASSOC)["secretotp"];
        }
        //echo $secret;
        if (!isset($_REQUEST["otp"])) {
            // pour la vérification du secret
            echo $secret !== null ? "true" : "false";
        }else{
            // pour vérification du code envoyé
            $otp = TOTP::createFromSecret($secret);
            echo $otp->now() === $_REQUEST["otp"] ? "true" : "false";
        }
        
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    

}else{
if ($_POST!=[]) {
    $identifiant = trim($_POST['identifiant']);
    $message_erreur = '';
    $trouve = false;
    try{
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
            $user, $pass);

        //verifie si membre

        $stmt = $dbh->prepare(
            "SELECT idC, pseudo, mdp FROM pact.membre WHERE pseudo = :pseudo;"
        );
        $stmt->bindParam(':pseudo', $identifiant, PDO::PARAM_STR);
        
            
        $stmt->execute();
        $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        if ($stmt!=[]) {
            if ($stmt[0]['mdp'] == $_POST['password']) {
                $idC = strval($stmt[0]['idc']);
                setcookie("user", $idC, time() + 3600, '/');
                setcookie("type_compte", 'membre', time() + 3600, '/');
                header('Location:accueil.php');
                $trouve = true;
            }
        }
        
        //verifie si professionnel

        $identifiant = (int)$identifiant;

        $stmt = $dbh->prepare(
            "SELECT idC, codePro, mdp FROM pact.professionnel WHERE codePro = :codePro;"
        );
        $stmt->bindParam(':codePro', $identifiant, PDO::PARAM_INT);

        $stmt->execute();
        $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt!=[]) {
            if ($stmt[0]['mdp'] == $_POST['password']) {
                $idC = strval($stmt[0]['idc']);
                setcookie("user", $idC, time() + 3600, '/');

                $stmt = $dbh->prepare(
                    "select * from pact.professionnel where idC in (select idC from pact.secteur_prive where idC = $idC);"
                );
        
                $stmt->execute();
                $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($stmt!=[]) {
                    setcookie("type_compte", 'pro_prive', time() + 3600, '/');
                }else{
                    setcookie("type_compte", 'pro_public', time() + 3600, '/');
                }
                header('Location:tableau_de_bord.php');   
                $trouve = true;
            }
        }

        if (!$trouve){
            $message_erreur = "Nom d'utilisateur ou mot de passe incorrect, veuillez réessayer";
        }

    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link rel="stylesheet" type="text/css" href="../css/styleGeneral.css">
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

<body class="login-page FO">
    <div class="container-connexion">
        <div class="menu-links-connexion">
            <div class="inscription-membre TS-bold" id="link-membre">
                <h1 class="titre-connexion TS-bold">Connexion</h1>
            </div>
        </div>
        <div class="form-container-connexion">
            <button class="back-button-connexion" onclick="history.back();">
                <img src="../icons/arrow-left-w.svg" alt="">Retour
            </button>

            <div class="logo-container-connexion">
                <img src="../logo/logo_vert.png" alt="Logo Vert" class="logo-connexion">
            </div>
            <?php if (!empty($message_erreur)) { ?>
            <p class="message-erreur-connexion"><?php echo $message_erreur; ?></p>
            <?php } ?>

            

            <form action="connexion.php" method="post" class="login-form">
                <div class="form-group-connexion">
                    <!--Identifiant correspond au pseudo ou codePro, pas à l'idC-->
                    <label for="identifiant">Identifiant compte</label>
                    <input id="identifiant" name="identifiant" placeholder="Identifiant" required>
                </div>

                <div class="form-group-connexion">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                </div>

                <button type="button" class="submit-button-connexion" id="submit-button">Se connecter</button>

                <div class="links-connexion">
                    <!--<a href="#" class="mdpOublie-connexion">Mot de passe oublié ?</a>-->
                    <a href="creation_compte_formulaire.php" class="inscription-connexion">S'inscrire</a>
                </div>
            </form>
        </div>
    </div>
            <?php include_once 'footerNav.php' ?>

<script src="../js/connexion.js"></script>
</body>
</html>
<?php }?>