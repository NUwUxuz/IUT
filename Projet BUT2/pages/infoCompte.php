<?php ini_set('display_errors', 1);
error_reporting(E_ALL); 
error_reporting(0); 
include("../sql/connect_params.php");

$Cmembre = False;
$Cpro_public = False;
$Cpro_prive = False;

$dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
try{

    if ($_POST['delete'] == "oui") {
        $stmt=$dbh->prepare("DELETE FROM pact.membre WHERE idC = :id_user");
        $stmt->bindParam(':id_user', $_COOKIE['user']);
        $stmt->execute();
        ?>
        <script>
            document.cookie = "user=; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
            document.cookie = "type_compte=; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
            location.assign("accueil.php");
        </script>
        <?php
    }

    else {
        if($_COOKIE['type_compte'] == "membre") {

            $stmt = $dbh->prepare("SELECT * FROM pact.membre WHERE idC = :idc");
            $stmt->bindParam(':idc', $_COOKIE['user']);
            $stmt->execute();
            
            $compte = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $dbh->prepare("SELECT image_compte FROM pact._compte WHERE idC = :idc");

            $stmt->bindParam(':idc', $_COOKIE['user']);

            $stmt->execute();

            $image_profil = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $dbh->prepare("SELECT src_image FROM pact._image WHERE idImage = :idimage");

            $stmt->bindParam(':idimage', $image_profil['image_compte']);

            $stmt->execute();

            $image_profil = $stmt->fetch(PDO::FETCH_ASSOC);

            $Cmembre = True;
        }else if($_COOKIE['type_compte'] == "pro_public"){
            $stmt = $dbh->prepare("SELECT * FROM pact.secteur_public WHERE idC = :idc");
            $stmt->bindParam(':idc', $_COOKIE['user']);
            $stmt->execute();

            $compte = $stmt->fetch(PDO::FETCH_ASSOC);
            $Cpro_public = True;
        }else{
            $stmt = $dbh->prepare("SELECT * FROM pact.secteur_prive WHERE idC = :idc");
            $stmt->bindParam(':idc', $_COOKIE['user']);
            $stmt->execute();

            $compte = $stmt->fetch(PDO::FETCH_ASSOC);
            $Cpro_prive = True;
        }
    }

    $stmt = $dbh->prepare("SELECT image_compte FROM pact._compte WHERE idC = :idc");
    $stmt->bindParam(':idc', $_COOKIE['user']);
    $stmt->execute();

    $image_profil = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $dbh->prepare("SELECT src_image FROM pact._image WHERE idImage = :idimage");
    $stmt->bindParam(':idimage', $image_profil['image_compte']);
    $stmt->execute();

    $image_profil = $stmt->fetch(PDO::FETCH_ASSOC);

}catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
    $dbh = null;
    ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres de Compte</title>
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <link rel="stylesheet" href="../css/info-compte.css">
    <link rel="stylesheet" href="../css/samStyles.css">
    <?php 
    print_r($_COOKIE['type_compte']);
    print_r("test");
    if(isset($_COOKIE['type_compte'])){
        if ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") { ?>
            <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/rouge.ico"> <?php   
        }  else { ?>
            <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/bleu.ico"> <?php
        }
    } else { ?>
        <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/bleu.ico"> <?php
    }?>
    
    <script src="../js/infoCompte.js" async></script>

    <script>
        function deconnexion() {
            document.cookie = "user=; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
            document.cookie = "type_compte=; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
            location.assign("accueil.php");  
        }
    </script>
    <style>
        <?php if($Cmembre){ ?>
            .bouton-modif-avis{
                background-color: #3136ED;
            }

            .bouton-modif-avis:hover {
                background-color: #5b60f3; /* Couleur plus claire ou différente */
            }
        <?php }else{ ?>
            .bouton-modif-avis{
                background-color: #ED3631;
            }

            .bouton-modif-avis:hover {
                background-color: #A2021F; /* Couleur plus claire ou différente */
            }
        <?php } ?>
    </style>
</head>

<div class="desktop-element">
    <?php
    include_once'nav.php';
    ?>
</div>

