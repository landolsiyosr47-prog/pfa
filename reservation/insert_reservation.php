<?php
include("../config.php");

$id_materiau = $_POST['id_materiau'];
$id_user = $_POST['id_user'];
$date_reservation = $_POST['date_reservation'];
$statut_reservation = $_POST['statut_reservation'];

$sql = "INSERT INTO reservation (id_materiau, id_user, date_reservation, statut_reservation)
        VALUES ('$id_materiau', '$id_user', '$date_reservation', '$statut_reservation')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>