<?php

include ('../sql/connect_params.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image-profil'])) {
    $uploadDir = "../images/";
    $fileName = uniqid() . "_" . basename($_FILES["image-profil"]["name"]);
    $uploadFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["image-profil"]["tmp_name"], "../images/" . $fileName)) {
        $userId = $_COOKIE['user'];
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
        $stmt = $dbh->prepare("INSERT INTO pact._image (src_image) VALUES (:image_profil)");
        $stmt->execute([$uploadFile]);

        $stmt = $dbh->prepare("SELECT idImage FROM pact._image WHERE src_image = :image_profil");
        $stmt->execute([$uploadFile]);
        $id_image = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $dbh->prepare("UPDATE pact._compte SET image_compte = :id_image WHERE idC = :idc");
        $stmt->execute([$id_image['idimage'], $userId]);

        echo json_encode(["success" => true, "file" => $uploadFile]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
