<?php
$host = "localhost";
$db   = "examens_db";
$user = "root";
$pass = ""; // WAMP : mot de passe vide

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}
