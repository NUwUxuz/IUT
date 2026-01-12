<?php


include('../sql/connect_params.php');

try{
    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
        $user, $pass);
        
        $stmt = $dbh->prepare(
            "SELECT o.en_ligne, o.titre, o.prix_min, o.moy_note, o.nbravis, o.resume, a.ville, p.src_image, o.ido
             FROM pact.offre o
             JOIN pact._adresse a ON o.idAdresse = a.idAdresse
             LEFT JOIN pact.photo_offre p ON o.idO = p.idO"
        );
    
    $stmt->execute();

    
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ido, titre, resume, type, nom_type, cout, description, prix_min, moy_note, nbravis, accessibilitev, categorie, date_creation, date_publication, idadresse, ville, numero_voie, voie, code_postal, complement, idc

} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}


?>
<head>
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <link rel="stylesheet" href="../css/accueilBO.css">
    <script src="../js/accueil.js"></script>
    <link rel="icon" type="image/png" href="../logo/logo_reduit_vert.png">


</head>

<html>
    <?php 
    if ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") { ?>
        <body class="BO"> <?php   
    } else { ?>
        <body class="FO"> <?php
    } ?>
        <nav class="desktop-element">
         <?php include_once'nav.php'; ?>
        </nav>
        <header class="phone-element">
        <?php
            include 'header.php';
        ?>
            <div id="filters">
                <?php include 'filtreMobile.php'; ?>
            </div>
        </header>
        <div class="main-content">
            <!-- Contenu principal de la page -->
            <header class="desktop-element">
                <h1 class="TS">Accueil</h1>
            </header>
            <nav class="desktop-element">
                <div class="nav_space">
                    <div class="space-button">
                        <div class="search">
                            <input type="text" name="search" id="search" placeholder="Search" class="FS">
                            <img src="../images/search_icon.png" alt="Search" title="Search">
                        </div>

                        <div class="filtrer-trier"> 
                            <div class="filtrer-bouton NoSelect">
                                <p class="FS">Filtrer par</p>
                                <img src="../images/fleche.png" alt="Fleche" title="Fleche">
                            </div>

                            <div class="dropdown NoSelect">
                                <div class="dropdown-toggle trier">
                                    <p class="FS">Trier par</p>
                                    <img src="../images/fleche.png" alt="Fleche" title="Fleche">
                                </div>
                                <ul class="dropdown-menu">
                                    <li><a href="#0">Prix croissant</a></li>
                                    <li><a href="#0">Prix décroissant</a></li>
                                    <li><a href="#0">Notes croissantes</a></li>
                                    <li><a href="#0">Notes décroissantes</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="NouveauALaUne">
                        <div class="boutonNouveau NoSelect">
                            <p class="FS">Nouveau</p>
                        </div>
                        <div class="boutonALaUne NoSelect">
                            <p class="FS">A la une</p>
                        </div>
                    </div>
                </div>

            </nav>

            <div class="all-filter">
                <div class="filter filtre-type">
                    <div class="filtre-case">
                        <input type="checkbox" id="visite" name="visite" />
                        <label for="visite">Visite</label>
                    </div>
                    <div class="filtre-case">
                        <input type="checkbox" id="spectacle" name="spectacle" />
                        <label for="spectacle">Spectacle</label>
                    </div>
                    <div class="filtre-case">
                        <input type="checkbox" id="Pattractions" name="Pattractions" />
                        <label for="Pattractions">Parc d'attractions</label>
                    </div>
                    <div class="filtre-case">
                        <input type="checkbox" id="restauration" name="restauration" />
                        <label for="restauration">Restauration</label>
                    </div>
                </div>
                <div class="filter filtre-lieu">
                    <div class="search-filter">
                        <input type="text" name="search" id="search" placeholder="Search">
                        <img src="../images/search_icon.png" alt="Search" title="Search">
                    </div>
                    <div class="filtre-case">
                        <input type="checkbox" id="sb" name="sb" />
                        <label for="sb">Saint-Brieuc</label>
                    </div>
                    <div class="filtre-case">
                        <input type="checkbox" id="lannion" name="lannion" />
                        <label for="lannion">Lannion</label>
                    </div>
                    <div class="filtre-case">
                        <input type="checkbox" id="plérin" name="plérin" />
                        <label for="plérin">Plérin</label>
                    </div>
                </div>
                <div class="filter filtre-temps">
                    <div id="calendar-start">
                        <label for="calendar-start">
                            Date de début
                        </label>
                        <input type="date" name="date-start" id="date-start">
                    </div>
                    
                    <div id="calendar-end">
                        <label for="calendar-end">
                            Date de Fin
                        </label>
                        <input type="date" name="date-end" id="date-end">
                    </div>
                </div>
                <div class="filter filtre-prix">
                    <div class="slider-container">
                        <input type="range" id="slider-left" min="0" max="1000" value="0" step="10">
                        <input type="range" id="slider-right" min="0" max="1000" value="1000" step="10">
                        <div class="value-container">
                            <span id="value-left">0€</span>
                            <span id="value-right">1000€</span>
                        </div>
                    </div>
                    <button type="submit" id="valider-prix" class="NoSelect">Valider</button>
                </div>
                <div class="filter filtre-note">
                    <?php
                    $notes = [
                        5 => [true, true, true, true, true],
                        4 => [true, true, true, true, false],
                        3 => [true, true, true, false, false],
                        2 => [true, true, false, false, false],
                        1 => [true, false, false, false, false],
                    ];

                    foreach ($notes as $note => $stars) { ?>
                        <div class="note">
                            <input type="checkbox" id="note-<?php echo $note; ?>" name="note-<?php echo $note; ?>" />
                            <div class="etoileNoteFiltre">
                                <?php foreach ($stars as $star) { ?>
                                    <img src="../icons/<?php echo $star ? 'etoile_rouge' : 'etoile_rouge_vide'; ?>.png" alt="<?php echo $star ? 'Etoile' : 'Etoile Vide'; ?>" title="<?php echo $star ? 'Etoile' : 'Etoile Vide'; ?>">
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <main>
            <?php
            foreach ($resultat as $key => $value) { 
                if ($value["en_ligne"]) { ?>
                    <div class="carte_offre desktop-element">
                        <a href="detail-offre.php?value=<?php echo $value["ido"]; ?>">
                            <?php if ($value['src_image']) { ?>
                                <img src="../images/<?php echo $value['src_image']; ?>" alt="Photo offre">
                            <?php } else { ?>
                                <img src="../images/imagesReferences/placeholder.jpg" alt="Image par défaut">
                            <?php } ?>
                            
                            <p class="titreAnnonce"><?php echo $value["titre"] ?></p>
                            <div class="infoAnnonce">
                                <p><?php echo $value["prix_min"] ?>€</p>
                                
                                <div class="etoileNote">
                                    <?php 
                                    if ($value["moy_note"] != null && $value["nbravis"] != 0) {
                                        for ($i=0; $i < $value["moy_note"]; $i++) { ?>
                                            <img src="../icons/etoile_rouge.png" alt="Etoile">
                                        <?php }
                                        for ($i=0; $i < 5-$value["moy_note"]; $i++) { ?>
                                            <img src="../icons/etoile_rouge_vide.png" alt="Etoile Vide">
                                    <?php }} ?>
                                    <?php if ($value["moy_note"] != null && $value["nbravis"] != 0) { ?>
                                        <p>(<?php echo $value["nbravis"] ?>)</p> <?php } ?>
                                </div>

                            </div>
                        </a>
                    </div>

                    <div class="carte_offre phone-element">
                        <a href="detail-offre.php?value=<?php echo $value["ido"]; ?>">

                        <div class="InfoOffreMobile">
                            <p class="titreAnnonce"><?php echo $value["titre"] ?></p>

                            <p class="resumeAnnonce"><?php echo $value["resume"] ?></p>

                            <p class="villeAnnonce"><?php echo $value["ville"] ?></p>
                        </div>
                        <div class="NoteImageOffreMobile">
                            <div class="etoileNote">
                                <?php
                                if ($value["moy_note"] != null && $value["nbravis"] != 0) {
                                    for ($i=0; $i < $value["moy_note"]; $i++) { ?>
                                        <img src="../icons/etoile_rouge.png" alt="Etoile">
                                    <?php }
                                    for ($i=0; $i < 5-$value["moy_note"]; $i++) { ?>
                                    
                                        <img src="../icons/etoile_rouge_vide.png" alt="Etoile Vide">
                                <?php }} ?>
                            </div>
                        
                            <div class="conteneurImage">
                                <?php if ($value['src_image']) { ?>
                                    <img src="../images/<?php echo $value['src_image']; ?>" alt="Photo offre">


                                <?php } else { ?>
                                    <img src="../images/imagesReferences/placeholder.jpg" alt="Image par défaut">
                                <?php } ?>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php } } ?>

                                
            </main>
            <div id="popup" class="popup">
                <img src="../images/croix.png" alt="" class="close-popup">
                <p>Vous aussi, postez votre offre dès maintenant</p>
                <a href="creation_compte.php" class="join-button">Nous rejoindre</a>
            </div>
            <footer>
                <?php include_once'footer.php'; ?>
            </footer>
        </div>
        
        <?php include_once'footerNav.php' ?>

            
    </body>
</html>

<?php 
$dbh = null;
?>