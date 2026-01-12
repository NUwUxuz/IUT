<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    // echo '<pre>';
    // print_r($_POST);
    // echo '<pre>';
    
    
}
$photos = [];

include('../sql/connect_params.php');


ini_set('display_errors', 1);
error_reporting(E_ALL);

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Création d'offre</title>

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
    <body class="BO">
        <nav> 
            <?php include_once('nav.php'); ?> 
        </nav>

        <div class="main-content-crea-modif-offre"> 
            <h1 class="TS-bold">Créez votre offre</h1>            
            <form action="previsualisation_offre.php" method="post" enctype="multipart/form-data">
                <div id="form-content" class="FS">
                    <div id="left-part">
                        <div id="left-part-content">
                            <div id="title-categorie">
                                <!-- Champ "Titre" -->
                                <div class="container-crea-modif-offre" id="titre-container">
                                    <label for="titre">Titre de l'offre*</label>
                                    <p id="error-message-titre" class="error-message"></p>
                                    <input type="text" id="titre" class="FS" name="titre" placeholder="Roméo et Juliette" required value="<?php echo isset($_POST['titre']) ? $_POST['titre'] : ''; ?>"  />
                                    
                                </div>
                                <!-- Champ "Catégorie" -->
                                <div class="container-crea-modif-offre categorie-container">
                                    <label for="categorie-offre">Catégorie de l'offre*</label>
                                    <select id="categorie-offre" name="categorie" class="FS" required>
                                        <option value="" disabled hidden <?= empty($_POST['categorie']) ? 'selected' : '' ?> id="first-option">Catégorie de l'offre</option>
                                        <option value="activite" <?= isset($_POST['categorie']) && $_POST['categorie'] === 'activite' ? 'selected' : '' ?> >Activité</option>
                                        <option value="visite" <?= isset($_POST['categorie']) && $_POST['categorie'] === 'visite' ? 'selected' : '' ?> >Visite</option>
                                        <option value="spectacle" <?= isset($_POST['categorie']) && $_POST['categorie'] === 'spectacle' ? 'selected' : '' ?> >Spectacle</option>
                                        <option value="parcAttraction" <?= isset($_POST['categorie']) && $_POST['categorie'] === 'parcAttraction' ? 'selected' : '' ?> >Parc d'attraction</option>
                                        <option value="restauration" <?= isset($_POST['categorie']) && $_POST['categorie'] === 'restauration' ? 'selected' : '' ?> >Restauration</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Champ "Résumé" -->
                            <div class="container-crea-modif-offre" id="resume-container">
                                <label for="resume">Résumé*</label>
                                <p id="resume-error-message" class="error-message"></p>
                                <textarea id="resume" class="FS textarea-modif-crea-offre" name="resume" placeholder="L'histoire d'amour et tragique de..." required ><?php echo isset($_POST['resume']) ? $_POST['resume'] : ''; ?></textarea>
                            </div>
                            <!-- Champ "Description" -->
                            <div class="container-crea-modif-offre" id="description-container">
                                <label for="description">Description*</label>
                                <p id="description-error-message" class="error-message"></p>
                                <textarea id="description" class="FS textarea-modif-crea-offre" name="description" placeholder="Spectacle se déroulant au théatre du..." required ><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                            </div>

                            <!-- Champ "Site web" -->
                            <div class="container-crea-modif-offre" id="site-container">
                                <label for="site">Site web</label>
                                <p id="site-error-message" class="error-message"></p>
                                <input type="text" id="site" class="FS" name="site" placeholder="https://exemple.com" value="<?php echo isset($_POST['site']) ? $_POST['site'] : ''; ?>">
                            </div>


                            <!-- Champ "Tags" -->
                            <div id="tag-container">
                                <label>Tags</label>
                                <div id="tags-items-container">
                                <input type="checkbox" class="hidden" name="culturel" id="culturel" value="Culturel" <?php echo isset($_POST['culturel']) ? 'checked' : ''; ?> >
                                    <label for="culturel">Culturel</label>

                                    <input type="checkbox" class="hidden" name="patrimoine" id="patrimoine" value="Patrimoine" <?php echo isset($_POST['patrimoine']) ? 'checked' : ''; ?> >
                                    <label for="patrimoine">Patrimoine</label>

                                    <input type="checkbox" class="hidden" name="histoire" id="histoire" value="Histoire" <?php echo isset($_POST['histoire']) ? 'checked' : ''; ?> >
                                    <label for="histoire">Histoire</label>

                                    <input type="checkbox" class="hidden" name="urbain" id="urbain" value="Urbain" <?php echo isset($_POST['urbain']) ? 'checked' : ''; ?>>
                                    <label for="urbain">Urbain</label>

                                    <input type="checkbox" class="hidden" name="nature" id="nature" value="Nature" <?php echo isset($_POST['nature']) ? 'checked' : ''; ?>>
                                    <label for="nature">Nature</label>

                                    <input type="checkbox" class="hidden" name="plein-air" id="plein-air" value="Pleine air" <?php echo isset($_POST['plein-air']) ? 'checked' : ''; ?>>
                                    <label for="plein-air">Pleine air</label>

                                    <input type="checkbox" class="hidden" name="sport" id="sport" value="Sport" <?php echo isset($_POST['sport']) ? 'checked' : ''; ?>>
                                    <label for="sport">Sport</label>

                                    <input type="checkbox" class="hidden" name="nautique" id="nautique" value="Nautique" <?php echo isset($_POST['nautique']) ? 'checked' : ''; ?>>
                                    <label for="nautique">Nautique</label>

                                    <input type="checkbox" class="hidden" name="gastronomie" id="gastronomie" value="Gastronomie" <?php echo isset($_POST['gastronomie']) ? 'checked' : ''; ?>>
                                    <label for="gastronomie">Gastronomie</label>

                                    <input type="checkbox" class="hidden" name="musee" id="musee" value="Musée" <?php echo isset($_POST['musee']) ? 'checked' : ''; ?>>
                                    <label for="musee">Musée</label>

                                    <input type="checkbox" class="hidden" name="atelier" id="atelier" value="Atelier" <?php echo isset($_POST['atelier']) ? 'checked' : ''; ?>>
                                    <label for="atelier">Atelier</label>

                                    <input type="checkbox" class="hidden" name="musique" id="musique" value="Musique" <?php echo isset($_POST['musique']) ? 'checked' : ''; ?>>
                                    <label for="musique">Musique</label>

                                    <input type="checkbox" class="hidden" name="famille" id="famille" value="Famille" <?php echo isset($_POST['famille']) ? 'checked' : ''; ?>>
                                    <label for="famille">Famille</label>

                                    <input type="checkbox" class="hidden" name="cinema" id="cinema" value="Cinéma" <?php echo isset($_POST['cinema']) ? 'checked' : ''; ?>>
                                    <label for="cinema">Cinéma</label>

                                    <input type="checkbox" class="hidden" name="cirque" id="cirque" value="Cirque" <?php echo isset($_POST['cirque']) ? 'checked' : ''; ?>>
                                    <label for="cirque">Cirque</label>

                                    <input type="checkbox" class="hidden" name="son-et-lumiere" id="son-et-lumiere" value="Son et lumière" <?php echo isset($_POST['son-et-lumiere']) ? 'checked' : ''; ?>>
                                    <label for="son-et-lumiere">Son et lumière</label>

                                    <input type="checkbox" class="hidden" name="humour" id="humour" value="Humour" <?php echo isset($_POST['humour']) ? 'checked' : ''; ?>>
                                    <label for="humour">Humour</label>

                                    <input type="checkbox" class="hidden" name="francais" id="francais" value="Français" <?php echo isset($_POST['francais']) ? 'checked' : ''; ?>>
                                    <label for="francais">Français</label>

                                    <input type="checkbox" class="hidden" name="fruit-de-mer" id="fruit-de-mer" value="Fruit de mer" <?php echo isset($_POST['fruit-de-mer']) ? 'checked' : ''; ?>>
                                    <label for="fruit-de-mer">Fruit de mer</label>

                                    <input type="checkbox" class="hidden" name="asiatique" id="asiatique" value="Asiatique" <?php echo isset($_POST['asiatique']) ? 'checked' : ''; ?>>
                                    <label for="asiatique">Asiatique</label>

                                    <input type="checkbox" class="hidden" name="indienne" id="indienne" value="Indienne" <?php echo isset($_POST['indienne']) ? 'checked' : ''; ?>>
                                    <label for="indienne">Indienne</label>

                                    <input type="checkbox" class="hidden" name="italienne" id="italienne" value="Italienne" <?php echo isset($_POST['italienne']) ? 'checked' : ''; ?>>
                                    <label for="italienne">Italienne</label>

                                    <input type="checkbox" class="hidden" name="gastronomique" id="gastronomique" value="Gastronomique" <?php echo isset($_POST['gastronomique']) ? 'checked' : ''; ?>>
                                    <label for="gastronomique">Gastronomique</label>

                                    <input type="checkbox" class="hidden" name="restauration-rapide" id="restauration-rapide" value="Restauration rapide" <?php echo isset($_POST['restauration-rapide']) ? 'checked' : ''; ?>>
                                    <label for="restauration-rapide">Restauration rapide</label>

                                    <input type="checkbox" class="hidden" name="creperie" id="creperie" value="Crêperie" <?php echo isset($_POST['creperie']) ? 'checked' : ''; ?>>
                                    <label for="creperie">Crêperie</label>

                                    <input type="checkbox" class="hidden" name="vegetarienne" id="vegetarienne" value="Végétarienne" <?php echo isset($_POST['vegetarienne']) ? 'checked' : ''; ?>>
                                    <label for="vegetarienne">Végétarienne</label>

                                    <input type="checkbox" class="hidden" name="vegetalienne" id="vegetalienne" value="Végétalienne" <?php echo isset($_POST['vegetalienne']) ? 'checked' : ''; ?>>
                                    <label for="vegetalienne">Végétalienne</label>

                                    <input type="checkbox" class="hidden" name="kebab" id="kebab" value="Kebab" <?php echo isset($_POST['kebab']) ? 'checked' : ''; ?>>
                                    <label for="kebab">Kebab</label>
                                </div>
                            </div>
                            <!-- Bouton de soumission -->
                            <div id="publier-retour">
                                <input id="submit-button" type="submit" class="FS" value="Prévisualiser" />
                                <button class="FS" onclick="confirmer(event)">
                                    <img src="../icons/arrow-left-o.svg" alt="Bouton Image">
                                    Retour
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="right-part">
                        <div id="right-part-content">
                            <div id="top-right-part-content">
                                <!-- Champ "Adresse" -->
                                <div class="container-crea-modif-offre" id="adresse-container">
                                    <label for="adresse">Adresse*</label>
                                    <p id="adresse-error-message" class="error-message"></p>
                                    <input type="text" id="adresse" class="FS" name="adresse" placeholder="5 rue de l'exemple" required value="<?php echo isset($_POST['adresse']) ? $_POST['adresse'] : ''; ?>"/>
                                </div>
                                <div class="container-crea-modif-offre" id="complementAdresse-container">
                                    <label for="complement">Complément d'adresse</label>
                                    <p id="complement-error-message" class="error-message"></p>
                                    <input type="text" id="complement" class="FS" name="complement" placeholder="batiment B, salle 105" value="<?php echo isset($_POST['complement']) ? $_POST['complement'] : ''; ?>"/>
                                </div>
                                <div id="codeP-ville">
                                    <!-- Champ Code postal -->
                                    <div class="container-crea-modif-offre" id="codeP-container">
                                        <label for="codeP">Code Postal*</label>
                                        <p id="postal-code-error-message" class="error-message"></p>
                                        <input type="text" id="codeP" class="FS" name="codeP" placeholder="22100" required value="<?php echo isset($_POST['codeP']) ? $_POST['codeP'] : ''; ?>"/>
                                    </div>
                                    <!-- Champ "ville" -->
                                    <div class="container-crea-modif-offre" id="ville-container">
                                        <label for="ville">Ville*</label>
                                        <p id="city-error-message" class="error-message"></p>
                                        <input type="text" id="ville" class="FS" name="ville" placeholder="Lannion" required value="<?php echo isset($_POST['ville']) ? $_POST['ville'] : ''; ?>"/>
                                    </div>
                                </div>
                                
                                <!-- Champ "Accesibilité" -->
                                <div class="container-crea-modif-offre" id="accessibilite-container">
                                    <label for="accessibilite">Accessibilité*</label>
                                    <p id="accessibility-error-message" class="error-message"></p>
                                    <textarea id="accessibilite" class="FS textarea-modif-crea-offre" name="accessibilite" placeholder="Entrée du théatre accessible en fauteuil roulant et amménagé pour.." required><?php echo isset($_POST['accessibilite']) ? $_POST['accessibilite'] : ''; ?></textarea>
                                </div>
                                <!-- Champ "prix minimum" -->
                                <div id="duree-prix-age">
                                    <div class="container-crea-modif-offre" id="duree-container">
                                        <label for="duree">Durée*</label>
                                        <p id="duree-error-message" class="error-message"></p>
                                        <input type="text" id="duree" class="FS" name="duree" placeholder="01:15" required value="<?php echo isset($_POST['duree']) ? $_POST['duree'] : ''; ?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="nbattraction-container">
                                        <label for="nbattraction">Nombre d'attractions*</label>
                                        <p id="nbr_attractions-error-message" class="error-message"></p>
                                        <input type="text" id="nbattraction" class="FS" name="nbattraction" placeholder="25" required value="<?php echo isset($_POST['nbattraction']) ? $_POST['nbattraction'] : ''; ?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="prix-container">
                                        <label for="prix">Prix minimum (€)*</label>
                                        <p id="prix-error-message" class="error-message"></p>
                                        <input type="text" id="prix" class="FS" name="prix" placeholder="25" required value="<?php echo isset($_POST['prix']) ? $_POST['prix'] : ''; ?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="age-container">
                                        <label for="age">Âge minimum*</label>
                                        <p id="age_min-error-message" class="error-message"></p>
                                        <input type="text" id="age" class="FS" name="age" placeholder="8" required value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="capacite-container">
                                        <label for="capacite">Nombre de places*</label>
                                        <p id="capacite-error-message" class="error-message"></p>
                                        <input type="text" id="capacite" class="FS" name="capacite" placeholder="230" required value="<?php echo isset($_POST['capacite']) ? $_POST['capacite'] : ''; ?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre categorie-container" id="categorie-prix-container">
                                        <label for="categorie-prix">Gamme de prix*</label>
                                        <select id="categorie-prix" name="categorie-prix" class="FS" required>
                                            <option value="" disabled hidden <?= empty($_POST['categorie-prix']) ? 'selected' : '' ?> id="first-option">Gamme de prix</option>
                                            <option value="€" <?= isset($_POST['categorie-prix']) && $_POST['categorie-prix'] === '€' ? 'selected' : '' ?>>€</option>
                                            <option value="€€" <?= isset($_POST['categorie-prix']) && $_POST['categorie-prix'] === '€€' ? 'selected' : '' ?>>€€</option>
                                            <option value="€€€" <?= isset($_POST['categorie-prix']) && $_POST['categorie-prix'] === '€€€' ? 'selected' : '' ?>>€€€</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="container-crea-modif-offre" id="pinclus-container">
                                    <label for="pinclus">Prestation incluse</label>
                                    <p id="pinclus-error-message" class="error-message"></p>
                                    <textarea id="pinclus" class="FS textarea-modif-crea-offre" name="pinclus" placeholder="Kayak, Pagaie, Gilet de sauvetage.."><?php echo isset($_POST['pinclus']) ? $_POST['pinclus'] : ''; ?></textarea>
                                </div>
                                <div class="container-crea-modif-offre" id="pexclus-container">
                                    <label for="pexclus">Prestation excluse</label>
                                    <p id="pexclus-error-message" class="error-message"></p>
                                    <textarea id="pexclus" class="FS textarea-modif-crea-offre" name="pexclus" placeholder="Casque,.."><?php echo isset($_POST['pexclus']) ? $_POST['pexclus'] : ''; ?></textarea>
                                </div>
                                <div class="container-crea-modif-offre" id="langue-container">
                                    <label for="langue">Langue</label>
                                    <p id="langue-error-message" class="error-message"></p>
                                    <input type="text" id="langue" class="FS" name="langue" placeholder="Saisir" value="<?php echo isset($_POST['langue']) ? $_POST['langue'] : ''; ?>"/>
                                </div>

                                <div id="repas-map">
                                    <div id="repas-container">
                                        <label>Repas servis</label>
                                        <div id="repas-items-container">
                                            <div id="petit-dejeuner">
                                                <input type="checkbox" id="petit-dejeuner" name="petit-dejeuner" value="petit-dejeuner" <?php echo isset($_POST['petit-dejeuner']) ? 'checked' : ''; ?>/>
                                                <label for="petit-dejeuner">Petit-déjeuner</label>
                                            </div>
                                            <div id="brunch">
                                                <input type="checkbox" id="brunch" name="brunch" value="brunch" <?php echo isset($_POST['brunch']) ? 'checked' : ''; ?>/>
                                                <label for="brunch">Brunch</label>
                                            </div>
                                            <div id="dejeuner">
                                                <input type="checkbox" id="dejeuner" name="dejeuner" value="dejeuner" <?php echo isset($_POST['dejeuner']) ? 'checked' : ''; ?>/>
                                                <label for="dejeuner">Déjeuner</label>
                                            </div>
                                            <div id="dinner">
                                                <input type="checkbox" id="diner" name="diner" value="diner" <?php echo isset($_POST['diner']) ? 'checked' : ''; ?>/>
                                                <label for="diner">Dîner</label>
                                            </div>
                                            <div id="boissons">
                                                <input type="checkbox" id="boissons" name="boissons" value="boissons" <?php echo isset($_POST['boissons']) ? 'checked' : ''; ?>/>
                                                <label for="boissons">Boissons</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!---- Champs ajout carte --->
                                    <div id="map-image">
                                        <label>Plan du parc</label>
                                        <!-- Éléments pour afficher les images importées -->
                                        <div id="map-image-container"> <?php
                                        if (isset($_POST['mapPhoto'])){
                                            if ($_POST['mapPhoto'] === ''){
                                                $src = '../images/rien.png';
                                                $photos[3] = 'false';
                                            } else {
                                                $src = '../images/'.$_POST['mapPhoto'];
                                                $photos[3] = $_POST['mapPhoto'];
                                            }
                                            
                                        }else {
                                            $src ='../images/rien.png';
                                            $photos[3] = 'false';
                                        }?>
                                    
                                    <img id="selected-image" src="<?php echo $src ?>" alt="Image sélectionnée" />
                                        </div>
                                        <div id="map-file-container">
                                            <label for="map-file-upload" id="map-custom-file-upload">
                                                <img src="../icons/plus.svg" alt="Bouton Image">
                                                <p id="map-upload-text">Ajouter</p>
                                            </label>
                                            <input type="file" id="map-file-upload" name="map-file-upload" accept=".png, .jpg, .jpeg, .gif"/>
                                            <button id="map-file-delete" type="button">
                                                <img src="../icons/moins.svg" alt="Bouton Image">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="type-option"  <?php echo  ((string)$_COOKIE['type_compte'] == 'pro_prive') ?  '' : 'style="display: none;"';?>>
                                    <!-- Champ "Type" -->
                                    <div id="type-container">
                                        <label>Type d'offre</label>
                                        <!-- bulle info -->
                                        <div id="bulle1" class="info-container">
                                            <div class="info-icon">?</div>
                                            <div class="info-tooltip">
                                            - Standard : Type par défaut. <br> <br>
                                            - Premium : Permet en plus de « blacklister » un maximum de 3 avis sur une offre. <br> <br>
                                            Attention : Une fois choisi, le type d'offre ne peut pas être changé.                                           </div>
                                        </div>
                                        <!---------------->
                                        <div id="standard-premium">
                                            <div id="standard">                                                
                                                <input type="radio" id="standard" name="offre" value="standard" 
                                                    <?php 
                                                    echo isset($_POST['type']) 
                                                        ? ($_POST['type'] == 'standard' ? 'checked' : '') 
                                                        : (((string)$_COOKIE['type_compte'] == 'pro_prive') ? 'checked' : '');
                                                    ?> 
                                                />
                                                
                                                
                                                
                                                <label for="standard">Standard (2€/jour)</label>
                                            </div>
                                            <div id="premium">
                                                <!-- <input type="radio" id="premium" name="offre" value="premium" /> -->
                                                <input type="radio" id="premium" name="offre" value="premium" 
                                                    <?php 
                                                    echo isset($_POST['type']) 
                                                        ? ($_POST['type'] == 'premium' ? 'checked' : '') 
                                                        : '';
                                                    ?> 
                                                />
                                                <label for="premium">Premium (4€/jour)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Champ "Options" -->
                                    <div id="option-container">
                                        <label>Options</label>
                                        <!-- bulle info -->
                                        <div id="bulle2" class="info-container">
                                            <div class="info-icon">?</div>
                                            <div class="info-tooltip">
                                            - À la une : Offre mis en avant sur l'accueil (comprend aussi l'option en relief). <br> <br>
                                            - En relief : Offre mis en exergue à l'affichage dans la liste d'offres.                                
                                            </div>
                                        </div>
                                        <!---------------->
                                        <div id="une-relief">
                                            <div id="une">
                                                <input type="checkbox" id="une" name="une" value="À la Une" <?php echo isset($_POST['une']) && $_POST['une'] == 'À la Une' ? 'checked' : ''; ?> onclick="checkOnlyOne(this)"/>
                                                <label for="une">À la une (20€/semaine)</label>
                                            </div>
                                            <div id="relief">
                                                <input type="checkbox" id="relief" name="relief" value="En relief" <?php echo isset($_POST['relief']) && $_POST['relief'] == 'En relief' ? 'checked' : ''; ?> onclick="checkOnlyOne(this)"/>
                                                <label for="relief">En relief (10€/semaine)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <div id="bottom-right-part-content">                        
                                <!---- Champs ajout 3 images --->
                                <div id="file-container">
                                    <!---- Bouton 1 --->
                                    <label for="file-upload1" class="custom-file-upload">
                                        <img src="../icons/plus.svg" alt="Bouton Image">
                                        <p class="upload-text">Ajouter une image 1</p>
                                    </label>
                                    <input type="file" id="file-upload1" class="file-upload" name="file-upload1" accept=".png, .jpg, .jpeg, .gif"/>

                                    <!---- Bouton 2 --->
                                    <label for="file-upload2" class="custom-file-upload">
                                        <img src="../icons/plus.svg" alt="Bouton Image">
                                        <p class="upload-text">Ajouter une image 2</p>
                                    </label>
                                    <input type="file" id="file-upload2" class="file-upload" name="file-upload2" accept=".png, .jpg, .jpeg, .gif"/>
                                    <!---- Bouton 3 --->
                                    <label for="file-upload3" class="custom-file-upload">
                                        <img src="../icons/plus.svg" alt="Bouton Image">
                                        <p class="upload-text">Ajouter une image 3</p>
                                    </label>
                                    <input type="file" id="file-upload3" class="file-upload" name="file-upload3" accept=".png, .jpg, .jpeg, .gif"/>
                                    
                                    <button id="file-delete" type="button">
                                        <img src="../icons/moins.svg" alt="Bouton Image">
                                        Supprimer une image
                                    </button>
                                </div>
                                <p id="error-message-supprimer" style="color: red; display: none;">Vous ne pouvez pas supprimer une image inexistante !</p>
                                <p id="message-prevention">Les images ne sont pas essentielles pour la poursuite de la création. <br> Vous pourrez les ajouter ou les modifier plus tard.</p>
                                <!-- Éléments pour afficher les images importées -->
                                <div id="image-container">
                                    <?php if (isset($_POST['image0'])){
                                        if ($_POST['image0'] === 'null'){
                                            $src = '../images/rien.png';
                                            $photos[0] = 'false';
                                        } else {
                                            $src = '../images/'.$_POST['image0'];
                                            $photos[0] = $_POST['image0'];
                                        }
                                        
                                    }else {
                                        $src ='../images/rien.png';
                                        $photos[0] = 'false';
                                    }
                                    ?><img class="selected-image" src="<?php echo $src ?>" alt="Image sélectionnée1" /><?php
                                    if (isset($_POST['image1'])){
                                        if ($_POST['image1'] === 'null'){
                                            $src = '../images/rien.png';
                                            $photos[1] = 'false';
                                        } else {
                                            $src = '../images/'.$_POST['image1'];
                                            $photos[1] = $_POST['image1'];
                                        }
                                        
                                    }else {
                                        $src ='../images/rien.png';
                                        $photos[1] = 'false';
                                    }?>
                                    
                                    <img class="selected-image" src="<?php echo $src ?>" alt="Image sélectionnée2" /><?php
                                    if (isset($_POST['image2'])){
                                        if ($_POST['image2'] === 'null'){
                                            $src = '../images/rien.png';
                                            $photos[2] = 'false';
                                        } else {
                                            $src = '../images/'.$_POST['image2'];
                                            $photos[2] = $_POST['image2'];
                                        }
                                        
                                    }else {
                                        $src ='../images/rien.png';
                                        $photos[2] = 'false';
                                    }?>
                                    
                                    <img class="selected-image" src="<?php echo $src ?>" alt="Image sélectionnée3" />
                                </div>
                            </div>                     
                        </div>
                    </div>
                </div>    
                <input type="hidden" name="image0" class="img" value="<?php echo $photos[0]; ?>">
                <input type="hidden" name="image1" class="img" value="<?php echo $photos[1]; ?>">
                <input type="hidden" name="image2" class="img" value="<?php echo $photos[2]; ?>">

                <input type="hidden" name="mapPhoto" id="img" value="<?php echo $photos[3]; ?>">
            </form>
        </div>

        <script src="../js/creation_offre.js">
        </script>
    </body>
</html>
