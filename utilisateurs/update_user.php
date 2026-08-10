<?php

include("../config.php");

$id = $_POST['id'];

$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$ville = $_POST['ville'];

$sql = "UPDATE utilisateur

SET nom_user='$nom',
prenom_user='$prenom',
email_user='$email',
ville_user='$ville'

WHERE id_user=$id";

if($conn->query($sql) === TRUE){

header("Location:index.php");

}

else{

echo "Erreur : ".$conn->error;

}

?>