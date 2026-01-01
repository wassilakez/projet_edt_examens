<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifiez si l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: login.php?role=administrateur");
    exit();
}

// Initialisation des messages
$generateResult = '';
$conflictResult = '';
$optimizeResult = '';

// Simulation de données pour la détection des conflits
$conflits = [
    'Informatique' => 3,
    'Mathématiques' => 2,
    'Physique' => 1
];

$ressourcesOptimisees = true;

// Gérer les actions des boutons
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['generateEDT'])) {
        $generateResult = "🔄 Emploi du temps généré avec succès!";
    }
    if (isset($_POST['detectConflicts'])) {
        $conflictMessages = [];
        foreach ($conflits as $formation => $count) {
            $conflictMessages[] = "$formation: $count conflit(s)";
        }
        $conflictResult = implode('<br>', $conflictMessages);
    }
    if (isset($_POST['optimizeResources'])) {
        $optimizeResult = $ressourcesOptimisees 
            ? "⚙️ Ressources optimisées avec succès!" 
            : "❌ Erreur lors de l'optimisation des ressources.";
    }
}

// Inclure le fichier HTML
include 'admin.html';
?>
