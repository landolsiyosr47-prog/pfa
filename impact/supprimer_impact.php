<?php
include("../config.php");

$id = $_GET['id'];

$sql = "DELETE FROM IMPACT_ENVIROMMENTAL WHERE id_impact=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
    exit;
} else {
    echo "Erreur : " . $conn->error;
}
?>