<?php

// Extraire la version du fichier config
function obtenirVersion($config) {
    $debut = strpos($config, 'VERSION=');
    $fin = strpos($config, "\n", $debut);
    $version = substr($config, $debut + strlen('VERSION='), $fin - $debut - strlen('VERSION='));
    return trim($version);
}

// Extraire le nom du projet du fichier config
function obtenirProjet($config) {
    $debut = strpos($config, 'PRODUIT=');
    $fin = strpos($config, "\n", $debut);
    $projet = substr($config, $debut + strlen('PRODUIT='), $fin - $debut - strlen('PRODUIT='));
    return trim($projet);
}

// Extraire le nom du client du fichier config
function obtenirClient($config) {
    $debut = strpos($config, 'CLIENT=');
    $fin = strpos($config, "\n", $debut);
    $client = substr($config, $debut + strlen('CLIENT='), $fin - $debut - strlen('CLIENT='));
    return trim($client);
}

// Retirer les caractères indésirables des commentaires
function retireslashEtoile($commentaire) {
    return str_replace(['/**', '*', '*/', '/', '\\', '/*'], '', $commentaire);
}

// Extraire le commentaire d'une structure
// Le Regex ici permet de reconnaitre le début et la fin d'un commentaire
function obtenirCommentaire($structure) {
    preg_match('/\/\*\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\//', $structure, $matches);
    if (isset($matches[0])) {
        return retireslashEtoile(trim($matches[0]));
    }
    return '';
}

// Retirer les commentaires d'une structure pour permettre d'afficher le code
function retirerCommentaire($code) {
    return preg_replace('/\/\*\*.*?\*\//s', '', $code);
}

// Retirer les commentaires d'une structure pour pemettre d'afficher le code
function retirerCommentaireStructure($code) {
    return preg_replace('/\/\*\*.*?\*\//s', '', $code);
}

// Extraire l'en-tête de code
function obtenirEntete($code) {
    $debut = strpos($code, '/**');
    $fin = strpos($code, '*/', $debut);
    if ($debut !== false && $fin !== false) {
        $taille = $fin + strlen('*/') - $debut;
        return retireslashEtoile(trim(substr($code, $debut, $taille)));
    }
}

// Extraire les #define
function obtenirDefines($code) {
    $debut = strpos($code, '#define');
    $defines = array();
    while ($debut !== false) {
        $finLigne = strpos($code, "\n", $debut);
        $ligneDefine = substr($code, $debut, $finLigne - $debut);
        $defines[] = trim($ligneDefine);
        $debut = strpos($code, '#define', $finLigne);
    }
    return $defines;
}

// Extraire les variables globales
function obtenirVarGlobal($code) {
    $debut = strpos($code, 'const');
    $varGlobal = array();
    while ($debut !== false) {
        $finLigne = strpos($code, "\n", $debut);
        $ligneVarGlobal = substr($code, $debut, $finLigne - $debut);
        $varGlobal[] = trim($ligneVarGlobal);
        $debut = strpos($code, 'const', $finLigne);
    }
    return $varGlobal;
}

// Extraire les structures
function obtenirStructures($code) {
    $debut = strpos($code, 'typedef struct');
    $structures = array();
    while ($debut !== false) {
        // la fin est : "} nomStructure; /**Commentaire*/" je dois obtenir toute la ligne
        $fin = strpos($code, '}', $debut);
        $fin = strpos($code, ';', $fin);
        $fin = strpos($code, '*/', $fin);
        $finLigne = strpos($code, "\n", $fin);
        $ligneStructure = substr($code, $debut, $finLigne - $debut);
        $structures[] = trim($ligneStructure);
        $debut = strpos($code, 'typedef struct', $finLigne);
    }
    return $structures;
}

// Extraire les commentaires des structures
function formateStructure($code) {
    $commentaire = [];
    $listeVariables = [];
    $commentaireFinal = [];
    // obtient les variables qui se situe entre un " " et un ";" et s'arrête au premier espace ne doit pas contenir la dernièe ligne qui est "}"
    // exemple de la ligne : "int numero;" je dois obtenir "numero"
    preg_match_all('/\\s+([^\\s]+)\\s*;/', $code, $matches);
    if (isset($matches[1])) {
        foreach ($matches[1] as $match) {
            $listeVariables[] = trim($match);
        }
    }
    //retire la dernière variable qui est le nom de la structure
    array_pop($listeVariables);

    // obtient tout les commentaires de la structure qui sont : "/** .. */"
    preg_match_all('/\/\*\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\//', $code, $matches);
    if (isset($matches[0])) {
        foreach ($matches[0] as $match) {
            $commentaire[] = retireslashEtoile(trim($match));
        }
    }
    // on veut mettre le nom de la variable devant le commentaire tel que : "nomVariable : commentaire" sauf pour la dernière ligne
    foreach ($listeVariables as $clef => $variable) {
        if (isset($commentaire[$clef])) {
            $commentaireFinal[] = $variable . ' : ' . $commentaire[$clef];
        }
    }
    // ajoute le commentaire de la dernière ligne
    if (isset($commentaire[$clef + 1])) {
        $commentaireFinal[] = $commentaire[$clef + 1];
    }
    return $commentaireFinal;
}

