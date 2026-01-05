<?php
// db.php
$host = 'localhost';
$dbname = 'gestion_examens_db';  // Ton nom exact de base !
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connexion échouée : " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("❌ Erreur DB : " . $e->getMessage());
}
?>
