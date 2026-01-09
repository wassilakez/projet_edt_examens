<?php
$host = "127.0.0.1";
$user = "root";
$pass = ""; // ton mot de passe
$db   = "gestion_examens_db";

$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error){
    die("Connexion échouée: ".$conn->connect_error);
}
$conn->set_charset("utf8mb4");