function obtenirFonctions($code) {
    $debut = strpos($code, ' * \brief');
    $fonctions = array();

    while ($debut !== false) {
        // Trouver la fin du commentaire et le début du code (marqué par '{')
        $finCommentaire = strpos($code, ' */', $debut);
        $debutCode = strpos($code, '{', $finCommentaire);

        // Trouver la fin du code (marquée par '}')
        $finCode = trouverFinCode($code, $debutCode);

        // Extraire les commentaires de la fonction
        $commentaire = substr($code, $debut, $finCommentaire - $debut + 4); // +4 pour inclure '*/'

        // Extraire le header de la fonction se trouve après la fin du commentaire */ et avant le début du code {
        $headerFonction = substr($code, $finCommentaire + 3, $debutCode - $finCommentaire - 3);

        // Extraire le début de la fonction (y compris la déclaration de la fonction)
        $debutFonction = substr($code, $debutCode, $finCode - $debutCode + 1); // +2 pour inclure '}'

        // Ajouter au tableau
        $fonction = trim($commentaire . $headerFonction . $debutFonction);
        $fonctions[] = $fonction;

        // Trouver la prochaine occurrence de ' * \brief'
        $debut = strpos($code, ' * \brief', $finCode);
    }

    return $fonctions;
}

// Trouver la fin du code (marquée par '}')
function trouverFinCode($code, $debutCode) {
    $level = 0;
    $pos = $debutCode;
    // Tant que le niveau de profondeur est supérieur à 0 et qu'on n'a pas atteint la fin du code
    do {
        $char = $code[$pos];
        if ($char === '{') {
            $level++;
        } elseif ($char === '}') {
            $level--;
        }
        $pos++;
    } while ($level > 0 && $pos < strlen($code));

    return $pos;
}

function retirerCommentaireFonction($code) {
    $code = preg_replace('/\*.+\./', '', $code);
    // retire  les espaces et retour à la ligne avant "*/" puit supprimer le "*/"
    $code = preg_replace('/\\s+\\*\\//', '', $code);
    return $code;
}

// Extraire le retour d'une fonction
function retourneFonction($code) {
    // Trouver la position du \return
    $posReturn = strpos($code, ' * \\return');
    if ($posReturn === false) {
        return null;
    }
    // Trouver la position du premier espace après le \return
    $posStart = strpos($code, ' ', $posReturn + strlen(' * \\return'));
    $posEnd = strpos($code, "\n", $posStart);
    $return = trim(substr($code, $posStart, $posEnd - $posStart));
    return str_replace(['return', "\n"], '', $return);
}

// Extraire les paramètres d'une fonction
function parametresFonction($code) {
    // Trouver la position du \param
    $parametres = array();
    $posParam = strpos($code, ' * \\param');
    if ($posParam === false) {
        return null;
    }
    // Tant qu'il y a des paramètres
    while ($posParam !== false) {
        $posStart = strpos($code, ' ', $posParam + strlen(' * \\param'));
        $posEnd = strpos($code, "\n", $posStart);
        $param = trim(substr($code, $posStart, $posEnd - $posStart));
        $parametres[] = str_replace(['param', "\n"], '', $param);
        
        $code = substr($code, $posEnd);
        $posParam = strpos($code, ' * \\param');
    }
    
    return $parametres;
}


function briefFonction($code) {
    // Trouver la position du \brief
    $debutCommentaire = strpos($code, ' * \brief');
    // Trouver la fin du commentaire (marquée par ' */' ou ' *\')
    $finCommentaire1 = strpos($code, ' */', $debutCommentaire);
    $finCommentaire2 = strpos($code, ' *\\', $debutCommentaire);
    // Choix de la fin du commentaire
    $finCommentaire = ($finCommentaire1 !== false) ? $finCommentaire1 : $finCommentaire2;
    if ($finCommentaire !== false) {
        // Extraire le commentaire
        $commentaire = substr($code, $debutCommentaire, $finCommentaire + 3 - $debutCommentaire); // +3 pour inclure ' */' ou ' *\'
        // Enlever le \brief et autres éléments indésirables
        $commentaire = str_replace([' * \brief', ' * ', ' *', '*', '/', '\\', 'brief', "\n"], '', $commentaire);
        return trim($commentaire);
    }
}

