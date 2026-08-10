<?php
include("../config.php");

$id = $_POST['id'];
$dechets = $_POST['dechets'];
$co2 = $_POST['co2'];
$date = $_POST['date'];

$sql = "UPDATE IMPACT_ENVIROMMENTAL
SET dechets_evites_kg_impact='$dechets', co2_economise_kg_impact='$co2', date_impact='$date'
WHERE id_impact=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>