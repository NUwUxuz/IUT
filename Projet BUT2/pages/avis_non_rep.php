<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Avis non répondu</title>
        <link rel="stylesheet" href="../css/styleGeneral.css">
        <link rel="icon" type="image/png" href="../logo/logo_reduit_rouge.png">
    </head>
    <body class="BO main-content" >
        <nav class="nav-bar-avis">
        <?php
        include_once 'nav.php'; ?>
        </nav>
        <?php
        include('../sql/connect_params.php');

        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);


        try{
            $stmt = $dbh->prepare("SELECT * FROM pact.experience");
            $stmt->execute();
            $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }

        try{
            $stmt = $dbh->prepare("SELECT * FROM pact._reponse");
            $stmt->execute();
            $reponse = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
        
        $dbh = null;
        ?>

        <div class="main-content-avis">
            <section class="mes-avis">
            <h1 class="h1-avis">Avis non répondu</h1>
                <?php
                $nbr_avis = 0;
                foreach($avis as $a) {
                    $check = true;
                    foreach($reponse as $rep) {
                        if($a['ida'] == $rep['ida']) {
                            $check = false;
                        }
                    }
                    if ($check) { ?>
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

                            if ($offre['idc'] == $_COOKIE['user']) {
                                $nbr_avis++;
                                $fullstar = $a['note'];
                                $emptystar = 5-$a['note'];
                                ?>

                                <article class="avis">
                                    <h3 class="h3-avis" id="titre-offre"><?php echo $offre['titre']?></h3>
                                    <p class="p-avis" id="adresse-offre"><?php echo $offre['numero_voie']?> <?php echo $offre['voie']?>, <?php echo $offre['code_postal']?> </p>
                                    <div class="note-date-publi">
                                        <?php for($i=0; $i<$fullstar;$i++){?>
                                            <img src="../icons/etoile_rouge.png" width="20px" height="20px">
                                        <?php } ?>
                                        <?php for($i=0; $i<$emptystar;$i++){?>
                                            <img src="../icons/etoile_rouge_vide.png" width="20px" height="20px">
                                        <?php } ?>
                                        <p id="date-publi"><?php echo $a['date_publication']?></p>
                                    </div>
                                    <p class="p-avis" id="commentaire"><?php echo $a['corps']?></p>
                                    <div class="boutons-avis">
                                        <a href="detail-offre.php?value=<?php echo $offre['ido'] ?>">Voir l'offre</a>
                                    </div>
                                </article>
                            <?php
                            }
                        }
                    }
                if ($nbr_avis == 0) { ?>
                    <p class="no_avis">Vous n'avez pas d'avis non répondu</p>
                <?php } ?>
            </section>
        </div>
    </body>
    <script src="../js/mes-avis.js"></script>
    <div class = "footer-nav">
        <?php
        include_once 'footerNav.php'; ?>
    </div>
</html>
