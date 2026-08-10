<?php
include("../config.php");

$id = $_GET['id'];

$sql = "DELETE FROM reservation WHERE id_reservation=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>