
        <link rel="stylesheet" href="../css/styleGeneral.css">

    <?php

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        include('../sql/connect_params.php');

        if(isset($_COOKIE['user'])){
            try{
                $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
                    $user, $pass);   
                
                if($_COOKIE['type_compte'] === 'pro_prive' || $_COOKIE['type_compte'] === 'pro_public'){
                    $stmt = $dbh->prepare(
                        "select nom, prenom, email from pact.professionnel where idc = :user;"
                    );
 
                } else {
                    $stmt = $dbh->prepare(
                        "select nom, prenom, email from pact.membre where idc = :user;"
                    );
                }

                $stmt->execute([
                    'user' => $_COOKIE['user']
                ]);
                // Récupérer la première ligne du résultat
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                
                $stmt = $dbh->prepare("SELECT image_compte FROM pact._compte WHERE idC = :idc");
                $stmt->bindValue(':idc', $_COOKIE['user'], PDO::PARAM_INT);
                $stmt->execute();

                $image_profil = $stmt->fetch(PDO::FETCH_ASSOC);


                $stmt = $dbh->prepare("SELECT src_image FROM pact._image WHERE idImage = :idimage");
                $stmt->bindParam(':idimage', $image_profil['image_compte']);
                $stmt->execute();

                $image_profil = $stmt->fetch(PDO::FETCH_ASSOC);


                if ($row) {
                    // Récupérer les valeurs des trois colonnes
                    $nom = $row['nom'];
                    $prenom = $row['prenom'];
                    $email = $row['email'];
                }
    
    
            } catch (PDOException $e) {
                echo "<pre>";
                print "Erreur !: " . $e->getMessage() . "<br/>";
                echo "</pre>";
                die();
            }


            // Récupération des avis et des réponse
            try{
                $stmt = $dbh->prepare("SELECT * FROM pact.experience");
                $stmt->execute();
                $avis_nav = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                print "Erreur !: " . $e->getMessage() . "<br/>";
                die();
            }

            try{
                $stmt = $dbh->prepare("SELECT * FROM pact._reponse");
                $stmt->execute();
                $reponse_nav = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                print "Erreur !: " . $e->getMessage() . "<br/>";
                die();
            }



            // Vérification de si il y a des avis non répondu
            $nbr_avis_nav = 0;
            foreach($avis_nav as $a_nav) {
                $check = true;
                foreach($reponse_nav as $rep_nav) {
                    if($a_nav['ida'] == $rep_nav['ida']) {
                        $check = false;
                    }
                }
                if ($check) {

                    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);

                    try{

                        $stmt=$dbh->prepare("SELECT * FROM pact.offre WHERE idO = :id_offre");
                        $stmt->bindParam(':id_offre', $a_nav['ido']);
                        $stmt->execute();
        
                        $offre_nav = $stmt->fetch(PDO::FETCH_ASSOC);
                
                    } catch (PDOException $e) {
                        print "Erreur !: " . $e->getMessage() . "<br/>";
                    die();
                    }


                    if ($offre_nav['idc'] == $_COOKIE['user']) {
                        $nbr_avis_nav++;
                    }
                }
            }
        }
        





        if (isset($_COOKIE['type_compte']) && ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive")) { ?>
            <div class="TS BO" id="sidebar"> <?php
        } else {?>
            <div class="TS FO" id="sidebar"> <?php
            
        } ?>
                    <?php
                        $estMembre = true;
                        if (isset($_COOKIE['type_compte'])) {
                            if (($_COOKIE['type_compte'] != "membre")){
                                $estMembre = false;
                            }
                        }
                    ?>
                    <a href="accueil.php" id="pact-item">
                        <img  class="image image-default">
                        <img  class="image image-hover">      
                    </a> 
                    <div id="items">
                        <div id="top-items">
                            <a href="accueil.php" class="nav-item" data-icon="accueil">
                                <img src="../icons/icons-gris/icon-accueil-gris.svg" alt="Logo accueil" class="logo-nav">
                                <p>Accueil</p>
                            </a>

                            <a href="tableau_de_bord.php" class="nav-item" data-icon="tableau-de-bord">
                                <img src="../icons/icons-gris/icon-tableau-de-bord-gris.svg" alt="Logo tableau de bord" class="logo-nav">
                                <p>Mes offres</p>
                            </a>
                                                    
                            <a href="historique.php" class="nav-item" data-icon="historique">
                                <img src="../icons/icons-gris/icon-historique-gris.svg" alt="Logo historique" class="logo-nav">
                                <p>Historique</p>
                            </a>

                                <a href="facturation.php" class="nav-item" data-icon="facturation">
                                    <img src="../icons/icons-gris/icon-facturation-gris.svg" alt="Logo Facturation" class="logo-nav">
                                    <p>Facturation</p>
                                </a>

                                <a href="creation_offre.php" class="nav-item" data-icon="creation">
                                    <img src="../icons/icons-gris/icon-creation-gris.svg" alt="Logo création offre" class="logo-nav">
                                    <p>Créer une offre</p>
                                </a>
                            </a>

                        </div>
                        <div id="bottom-items">
                            <?php if (isset($_COOKIE['user']) && $_COOKIE['type_compte'] != 'membre') {
                                if ($nbr_avis_nav > 0) { ?>
                                    <a href="avis_non_rep.php" class="nav-item" data-icon="notification-avec-notif">
                                        <img src="../icons/icons-gris/icon-notification-avec-notif-gris.svg" alt="Logo notification" class="logo-nav">
                                        <p>Notifications</p>
                                    </a>
                                <?php } else { ?>
                                    <a href="avis_non_rep.php" class="nav-item" data-icon="notification-sans-notif">
                                        <img src="../icons/icons-gris/icon-notification-avec-notif-gris.svg" alt="Logo notification" class="logo-nav">
                                        <p>Notifications</p>
                                    </a>
                                <?php } 
                            } ?>

                                <?php if (isset($_COOKIE['type_compte'])) { ?>
                                <a href="infoCompte.php" class="nav-item" data-icon="parametre">
                                    <img src="../icons/icons-gris/icon-parametre-gris.svg" alt="Logo paramètres" class="logo-nav">
                                    <p>Paramètres</p>
                                </a>
                                <?php } ?>
                                <?php if (isset($_COOKIE['type_compte'])) { ?>
                                    
                                    <script>
                                        function deconnexion() {
                                            let confirmation = confirm("Voulez-vous vous déconnecter ?");
                                            if (confirmation) {
                                                document.cookie = "user=; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
                                                document.cookie = "type_compte=; expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
                                                location.assign("accueil.php");
                                            }
                                        }
                                    </script>
                                    <a href="#" class="nav-item" data-icon="deconnection" onclick="deconnexion()">

                                        <img src="../icons/icons-gris/icon-deconnection-gris.svg" alt="Logo déconnecter" class="logo-nav">
                                        <p>Se déconnecter</p>
                                    </a>
                                <?php } else { ?>
                                    <a href="creation_compte_formulaire.php" class="nav-item inscription_icon" data-icon="inscription">
                                        <img src="../icons/icons-gris/icon-inscription-gris.svg" alt="Logo Inscription" class="logo-nav">
                                        <p>S'inscrire</p>
                                    </a>
                                <?php } ?>


                            <?php if (isset($_COOKIE['type_compte'])) {?>
                                
                                <a href="infoCompte.php" class="nav-item">
                                    <img src="../images/<?php echo $image_profil['src_image']?>" alt="Logo déconnecter" class="logo-nav connecte" id="img-profile">
                                    <div id="infoProfile">
                                        <p id="prenom" class="TS"><?php echo $nom . ' ' . $prenom ?></p>
                                        <p id="email" class="TS"><?php echo $email ?></p>
                                    </div>
                                </a>
                            <?php } else { ?>
                                <a href="connexion.php" class="nav-item">
                                    <img src="../icons/utilisateur.png" alt="Logo connexion" class="logo-nav" id="img-profile">
                                    <div id="infoProfile">
                                        <p id="connexion" class="TS">Se connecter</p>
                                    </div>
                                </a>
                            <?php } ?>

                        </div>
                    </div>
                <div class="sphere">
                    <img src="../icons/arrow.svg">
                </div>
            </div>
            
        <script src="../js/nav.js"></script>



        <!-- IMPORTANT: rajouter le code ci-dessous pour que la nav fonctionne correctement -->


        <!--
                <div class="main-content"> VOTRE CODE </div>     
        -->
