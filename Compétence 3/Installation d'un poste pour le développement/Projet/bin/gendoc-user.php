<?php

// Récupérer la version, le projet et le client dans le fichier config
function obtenirVersion($config) {
    $debut = strpos($config, 'VERSION=');
    $fin = strpos($config, "\n", $debut);
    $version = substr($config, $debut + strlen('VERSION='), $fin - $debut - strlen('VERSION='));
    return trim($version);
}
function obtenirProjet($config) {
    $debut = strpos($config, 'PRODUIT=');
    $fin = strpos($config, "\n", $debut);
    $projet = substr($config, $debut + strlen('PRODUIT='), $fin - $debut - strlen('PRODUIT='));
    return trim($projet);
}
function obtenirClient($config) {
    $debut = strpos($config, 'CLIENT=');
    $fin = strpos($config, "\n", $debut);
    $client = substr($config, $debut + strlen('CLIENT='), $fin - $debut - strlen('CLIENT='));
    return trim($client);
}

// Appelle la fonction pour obtenir la version et initialise les variables
$config = file_get_contents('config');
$version = obtenirVersion($config);
$projet = obtenirProjet($config);
$client = obtenirClient($config);

// Récupérer toute les fichiers Markdonw et va générer un fichier configuration avec la version qui augmente à chaque fois, nomme les variables en francais
$markdown = glob('*.md');
// Sélectionne le premier fichier Markdown
$markdown = file_get_contents($markdown[0]);
$html = convertMarkdownToHTML($markdown);

function convertMarkdownToHTML($markdown)
{
    // Traite les en-têtes Markdown si un # est présent en début de ligne
    $markdown = preg_replace('/^#\s?([^#].+)$/m', '<h1>$1</h1>', $markdown);
    $markdown = preg_replace('/^##\s?([^#].+)$/m', '<h2>$1</h2>', $markdown);
    $markdown = preg_replace('/^###\s?([^#].+)$/m', '<h3>$1</h3>', $markdown);
    $markdown = preg_replace('/^####\s?([^#].+)$/m', '<h4>$1</h4>', $markdown);
    $markdown = preg_replace('/^#####\s?([^#].+)$/m', '<h5>$1</h5>', $markdown);

    // Traite italique et gras Markdown
    $markdown = preg_replace('/\*\*(.+)\*\*/', '<strong>$1</strong>', $markdown);
    $markdown = preg_replace('/_(.+)_/', '<em>$1</em>', $markdown);

    // Traite les listes Markdown
    for ($i = 0; $i < 500; $i++) {    
        $markdown = preg_replace('/^[*-]\s*(.+)$/m', '<li>$1</li>', $markdown);
    }

    // Traite les listes ordonnées Markdown
    for ($i = 0; $i < 500; $i++) {    
        $markdown = preg_replace('/^(\d+)\.\s*(.+)$/m', '<li>$2</li>', $markdown);
    }

    // Traite les images Markdown
    $markdown = preg_replace('/!\[(.+)\]\s?\((.+)\)/', '<img src="$2" alt="$1" />', $markdown);

    // Traite les liens Markdown 
    $markdown = preg_replace('/\[(.+)\]\s?\((.+)\)/', '<a href="$2">$1</a>', $markdown);
    
    // Traite le texte barré Markdown
    $markdown = preg_replace('/~~(.+)~~/', '<del>$1</del>', $markdown);

    // Traite les blocs de code code Markdown
    $markdown = preg_replace_callback('/```(.*?)```/s', function ($matches) {
        return '<pre>' . trim($matches[1]) . '</pre>';
    }, $markdown);
    
    // Traite les citations Markdown
    $markdown = preg_replace('/\n>(.+)/', '<blockquote>$1</blockquote>', $markdown);

    // Traite les lignes horizontales Markdown
    $markdown = preg_replace('/\n---/', '<hr/>', $markdown);

    // Traite les retours à la ligne Markdown
    $markdown = preg_replace('/\n/', '<br/>', $markdown);

    
    // Balise ul pour afficher la liste
    $markdown = '<ul>' . $markdown . '</ul>';  

    // Tout le texte qui n'a pas été traité est considéré comme un paragraphe
    $markdown = preg_replace('/(.+)/', '<p>$1</p>', $markdown);

    return $markdown;
}

ob_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation Utilisateur - Version <?php echo $version ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Documentation Utilisateur - Version <?php echo $version ?></h1>
        <h2><?php echo $projet ?></h2>
        <h3><?php echo $client ?></h3>
    </header>
    <main>
        <?php echo $html ?>
    </main>
</body>
</html>

<?php
// Génère le fichier HTML
$html = ob_get_clean();
file_put_contents('doc-user-' . $version . '.html', $html);
?>