<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once('../includes/connect.php');

// Vérifie si l'utilisateur est un chef de département
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'chef_departement') {
    header("Location: ../login.php?role=chef_departement");
    exit();
}

$username = $_SESSION['username'];

// Exemple de données fictives pour les formations (à remplacer par une requête SQL réelle)
$formations = [
    ['nom' => 'Informatique', 'etudiants' => 800, 'taux_reussite' => '85%'],
    ['nom' => 'Mathématiques', 'etudiants' => 600, 'taux_reussite' => '90%'],
    ['nom' => 'Physique', 'etudiants' => 500, 'taux_reussite' => '78%']
];

// Inclure le fichier HTML
include 'chef.html';
?>
