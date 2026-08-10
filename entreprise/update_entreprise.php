<?php
include("../config.php");

$id = $_POST['id'];
$id_user = $_POST['id_user'];
$nom = $_POST['nom'];
$secteur = $_POST['secteur'];
$matricule = $_POST['matricule'];

$sql = "UPDATE ENTREPRISE SET 
id_user='$id_user',
nom_entrep='$nom',
secteur_activite_entrep='$secteur',
matricule_fiscale_entrep='$matricule'
WHERE id_entrep=$id";

if ($conn->query($sql) === TRUE) {
header("Location: index.php");
} else {
echo "Erreur : " . $conn->error;
}
?>