<?php
include("../config.php");

$id = $_POST['id'];
$nom_categorie = $_POST['nom_categorie'];

$sql = "UPDATE CATEGORIE SET nom_categorie='$nom_categorie' WHERE id_categorie=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>