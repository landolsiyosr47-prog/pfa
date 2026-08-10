<?php

include("config.php");

$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$telephone = $_POST['telephone'];
$motdepasse = $_POST['motdepasse'];
$type = $_POST['type'];
$adresse = $_POST['adresse'];
$ville = $_POST['ville'];

$sql = "INSERT INTO utilisateur
(nom_user, prenom_user, email_user, telephone_user, mot_de_passe_user, type_user, adresse_user, ville_user, date_inscription_user)

VALUES
('$nom','$prenom','$email','$telephone','$motdepasse','$type','$adresse','$ville', NOW())";

if($conn->query($sql) === TRUE){

header("Location:index.php");

}

else{

echo "Erreur : ".$conn->error;

}

?>