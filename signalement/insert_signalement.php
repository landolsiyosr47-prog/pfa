<?php
include("../config.php");

$id_materiau = $_POST['id_materiau'];
$id_user = $_POST['id_user'];
$motif = $_POST['motif_signalement'];
$date = $_POST['date_signalement'];

$sql = "INSERT INTO SIGNALEMENT (id_materiau, id_user, motif_signalement, date_signalement)
VALUES ('$id_materiau', '$id_user', '$motif', '$date')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>