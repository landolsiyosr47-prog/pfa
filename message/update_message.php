<?php
include("../config.php");

$id = $_POST['id_message'];
$uti_id_user = $_POST['uti_id_user'];
$id_user = $_POST['id_user'];
$contenu = $_POST['contenu_message'];

$sql = "UPDATE MESSAGE 
        SET uti_id_user='$uti_id_user', id_user='$id_user', contenu_message='$contenu' 
        WHERE id_message=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>