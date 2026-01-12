<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Filtre et Tri</title>
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
    <!-- Conteneur des boutons -->
    <div class="button-container">
        <!-- Bouton Filtrer -->
        <button id="filterButton">
            <span>filtrer</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path d="M10 18h4v-2h-4v2zm-7-7v2h14v-2h-14zm17-5h-20v2h20v-2z" />
            </svg>
        </button>

        <button id="sortButton">
            <span>trier</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                <path d="M3 18h6v-2H3v2zm0-5h12v-2H3v2zm0-7v2h18V6H3z" />
            </svg>
        </button>


        <div class="mobile sensTris NoSelect">
            <img src="../icons/sens.png" alt="Sens Du tri" titre="Sens Tris">
        </div>

    </div>

    <!-- Menu Filtrer -->
    <div id="overlay-filtre">
        <div id="menu">
            <div class="menu-header">
                <span>filtrer</span>
                <button id="closeButton">×</button>
            </div>
            <div class="all-filter mobile">
                <div class="filter mobile">
                    <div class="titleFilter">
                        <p class="FS">Catégories</p>
                        <img src="../icons/arrow.svg" id="fleche-filtre">
                    </div>
                    <div class="filtre-type mobile">
                        <div class="filtre-case">
                            <input type="checkbox" id="visite" name="Visite" />
                            <label class="label-filtre" for="visite">Visite</label>
                        </div>
                        <div class="filtre-case">
                            <input type="checkbox" id="spectacle" name="Spectacle" />
                            <label class="label-filtre" for="spectacle">Spectacle</label>
                        </div>
                        <div class="filtre-case">
                            <input type="checkbox" id="activite" name="Activite" />
                            <label class="label-filtre" for="activite">Activité</label>
                        </div>
                        <div class="filtre-case">
                            <input type="checkbox" id="restauration" name="Restauration" />
                            <label class="label-filtre" for="restauration">Restauration</label>
                        </div>
                        <div class="filtre-case">
                            <input type="checkbox" id="parc d'attraction" name="Parc d'attraction" />
                            <label class="label-filtre" for="parc d'attraction">Parc d'attraction</label>
                        </div>
                    </div>
                </div>
                <div class="filter mobile">
                    <div class="titleFilter">
                        <p class="FS">Lieux</p>
                        <img src="../icons/arrow.svg" id="fleche-filtre">
                    </div>
                    <div class="filtre-lieu mobile">
                        <div class="search-filter">
                            <input type="text" name="search" class="rechercheLieu" placeholder="Recherche">
                            <img src="../images/search_icon.png" alt="Recherche" title="Recherche"
                                class="rechercheLieuIMG">
                        </div>
                        <div class="filtre-lieu-case">
                            <?php foreach ($lieux as $lieu) { ?>
                            <div class="filtre-case lieu">
                                <input type="checkbox" id="<?php echo strtolower($lieu); ?>" name="<?php echo $lieu; ?>" />
                                <label class="label-filtre" for="<?php echo strtolower($lieu); ?>"><?php echo $lieu; ?></label>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="filter mobile">
                    <div class="titleFilter">
                        <p class="FS">Date de publication</p>
                        <img src="../icons/arrow.svg" id="fleche-filtre">
                    </div>
                    <div class="filtre-temps mobile">
                        <div id="calendar-start">
                            <label class="label-filtre" for="calendar-start">
                                Au plus tôt
                            </label>
                            <input type="date" name="date-start" id="date-start" class="filtre-temps-start">
                        </div>

                        <div id="calendar-end">
                            <label class="label-filtre" for="calendar-end">
                                Au plus tard
                            </label>
                            <input type="date" name="date-end" id="date-end" class="filtre-temps-end">
                        </div>
                    </div>
                </div>
                <div class="filter mobile">
                    
                    <div class="titleFilter">
                        <p class="FS">Prix</p>
                        <img src="../icons/arrow.svg" id="fleche-filtre">
                    </div>
                    <div class="filtre-prix mobile">
                        <div class="slider-container">
                            <input type="range" id="slider-left" min="<?php echo $prix_min ?>"
                                max="<?php echo $prix_max ?>" value="<?php echo $prix_min ?>" step="<?php echo $step ?>"
                                class="filtre-prix-min">
                            <input type="range" id="slider-right" min="<?php echo $prix_min ?>"
                                max="<?php echo $prix_max ?>" value="<?php echo $prix_max ?>" step="<?php echo $step ?>"
                                class="filtre-prix-max">

                            <div class="value-container">
                                <span id="value-left"><?php echo $prix_min ?>€</span>
                                <span id="value-right"><?php echo $prix_max ?>€</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="filter mobile">
                    <div class="titleFilter">
                        <p class="FS">Notes</p>
                        <img src="../icons/arrow.svg" id="fleche-filtre">
                    </div>
                    <div class="filtre-note mobile">
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
                                <img src="../icons/<?php echo $star ? 'etoile_rouge' : 'etoile_rouge_vide'; ?>.png"
                                    alt="<?php echo $star ? 'Etoile' : 'Etoile Vide'; ?>"
                                    title="<?php echo $star ? 'Etoile' : 'Etoile Vide'; ?>">
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="filter mobile">
                    <div class="titleFilter">
                        <p class="FS">Ouvert / Fermé</p>
                        <img src="../icons/arrow.svg" id="fleche-filtre">
                    </div>
                    <div class="filtre-ouvert mobile">
                        <div class="choixOuvert">
                            <input type="checkbox" name="ouverture" id="ouvert">
                            <label class="label-filtre" for="ouvert">Ouvert</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Menu Trier -->

    <div id="sortMenu">
        <button class="menu-item-tri" id="prixCroissant">Prix</button>
        <!-- <button class="menu-item-tri" id="prixDecroissant">Prix décroissant</button> -->
        <!-- <button class="menu-item-tri" id="presDeMoi">Plus proche</button>
        <button class="menu-item-tri" id="presDeMoi">Plus proche</button> -->
        <button class="menu-item-tri" id="notesCroissantes">Notes</button>
        <!-- <button class="menu-item-tri" id="notesDecroissantes">Notes décroissantes</button> -->
    </div>


    <script type="module" src="../js/scriptFilter.js"></script>
</body>

</html>