<?php
include("../config.php");

$id = $_POST['id'];
$nom = $_POST['nom'];
$description = $_POST['description'];
$etat = $_POST['etat'];
$quantite = $_POST['quantite'];
$dimensions = $_POST['dimensions'];
$mode = $_POST['mode'];
$disponibilite = $_POST['disponibilite'];
$prix = $_POST['prix'];

$sql = "UPDATE MATERIAU SET
nom_materiau='$nom',
description_materiau='$description',
etat_materiau='$etat',
quantite_materiau='$quantite',
dimensions_materiau='$dimensions',
mode_echange_materiau='$mode',
disponibilite_materiau='$disponibilite',
prix_materiau='$prix'
WHERE id_materiau=$id";

if ($conn->query($sql) === TRUE) {
header("Location: index.php");
} else {
echo "Erreur : " . $conn->error;
}
?>