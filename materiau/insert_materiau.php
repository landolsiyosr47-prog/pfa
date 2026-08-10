<?php
include("../config.php");

$id_user = $_POST['id_user'];
$id_categorie = $_POST['id_categorie'];
$nom = $_POST['nom'];
$description = $_POST['description'];
$etat = $_POST['etat'];
$quantite = $_POST['quantite'];
$dimensions = $_POST['dimensions'];
$mode = $_POST['mode'];
$disponibilite = $_POST['disponibilite'];
$date_pub = $_POST['date_pub'];
$prix = $_POST['prix'];

$sql = "INSERT INTO MATERIAU (
id_user, id_categorie, nom_materiau, description_materiau,
etat_materiau, quantite_materiau, dimensions_materiau,
mode_echange_materiau, disponibilite_materiau,
date_publication_materiau, prix_materiau
) VALUES (
'$id_user', '$id_categorie', '$nom', '$description',
'$etat', '$quantite', '$dimensions',
'$mode', '$disponibilite',
'$date_pub', '$prix'
)";

if ($conn->query($sql) === TRUE) {
header("Location: index.php");
} else {
echo "Erreur : " . $conn->error;
}
?>