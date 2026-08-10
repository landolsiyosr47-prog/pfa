<?php
include("../config.php");

$dechets = $_POST['dechets'];
$co2 = $_POST['co2'];
$date = $_POST['date'];

$sql = "INSERT INTO IMPACT_ENVIROMMENTAL (dechets_evites_kg_impact, co2_economise_kg_impact, date_impact)
VALUES ('$dechets', '$co2', '$date')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Erreur : " . $conn->error;
}
?>