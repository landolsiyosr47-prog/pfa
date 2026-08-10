<?php
include("../config.php");

$id = $_GET['id'];

$sql = "DELETE FROM SIGNALEMENT WHERE id_signalement=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>