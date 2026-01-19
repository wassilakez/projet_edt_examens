<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once('../includes/connect.php'); 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header("Location: ../login.php?role=professeur");
    exit();
}

$username = $_SESSION['username']; // Utilise le matricule (ex: P001)

try {
    // 1. On récupère d'abord le nom du prof pour l'affichage
    $sql_prof = "SELECT nom, prenom FROM utilisateurs WHERE username = :uname";
    $stmt_prof = $pdo->prepare($sql_prof);
    $stmt_prof->execute(['uname' => $username]);
    $prof_data = $stmt_prof->fetch();
    
    // Nom complet pour l'affichage
    $prof_nom = $prof_data ? $prof_data['prenom'] . " " . $prof_data['nom'] : $username;

    // 2. Requête pour le planning (uniquement examens validés par le doyen)
    $sql = "SELECT e.date_examen, e.heure_debut, e.duree_minutes, m.nom as matiere, l.nom as salle, l.type
            FROM examens e
            JOIN modules m ON e.module_id = m.id
            JOIN lieu_examen l ON e.salle_id = l.id
            JOIN utilisateurs u ON e.prof_id = u.id
            WHERE u.username = :uname
              AND e.statut = 'VALIDE'   -- 🔹 uniquement validé par le doyen
            ORDER BY e.date_examen ASC, e.heure_debut ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uname' => $username]);
    $surveillances = $stmt->fetchAll();

    // 3. Si pas encore validé, on peut afficher un message
    if (empty($surveillances)) {
        $message = "🕒 L’emploi du temps n’a pas encore été validé par le doyen.";
    }

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

include 'planning.html';
?>
