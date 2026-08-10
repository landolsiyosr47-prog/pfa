<?php
include("../config.php");

$id = $_GET['id'];

$sql = "DELETE FROM CATEGORIE WHERE id_categorie=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>