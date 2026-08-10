<?php
include("../config.php");

$id = $_GET['id'];

$sql = "DELETE FROM MATERIAU WHERE id_materiau=$id";

if ($conn->query($sql) === TRUE) {
header("Location: index.php");
} else {
echo "Erreur : " . $conn->error;
}
?>