// Appelle la fonction pour obtenir la version et initialise les variables
$config = file_get_contents('config');
$version = obtenirVersion($config);
$projet = obtenirProjet($config);
$client = obtenirClient($config);

// Démarre le buffer de sortie pour pouvoir le sortir dans un fichier en html
ob_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation Technique - Version <?php echo $version ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        $files = glob('*.c');
        $nbcode = 0;
        foreach ($files as $file) :
            $code = file_get_contents($file);
            $definesContent = obtenirDefines($code);
            $enTeteDeCodeContent = obtenirEntete($code);
            $structuresContent = obtenirStructures($code); 
            $varGlobalContent = obtenirVarGlobal($code);
            $fonctionsContent = obtenirFonctions($code);
            $nbcode++;

    ?>
    <header>
        <h1>Documentation Technique : <?php echo $projet; ?></h1>
        <p>Client : <?php echo $client; ?> - Version <?php echo $version ?> - Date de Révision : <?php echo date('d/m/Y'); ?></p>
        <h2><?php echo $file; ?></h2>
    </header>

    <nav>
        <ul>
            <li><a href="#en-tete<?php echo $nbcode ?>">En-tête de code</a></li>
            <li><a href="#defines<?php echo $nbcode ?>">#define</a></li>
            <li><a href="#variablesglobales<?php echo $nbcode ?>">Variables Globales</a></li>
            <li><a href="#structures<?php echo $nbcode ?>">Structures</a></li>
            <li><a href="#fonctions<?php echo $nbcode ?>">Fonctions</a></li>
        </ul>
    </nav>

    <section id="en-tete<?php echo $nbcode ?>">
        <h2>En-tête de code</h2>
        <figure>
            <pre>
                <?php echo htmlspecialchars($enTeteDeCodeContent); ?>
            </pre>
        </figure>
    </section>

    <section id="defines<?php echo $nbcode ?>">
        <h2>#define</h2>
        <?php foreach ($definesContent as $define) : ?>
            <figure>
                <h3><?php echo obtenirCommentaire($define); ?></h3>
                <pre><?php echo htmlspecialchars(retirerCommentaire($define)); ?></pre>
            </figure>
            <?php if ($define !== end($definesContent)) : ?>
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>
    </section>

    <section id="variablesglobales<?php echo $nbcode ?>">
        <h2>Variables Globales</h2>
        <?php foreach ($varGlobalContent as $varGlobal) : ?>
            <figure>
                <h3><?php echo obtenirCommentaire($varGlobal); ?></h3>
                <pre><?php echo htmlspecialchars(retirerCommentaire($varGlobal)); ?></pre>
            </figure>
            <?php if ($varGlobal !== end($varGlobalContent)) : ?>
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>
    </section>

    <section id="structures<?php echo $nbcode ?>">
    <h2>Structures</h2>
    <?php foreach ($structuresContent as $structure) : ?>
        <?php $commentaire = formateStructure($structure); ?>
        <figure>
            <h3><?php echo end($commentaire); ?></h3>
            <pre><?php echo htmlspecialchars(retirerCommentaireStructure($structure)); ?></pre>
        
        <?php foreach ($commentaire as $comment) : ?>
            <?php if ($comment !== end($commentaire)) : ?>
                <p><?php echo $comment; ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($structure !== end($structuresContent)) : ?>
            <hr>
        <?php endif; ?>
        </figure>
    <?php endforeach; ?>
    </section>

    
    <section id="fonctions<?php echo $nbcode ?>">
    <h2>Fonctions</h2>
    <?php foreach ($fonctionsContent as $fonction) : ?>
        <?php $return = retourneFonction($fonction);
              $parametres = parametresFonction($fonction);
              $brief = briefFonction($fonction);
        ?>

        <figure>
            <h3><?php echo $brief; ?></h3>
            <pre><?php echo htmlspecialchars(retirerCommentaireFonction($fonction)); ?></pre>

            <?php if ($parametres !== null) : ?>
                <p>Paramètres :</p>
                <?php foreach ($parametres as $parametre) : ?>
                    <p><?php echo $parametre; ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($return !== null) : ?>
                <p>Retourne :</p>
                <p><?php echo $return; ?></p>
            <?php endif; ?>

        </figure>
        <?php if ($fonction !== end($fonctionsContent)) : ?>
            <hr>
        <?php endif; ?>
    <?php endforeach; ?>
    </section>

    <?php endforeach; ?>
    <footer>
        <p>NEVOT Pierre, LE VERGE Lou, GOGDET Mael, CHAPLAIS Ethan</p>
    </footer>

</body>
</html>

<?php
// Récupère le contenu du buffer et le vide
 $htmlContent = ob_get_clean();
 file_put_contents('doc-tech-' . $version . '.html', $htmlContent);
?>