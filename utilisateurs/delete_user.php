<?php

include("../config.php");

$id = $_GET['id'];

$sql = "DELETE FROM utilisateur WHERE id_user=$id";

if($conn->query($sql) === TRUE){

header("Location:index.php");

}

else{

echo "Erreur : ".$conn->error;

}

?>