<body class = "<?php if($Cmembre){echo "FO";}else{echo "BO";}?>">

    <!-- Liens de navigation pour choisir le formulaire -->
    <div class="container-page-info-compte">
            <div class="compte-titre-photo-profil TS-bold" id="link-membre" >
                <h1 class="TS-bold">Paramètre de Compte</h1>
                <img id="photo-profil" class="compte-photo-profil" src="../images/<?php echo $image_profil['src_image'] ?>" alt="icone-membre" class="icone-membre" width="160px" height="160px" >
            </div>
        <div class="compte-container-main-page" id="form-membre">
                <div class="compte-details-section">
                    <div class="left-column-info-compte">
                        <label for="nom" class="FS">Nom</label>
                        <input type="text" class="FS" value="<?php echo $compte['nom']?>" id="nom-membre" name="nom" readonly>
                        
                        
                        <label for="prenom" class="FS">Prénom</label>
                        <input type="text" class="FS" value="<?php echo $compte['prenom'] ?>" id="prenom-membre" name="prenom" readonly>
                        
                        <?php if($Cmembre){ ?>
                            <label for="pseudo" class="FS">Pseudonyme</label>
                            <input type="text" class="FS" value="<?php echo $compte['pseudo'] ?>" id="pseudo-membre" name="pseudo" readonly>
                        <?php }else if($Cpro_prive){ ?>
                            <label for="codepro" class="BO">Code Professionnel</label>
                            <input type="text" class="BO" value="<?php echo $compte['codepro'] ?>" id="codepro" name="codepro" readonly>

                            <label for="bic" class="BO">Bic</label>
                            <input type="text" class="BO" value="<?php echo $compte['bic'] ?>" id="bic" name="bic" readonly>
                        <?php } ?>

                        <label for="adresse" class="FS">Adresse postale</label>
                        <input type="text" class="FS" value="<?php echo $compte['numero_voie'] . ' ' . $compte['voie']?>" id="adresse-membre" name="adresse" readonly>

                        <div class="row-info-compte">
                            <div class="input-wrapper-info-compte">
                                <label for="codepostal" class="FS">Code postal</label>
                                <input type="text" class="FS" value="<?php echo $compte['code_postal'] ?>" id="codepostal-membre" name="codepostal" readonly>
                                
                            </div>

                            <div class="input-wrapper-info-compte">
                                <label for="ville" class="FS">Ville</label>
                                <input type="text" class="FS" value="<?php echo $compte['ville'] ?>" id="ville-membre" name="ville" readonly>
                                
                            </div>
                        </div>
                    </div>
                  <div class="right-column-info-compte">
                        <label for="email" class="FS">E-mail</label>
                        <input type="email" class="FS" value="<?php echo $compte['email'] ?>" id="email-membre" name="email" readonly>

                        <label for="telephone" class="FS">Téléphone</label>
                        <input type="tel" class="FS" value="<?php echo $compte['telephone'] ?>" id="telephone-membre" name="telephone" readonly>

                        <?php if(!$Cmembre){?>
                            <label for="denomination" class="BO">Dénomination</label>
                            <input type="text" class="BO" value="<?php echo $compte['denomination_sociale'] ?>" id="denomination" name="denomination" readonly>
                            <?php if($Cpro_prive){ ?>
                                <label for="iban" class="BO">Iban</label>
                                <input type="text" class="BO" value="<?php echo $compte['iban'] ?>" id="iban" name="iban" readonly>

                                <label for="siren" class="BO">Siren</label>
                                <input type="text" class="BO" value="<?php echo $compte['siren'] ?>" id="siren" name="siren" readonly>
                            <?php } ?>
                        <?php } ?>

                        <button class="boutonA2F TS-bold" id="A2F" onclick="openPopup()" >Activer l'authentification à deux facteurs</button>
                        <div class="overlay" id="overlay" onclick="closePopup()"></div>
                        <div class="popup" id="popup">
                            <h3 class="TS-bold" id="titre-popup">Confirmer le mot de passe</h3>
 
                            <label name="password" class="FS">Mot de passe</label>
                            <input type="password" class="FS" id="password-popup" name="password" >
 
                            <label name="password2" class="FS">Confirmer le mot de passe</label>
                            <input type="password" class="FS" id="password2-popup" name="password2" >
                            <button class="confirm-btn" onclick="checkPassword()">Confirmer</button>
                            <button class="close-btn" onclick="closePopup()">Fermer</button>
                         </div>
                    </div>
                </div>
                <div class="info-compte-buttons">
                    <a class="bouton-modif-avis TS-bold" href="modifInfo.php" >Modifier les Informations</a>
                    <?php
                    if($Cmembre){ ?>
                        <a class="bouton-modif-avis TS-bold" href="mes-avis.php" >Voir mes avis</a>
                    <?php } ?>
                    <a href="#" class="info-compte-bouton-deconnecter" data-icon="deconnection" onclick="deconnexion()">Se déconnecter</a>

                    <button id="delete-btn-compte" class="bouton-modif-avis TS-bold">
                        Supprimer le compte
                    </button>
                </div>
                <div id="overlay">
                <div id="popup_suppr_compte">
                    <div id="group">
                        <div id="text">
                            <p>Voulez-vous vraiment supprimer votre compte ?</p>
                        </div>
                        <form method="POST" action="infoCompte.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="delete" value="oui">
                            <button type="submit" class="confirm-delete-btn-compte">Supprimer le compte</button>
                        </form>
                    </div>
                    <img id="croix_small" src="../images/croix.png" alt="" class="close-popup">
                </div>
            </div>
        </div>
    </div>
</body>

<?php include_once'footerNav.php' ?>
</html>



