<?php
require '../vendor/autoload.php'; // Inclure l'autoloader de Composer
use Dompdf\Dompdf;
use Dompdf\Options;

// Connexion à la base de données
include('../sql/connect_params.php'); // Ce fichier contient vos paramètres de connexion

// Vérifier si les paramètres GET sont présents
if (isset($_GET['numFacture'])) {
    try {
        // Connexion à la base de données
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $numfacture = filter_var($_GET['numFacture'], FILTER_VALIDATE_INT);
        $userID = filter_var($_COOKIE['user'], FILTER_VALIDATE_INT);

        if (!$numfacture || !$userID) {
            die("Paramètres manquants ou invalides.");
        }

        // Préparer les données nécessaires pour `facturation_template.php`
        // (À personnaliser en fonction des données nécessaires dans votre template)
        $stmt = $dbh->prepare("
            SELECT 
        f.*,o.*,
  f.nbrjours, 
  f.numfacture,
  f.date_facture AS date_emission,
  c.nom AS pro_nom,
  a.numero_voie AS pro_num_adrese,
  a.voie AS pro_adresse,
  a.code_postal AS pro_code_postal,
  a.ville AS pro_ville,
  c.telephone AS pro_telephone,
  c.email AS pro_email,
  t.nom_type AS abonnement_type,
  o.option AS options,
  o.titre AS titre_offre,
  t.cout_HT AS abonnement_ht_unitaire,
  t.cout_TTC AS abonnement_ttc_unitaire,
  op.cout_ht AS total_options_ht,
  op.cout_ttc AS total_option
FROM pact.facturation f
  INNER JOIN pact._compte c ON f.idC = c.idC
  INNER JOIN pact._adresse a ON c.idAdresse = a.idAdresse
  LEFT JOIN pact.option_offre op ON f.idO = op.idO
  INNER JOIN pact.offre o ON f.idO = o.idO
  INNER JOIN pact._type_offre t ON o.type = t.idT
  WHERE f.numfacture = :numfacture AND f.idC =  :userId;
        ");
        $stmt->bindParam(':numfacture', $numfacture, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $facture_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$facture_data) {
            print_r($facture_data);
            throw new Exception("Aucune donnée trouvée pour l'offre ID: $ido");
        }

        function offre($numFacture, $dbh){
            $stmt = $dbh->prepare("SELECT o.*, f.* FROM pact.offre o INNER JOIN pact.facturation f ON f.idO = o.idO WHERE f.numFacture = :numFacture");
            $stmt->execute([':numFacture' => $numFacture]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $offre = offre($numfacture, $dbh);
        

        // Calculs
        $jours_publication = $facture_data['nbrjours'];
        $abonnement_ht = $jours_publication * $facture_data['abonnement_ht_unitaire'];
        $abonnement_ttc = $jours_publication * $facture_data['abonnement_ttc_unitaire'];
        $total_ht = round($offre[0]['prix_offre_ht'] + $offre[0]['prix_option_ht'],2);
        $total_ttc = round($offre[0]['prix_offre_ttc'] + $offre[0]['prix_option_ttc'],2);
        $offre_options_ht = $offre[0]['prix_option_ht'];
        $offre_options_ttc = $offre[0]['prix_option_ttc'];
        $option_titre = $offre[0]['option'];

        
        // Initialisez $bdh avant d'inclure le template
include('../sql/connect_params.php');
$bdh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
$bdh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Transmettez $facture_data et $bdh au template
ob_start();
include('facturation_template.php');
$html = ob_get_clean();
$ido = $offre[0]['ido'] ?? null;
if (!$ido) {
    die("Erreur : Identifiant de l'offre introuvable.");
}

        // Configurer Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Envoyer le fichier PDF au navigateur pour téléchargement
        $dompdf->stream("Facture_Offre_$ido.pdf", ["Attachment" => true]);
    } catch (Exception $e) {
        die("Erreur : " . $e->getMessage());
    }
} else {
    die("Paramètre 'ido' manquant.");
}
?>

