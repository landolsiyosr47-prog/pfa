<?php
$serveur = "localhost";
$user = "root";
$password = "";
$base = "pfa";
// création connexion
$conn = new mysqli($serveur, $user, $password, $base);
// vérifier connexion
if ($conn->connect_error) {
die("Erreur connexion : " . $conn->connect_error);
}

?>

