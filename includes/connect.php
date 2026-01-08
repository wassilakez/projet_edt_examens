<?php
// Configuration de la base de données
$host = 'localhost';
$db   = 'gestion_examens_db'; // Assure-toi que ce nom est EXACTEMENT le même dans phpMyAdmin
$user = 'root';              
$pass = '';                  
$charset = 'utf8mb4';

// Options pour sécuriser et optimiser PDO (Important pour les 130k inscriptions)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
    PDO::ATTR_EMULATE_PREPARES   => false,                  
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
     // ON CHANGE $conn PAR $pdo ICI
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En cas d'erreur (ex: base de données non créée dans XAMPP)
     die("Erreur de connexion : " . $e->getMessage());
}
?>