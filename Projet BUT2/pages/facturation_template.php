<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture N°<?= htmlspecialchars($facture_data['numfacture']) ?></title>
     <style>
        <?php include_once("../css/styleGeneral.css");?>
    </style>
    <link rel="icon" type="image/png" href="../logo/logo_reduit_vert.png">
</head>
<body class="BO body-creation-facturation">
    <header class="header-creation-facturation">
        <img src="../logo/logo_rouge.png" alt="PACT" id="logo">
        <div class="titre">
            <h1>Facture N°<?= htmlspecialchars($facture_data['numfacture']) ?></h1>
            <p>Date d’émission : <?= date('d/m/Y', strtotime($facture_data['date_emission'])) ?></p>
        </div>
    </header>

    <div class="E-D">
        <!-- Informations des parties -->
        <div class="Emetteur"> 
            <h2>Émetteur : PACT</h2>
            <p>Plateforme</p>
            <p>1 Place du Chai</p>
            <p>22000 Saint-Brieuc</p>
            <p>Email : PACT-TripEnArvor@proton.me</p>
        </div>
        <div class="Destinataire">
            <h2>Destinataire : <?= htmlspecialchars($facture_data['pro_nom']) ?></h2>
            <p><?= htmlspecialchars($facture_data['pro_adresse']) ?></p>
            <p><?= htmlspecialchars($facture_data['pro_code_postal']) ?> <?= htmlspecialchars($facture_data['pro_ville']) ?></p>
            <p>Téléphone : <?= htmlspecialchars($facture_data['pro_telephone']) ?></p>
            <p>Email : <?= htmlspecialchars($facture_data['pro_email']) ?></p>
        </div>    
    </div>

    <!-- Détails de la facture -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantité</th>
                <th>Prix unitaire HT</th>
                <th>Total HT</th>
                <th>Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Abonnement <?= htmlspecialchars($facture_data['abonnement_type']) ?></td>
                <td><?= htmlspecialchars($facture_data['nbrjours']); if ($facture_data['nbrjours'] == 1) {echo " jour";} else {echo " jours";} ?></td>
                <td><?= number_format($facture_data['abonnement_ht_unitaire'], 2, ',', ' ') ?> €/Jour</td>
                <td><?= number_format($facture_data['prix_offre_ht'], 2, ',', ' ') ?> €</td>
                <td><?= number_format($facture_data['prix_offre_ttc'], 2, ',', ' ') ?> €</td>
            </tr>

            <?php
                        $idO = $offre[0]['ido'];

                        $stmtOption = $dbh->prepare("SELECT * FROM pact._option_offre WHERE date_lancement < now() and idO = :idO");
                        $stmtOption->execute([':idO' => $idO]);
                        $options = $stmtOption->fetchAll(PDO::FETCH_ASSOC);

                        $stmtSemaines = $dbh->prepare("SELECT COUNT(*) as nbSemaines FROM pact._option_offre WHERE idO = :idO AND date_lancement < now()");
                        $stmtSemaines->execute([':idO' => $idO]);
                        $stmtSemaines->fetch(PDO::FETCH_ASSOC);

                        ?>
            <?php if (!empty($options[0])) { ?>
                <tr>
                    <td>Option « <?= $option_titre ?> »</td>
                    <td><?php if (round($facture_data['nbrjours'] /7) > 0) {echo round($facture_data['nbrjours'] /7);} else echo 1; ?> Semaine</td>
                    <td><?= number_format($facture_data['total_options_ht'], 2, ',', ' ') ?> €/Semaine</td>
                    <td><?= number_format($offre_options_ht, 2, ',', ' ') ?> €</td>
                    <td><?= number_format($offre_options_ttc, 2, ',', ' ') ?> €</td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3">Total</td>
                <td><?= number_format($total_ht, 2, ',', ' ') ?> €</td>
                <td><?= number_format($total_ttc, 2, ',', ' ') ?> €</td>
            </tr>
        </tfoot>
    </table>

    <footer class="footer-creation-facturation">
        <p>Merci pour votre confiance.</p>
    </footer>
</body>
</html>
