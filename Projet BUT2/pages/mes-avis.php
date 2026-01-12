<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mes avis</title>
        <link rel="stylesheet" href="../css/mes-avis.css">
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
    <body class="FO">
        <nav class="nav-bar">
        <?php
        include_once 'nav.php'; ?>
        </nav>
        <?php
        include('../sql/connect_params.php');

        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);


        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(isset($_POST['id_avis'])){
                try{
                    $stmt = $dbh->prepare("DELETE FROM pact.avis WHERE idA = :ida");
                    $stmt->bindParam(':ida', $_POST['id_avis']);
                    $stmt->execute();
                }catch(PDOException $e){
                    print "Erreur !: " . $e->getMessage() . "<br/>";
                    die();
                }
            }else{
                
            }
        }

        try{
            $stmt = $dbh->prepare("SELECT * FROM pact.experience WHERE idC = :id_user");
            $stmt->bindParam(':id_user', $_COOKIE['user']);
            $stmt->execute();
            $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
        
        $dbh = null;
        ?>

        <div class="main-content">
            <!--<section class="mes-avis-donnees">
                <article class="ligne-info">
                    <div>
                        <p>Prénom</p>
                        <textarea placeholder=""></textarea>
                    </div>
                    <div>
                        <p>Nom</p>
                        <textarea placeholder=""></textarea>
                    </div>
                </article>
                <article class="ligne-info">
                    <div>
                        <p>Téléphone</p>
                        <textarea placeholder=""></textarea>
                    </div>
                    <div>
                        <p>Adresse</p>
                        <textarea placeholder=""></textarea>
                    </div>
                </article>
                <article class="ligne-info">
                    <div>
                        <p>Code Postal</p>
                        <textarea placeholder=""></textarea>
                    </div>
                    <div>
                        <p>Ville</p>
                        <textarea placeholder=""></textarea>
                    </div>
                </article>
                <button id="modif-infos">Modifier les informations</button>
            </section>-->
            <section class="mes-avis">
            <h1>Mes avis</h1>
                <?php
                foreach($avis as $a) { 
                ?>
                <article class="avis">
                    <?php


                    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);

                    try{

                        $stmt=$dbh->prepare("SELECT * FROM pact.offre WHERE idO = :id_offre");
                        $stmt->bindParam(':id_offre', $a['ido']);
                        $stmt->execute();
                
                        $offre = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                    } catch (PDOException $e) {
                        print "Erreur !: " . $e->getMessage() . "<br/>";
                        die();
                    }

                    $dbh = null;


                    $fullstar = $a['note'];
                    $emptystar = 5-$a['note'];
                    ?>
                    
                    <h3 id="titre-offre"><?php echo $offre['titre']?></h3>
                    <p id="adresse-offre"><?php echo $offre['numero_voie']?> <?php echo $offre['voie']?>, <?php echo $offre['code_postal']?> </p>
                    <div class="note-date-publi">
                        <?php for($i=0; $i<$fullstar;$i++){?>
                            <img src="../icons/etoile_bleue.png" width="20px" height="20px">
                        <?php } ?>
                        <?php for($i=0; $i<$emptystar;$i++){?>
                            <img src="../icons/etoile_bleue_vide.png" width="20px" height="20px">
                        <?php } ?>
                        <p id="date-publi"><?php echo $a['date_publication']?></p>
                    </div>
                    <p id="commentaire"><?php echo $a['corps']?></p>
                    <div class="boutons-avis">
                        <form method="POST" action="mes-avis.php" class="form-supp">
                            <input type="hidden" name="id_avis" value=<?php echo $a['ida']; ?>>
                            <input type="button" name="bouton_supp" class="bouton-supp" value="Supprimer l'avis">
                        </form>
                        <a href="detail-offre.php?value=<?php echo $offre['ido'] ?>">Voir l'offre</a>
                    </div>
                </article>
                <?php
                } ?>
            </section>
        </div>
    </body>
    <script src="../js/mes-avis.js"></script>
    <div class = "footer-nav">
        <?php
        include_once 'footerNav.php'; ?>
    </div>
</html>
