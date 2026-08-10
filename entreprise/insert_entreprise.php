<?php
include("../config.php");

$id_user = $_POST['id_user'];
$nom = $_POST['nom'];
$secteur = $_POST['secteur'];
$matricule = $_POST['matricule'];

$sql = "INSERT INTO ENTREPRISE 
(id_user, nom_entrep, secteur_activite_entrep, matricule_fiscale_entrep)
VALUES ('$id_user', '$nom', '$secteur', '$matricule')";

if ($conn->query($sql) === TRUE) {
header("Location: index.php");
} else {
echo "Erreur : " . $conn->error;
}
?>