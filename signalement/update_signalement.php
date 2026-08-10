<?php
include("../config.php");

$id = $_POST['id'];
$id_materiau = $_POST['id_materiau'];
$id_user = $_POST['id_user'];
$motif = $_POST['motif_signalement'];
$date = $_POST['date_signalement'];

$sql = "UPDATE SIGNALEMENT
SET id_materiau='$id_materiau', id_user='$id_user', motif_signalement='$motif', date_signalement='$date'
WHERE id_signalement=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>