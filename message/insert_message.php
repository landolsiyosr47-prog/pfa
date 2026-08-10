<?php
include("../config.php");

$uti_id_user = $_POST['uti_id_user'];
$id_user = $_POST['id_user'];
$contenu = $_POST['contenu_message'];

$sql = "INSERT INTO MESSAGE (uti_id_user, id_user, contenu_message) 
        VALUES ('$uti_id_user', '$id_user', '$contenu')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>