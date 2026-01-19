<?php
session_start();
require_once('../includes/connect.php'); 

// Vérification de la session étudiant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../login.php?role=etudiant");
    exit();
}

$username = $_SESSION['username'];

try {
    // 1. Récupérer les infos de l'étudiant (nom, prenom, promo, formation_id)
    $stmt = $pdo->prepare("
        SELECT u.nom, u.prenom, u.promo, f.nom as formation, u.formation_id
        FROM utilisateurs u
        JOIN formations f ON u.formation_id = f.id
        WHERE u.username = :uname
    ");
    $stmt->execute(['uname' => $username]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        die("Erreur : étudiant introuvable.");
    }

    // 2. Récupérer les examens de sa formation validés par le doyen
    $sql = "
        SELECT e.date_examen, e.heure_debut, e.duree_minutes, m.nom as matiere, l.nom as salle, u.nom as prof
        FROM examens e
        JOIN modules m ON e.module_id = m.id
        JOIN lieu_examen l ON e.salle_id = l.id
        JOIN utilisateurs u ON e.prof_id = u.id
        WHERE m.formation_id = :formation_id
          AND e.statut = 'VALIDE'
        ORDER BY e.date_examen ASC, e.heure_debut ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['formation_id' => $etudiant['formation_id']]);
    $resultats = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur technique : " . $e->getMessage());
}

// Inclure le HTML pour l'affichage
include 'recherche.html';
?>
