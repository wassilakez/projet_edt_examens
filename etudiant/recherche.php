<?php
session_start();
require_once('../includes/connect.php'); 

$resultats = [];
$erreur = "";
$nom_formation_saisi = "";

try {
    // 1. Récupérer tous les départements pour la liste déroulante
    $stmtDept = $pdo->query("SELECT * FROM departements ORDER BY nom ASC");
    $departements = $stmtDept->fetchAll();

    // 2. Traitement de la recherche
    if (isset($_GET['rechercher'])) {
        $id_dept = $_GET['dept_id'] ?? '';
        $nom_formation_saisi = trim($_GET['nom_formation'] ?? '');

        if (!empty($nom_formation_saisi)) {
            // ÉTAPE A : Vérifier si le NOM de la formation existe (optionnellement dans ce département)
            $checkSql = "SELECT id FROM formations WHERE nom = :nom";
            $params = ['nom' => $nom_formation_saisi];

            if (!empty($id_dept)) {
                $checkSql .= " AND dept_id = :dept_id";
                $params['dept_id'] = $id_dept;
            }

            $stmtCheck = $pdo->prepare($checkSql);
            $stmtCheck->execute($params);
            $formation = $stmtCheck->fetch();

            if (!$formation) {
                $erreur = "Erreur : La formation '" . htmlspecialchars($nom_formation_saisi) . "' n'existe pas ou n'appartient pas à ce département.";
            } else {
                // ÉTAPE B : Récupérer les examens
                $sql = "SELECT e.*, m.nom as matiere, l.nom as salle, u.nom as prof 
                        FROM examens e
                        JOIN modules m ON e.module_id = m.id
                        JOIN lieu_examen l ON e.salle_id = l.id
                        JOIN utilisateurs u ON e.prof_id = u.id
                        WHERE m.formation_id = :form_id
                        ORDER BY e.date_examen ASC, e.heure_debut ASC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['form_id' => $formation['id']]);
                $resultats = $stmt->fetchAll();
                
                if (empty($resultats)) {
                    $erreur = "Aucun examen trouvé pour cette formation.";
                }
            }
        } else {
            $erreur = "Veuillez saisir le nom d'une formation.";
        }
    }
} catch (PDOException $e) {
    die("Erreur technique : " . $e->getMessage());
}

include 'recherche.html';
?>