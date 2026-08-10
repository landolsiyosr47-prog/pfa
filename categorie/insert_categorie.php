<?php
include("../config.php");

$nom_categorie = $_POST['nom_categorie'];

$sql = "INSERT INTO CATEGORIE (nom_categorie) VALUES ('$nom_categorie')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
    exit;
} else {
    echo "Erreur : " . $conn->error;
}
?>