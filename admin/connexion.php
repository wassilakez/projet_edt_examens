<?php
// admin/connexion.php
// Configuration de la connexion à la base de données

// ============================================
// CONFIGURATION - ADAPTEZ CES VALEURS
// ============================================

// Paramètres de connexion MySQL
$host = 'localhost';               // Généralement 'localhost'
$dbname = 'gestion_examens_db';    // Votre base de données
$username = 'root';                // Votre utilisateur MySQL
$password = '';                    // Votre mot de passe MySQL

// ============================================
// CONNEXION À LA BASE DE DONNÉES
// ============================================

// Désactiver l'affichage des erreurs en production
error_reporting(0);

// Variable globale pour la connexion
$pdo = null;

try {
    // Créer la connexion PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Lever des exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Tableaux associatifs
            PDO::ATTR_EMULATE_PREPARES => false,              // Préparations réelles
            PDO::ATTR_PERSISTENT => false                     // Non persistante
        ]
    );
    
    // Vérifier la connexion avec une requête simple
    $pdo->query("SELECT 1");
    
    // Définir le fuseau horaire si nécessaire
    $pdo->query("SET time_zone = '+00:00'");
    
} catch (PDOException $e) {
    // Journaliser l'erreur
    error_log("[" . date('Y-m-d H:i:s') . "] ERREUR DB: " . $e->getMessage());
    
    // Message d'erreur formaté pour votre interface
    if (!headers_sent() && php_sapi_name() !== 'cli') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de connexion à la base de données',
            'error_code' => 'DB_CONNECTION_FAILED',
            'debug' => (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'localhost') ? $e->getMessage() : null
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        die("Erreur de connexion à la base de données");
    }
}

// ============================================
// FONCTIONS UTILES
// ============================================

/**
 * Exécute une requête SQL sécurisée
 */
function db_query($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage() . " - Requête: " . $sql);
        throw $e;
    }
}

/**
 * Récupère une seule ligne
 */
function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}

/**
 * Récupère toutes les lignes
 */
function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Exécute une requête INSERT/UPDATE/DELETE
 */
function db_execute($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->rowCount();
}

/**
 * Récupère la dernière ID insérée
 */
function db_last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

// ============================================
// CONSTANTE POUR VÉRIFIER LA CONNEXION
// ============================================
if (!defined('DB_CONNECTED')) {
    define('DB_CONNECTED', true);
}

// ============================================
// TEST DE CONNEXION (optionnel - à commenter en production)
// ============================================
/*
if (isset($_GET['test_db']) && $_SERVER['SERVER_NAME'] == 'localhost') {
    echo "<h2>Test de connexion DB réussi</h2>";
    echo "Base: $dbname<br>";
    echo "Hôte: $host<br>";
    echo "Utilisateur: $username<br>";
    
    // Test des tables
    $tables = db_fetch_all("SHOW TABLES");
    echo "<h3>Tables disponibles:</h3>";
    foreach ($tables as $table) {
        echo "- " . current($table) . "<br>";
    }
    exit;
}
*/
?>
