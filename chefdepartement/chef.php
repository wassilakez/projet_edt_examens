<?php
session_start();
require_once('../includes/connect.php');

// Sécurité : Vérification du rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'chef_dep') {
    header("Location: ../index.php");
    exit();
}

$dept_id = $_SESSION['dept_id'];
$nom_chef = $_SESSION['nom'];

try {
    // 1. RÉCUPÉRER LE PLANNING (EDT) DU DÉPARTEMENT
    // On récupère date_examen et heure_debut séparément pour éviter les erreurs d'affichage
    $query_planning = "SELECT e.date_examen, e.heure_debut, m.nom as module_nom, f.nom as formation_nom, 
                              l.nom as salle_nom, p.nom as prof_nom
                       FROM examens e
                       JOIN modules m ON e.module_id = m.id
                       JOIN formations f ON m.formation_id = f.id
                       JOIN lieu_examen l ON e.salle_id = l.id
                       JOIN utilisateurs p ON e.prof_id = p.id
                       WHERE f.dept_id = ?
                       ORDER BY e.date_examen, e.heure_debut ASC";
    $stmt_p = $pdo->prepare($query_planning);
    $stmt_p->execute([$dept_id]);
    $exams = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

    // 2. RÉCUPÉRER LES STATISTIQUES ET DÉTECTER LA SURCHARGE (MAX 20 ÉTUDIANTS)
    // Cette requête compte les étudiants par formation pour vérifier la limite de capacité
    $query_stats = "SELECT f.nom, f.nb_modules, COUNT(u.id) as nb_etudiants 
                    FROM formations f 
                    LEFT JOIN utilisateurs u ON f.id = u.formation_id 
                    WHERE f.dept_id = ? 
                    GROUP BY f.id";
    $stmt_s = $pdo->prepare($query_stats);
    $stmt_s->execute([$dept_id]);
    $stats = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

    // 3. CONFLIT ÉTUDIANT : PLUS D'UN EXAMEN PAR JOUR
    // On vérifie si une formation a plusieurs examens à la même date
    $query_conflit_date = "SELECT e.date_examen, f.nom as formation_nom, COUNT(*) as nb_examens_jour
                           FROM examens e
                           JOIN modules m ON e.module_id = m.id
                           JOIN formations f ON m.formation_id = f.id
                           WHERE f.dept_id = ?
                           GROUP BY e.date_examen, f.id
                           HAVING nb_examens_jour > 1";
    $stmt_cd = $pdo->prepare($query_conflit_date);
    $stmt_cd->execute([$dept_id]);
    $conflits_dates = $stmt_cd->fetchAll(PDO::FETCH_ASSOC);

    // 4. CONFLIT ENSEIGNANT : PLUS DE 3 SURVEILLANCES PAR JOUR
    // On vérifie la charge des professeurs du département pour respecter la limite de 3
    $query_conflit_prof = "SELECT e.date_examen, p.nom as prof_nom, COUNT(*) as nb_surveillances
                           FROM examens e
                           JOIN utilisateurs p ON e.prof_id = p.id
                           WHERE p.dept_id = ?
                           GROUP BY e.date_examen, p.id
                           HAVING nb_surveillances > 3";
    $stmt_cp = $pdo->prepare($query_conflit_prof);
    $stmt_cp->execute([$dept_id]);
    $conflits_profs = $stmt_cp->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}

include 'chef.html';
?>