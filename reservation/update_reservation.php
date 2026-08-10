<?php
include("../config.php");

$id = $_POST['id'];
$id_materiau = $_POST['id_materiau'];
$id_user = $_POST['id_user'];
$date_reservation = $_POST['date_reservation'];
$statut_reservation = $_POST['statut_reservation'];

$sql = "UPDATE reservation
        SET id_materiau='$id_materiau', id_user='$id_user', date_reservation='$date_reservation', statut_reservation='$statut_reservation'
        WHERE id_reservation=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>