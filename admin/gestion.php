<?php
// gestion.php - VERSION COMPLÈTE FINALE
include 'db.php';

$generateResult = '';
$conflictResult = '';
$optimizeResult = '';
$activeSection = 'generation';

// === 1. MODE SIMULATION ===
if (isset($_POST['simulateEDT'])) {
    $activeSection = 'generation';

    $sql = "SELECT m.id, m.nom AS module_nom, f.nom AS formation, f.id AS formation_id, d.id AS dept_id
            FROM modules m
            JOIN formations f ON m.formation_id = f.id
            JOIN departements d ON f.dept_id = d.id
            LEFT JOIN examens e ON m.id = e.module_id
            WHERE e.id IS NULL
            LIMIT 10";

    $result = $conn->query($sql);
    $propositions = [];
    $simulated = 0;

    if ($result && $result->num_rows > 0) {
        while ($module = $result->fetch_assoc()) {
            // Professeur du même département
            $prof_sql = "SELECT id, CONCAT(COALESCE(prenom, ''), ' ', nom) AS prof_name
                         FROM utilisateurs
                         WHERE dept_id = ? AND role = 'professeur'
                         ORDER BY RAND() LIMIT 1";
            $stmt_prof = $conn->prepare($prof_sql);
            $stmt_prof->bind_param("i", $module['dept_id']);
            $stmt_prof->execute();
            $prof_res = $stmt_prof->get_result();
            $prof = $prof_res->num_rows > 0 ? $prof_res->fetch_assoc() : ['id' => 1, 'prof_name' => 'Professeur par défaut'];
            $prof_id = $prof['id'];
            $prof_name = trim($prof['prof_name']);

            // Dates possibles
            $dates_possibles = [];
            for ($i = 7; $i <= 30; $i += 3) {
                $dates_possibles[] = date('Y-m-d', strtotime("+$i days"));
            }

            $times = ['09:00:00', '11:00:00', '14:00:00'];
            $duree = 90;
            $placed = false;

            foreach ($dates_possibles as $date) {
                if ($placed) break;

                // Contrainte formation : même jour interdit
                $check_formation = "SELECT 1 FROM examens e
                                    JOIN modules m ON e.module_id = m.id
                                    WHERE m.formation_id = ? AND e.date_examen = ?";
                $stmt_form = $conn->prepare($check_formation);
                $stmt_form->bind_param("is", $module['formation_id'], $date);
                $stmt_form->execute();
                if ($stmt_form->get_result()->num_rows > 0) continue;

                $room_result = $conn->query("SELECT * FROM lieu_examen WHERE capacite >= 20 ORDER BY RAND() LIMIT 3");

                while ($room = $room_result->fetch_assoc()) {
                    foreach ($times as $heure) {
                        $end_time = date('H:i:s', strtotime($heure . " + $duree minutes"));

                        $check_salle = "SELECT 1 FROM examens
                                        WHERE salle_id = ? AND date_examen = ?
                                        AND heure_debut < ? AND ADDTIME(heure_debut, SEC_TO_TIME(duree_minutes*60)) > ?";
                        $stmt_salle = $conn->prepare($check_salle);
                        $stmt_salle->bind_param("isss", $room['id'], $date, $end_time, $heure);
                        $stmt_salle->execute();

                        $check_prof = "SELECT 1 FROM examens
                                       WHERE prof_id = ? AND date_examen = ?
                                       AND heure_debut < ? AND ADDTIME(heure_debut, SEC_TO_TIME(duree_minutes*60)) > ?";
                        $stmt_prof_check = $conn->prepare($check_prof);
                        $stmt_prof_check->bind_param("isss", $prof_id, $date, $end_time, $heure);
                        $stmt_prof_check->execute();

                        if ($stmt_salle->get_result()->num_rows == 0 && $stmt_prof_check->get_result()->num_rows == 0) {
                            $propositions[] = [
                                'module' => $module['module_nom'],
                                'formation' => $module['formation'],
                                'prof' => $prof_name,
                                'salle' => $room['nom'],
                                'date' => $date,
                                'heure' => $heure,
                                'duree' => $duree
                            ];
                            $simulated++;
                            $placed = true;
                            break 3;
                        }
                    }
                }
            }

            if (!$placed) {
                $propositions[] = ['module' => $module['module_nom'], 'warning' => 'Aucun créneau disponible'];
            }
        }

        $generateResult = "<div style='background:#e7f3ff;padding:20px;border-radius:8px;border:1px solid #b3d9ff;'>
            <h4 style='color:#004080;margin:0 0 15px 0;'>Prévisualisation : $simulated examen(s) proposé(s)</h4>
            <table style='width:100%;border-collapse:collapse;margin-top:10px;'>
                <tr style='background:#004080;color:white;'>
                    <th style='padding:10px;'>Module</th>
                    <th style='padding:10px;'>Formation</th>
                    <th style='padding:10px;'>Professeur</th>
                    <th style='padding:10px;'>Salle</th>
                    <th style='padding:10px;'>Date</th>
                    <th style='padding:10px;'>Heure</th>
                    <th style='padding:10px;'>Durée</th>
                </tr>";

        foreach ($propositions as $p) {
            if (isset($p['warning'])) {
                $generateResult .= "<tr style='background:#fff3cd;'><td colspan='7' style='padding:12px;'><strong>{$p['module']}</strong> → {$p['warning']}</td></tr>";
            } else {
                $generateResult .= "<tr>
                    <td style='padding:10px;'><strong>{$p['module']}</strong></td>
                    <td style='padding:10px;'>{$p['formation']}</td>
                    <td style='padding:10px;'>{$p['prof']}</td>
                    <td style='padding:10px;'>{$p['salle']}</td>
                    <td style='padding:10px;text-align:center;'>{$p['date']}</td>
                    <td style='padding:10px;text-align:center;'>{$p['heure']}</td>
                    <td style='padding:10px;text-align:center;'>{$p['duree']} min</td>
                </tr>";
            }
        }

        $generateResult .= "</table>
            <p style='margin-top:20px;font-weight:bold;color:#004080;'>
                Cette simulation n'a rien modifié en base.<br>
                Si OK → cliquez sur <strong>« Appliquer définitivement »</strong>.
            </p>
        </div>";
    } else {
        $generateResult = "<div style='background:#fff3cd;padding:15px;border-radius:8px;'>Aucun module sans examen.</div>";
    }
}

// === 2. APPLICATION DÉFINITIVE ===
if (isset($_POST['generateEDT'])) {
    $activeSection = 'generation';

    $sql = "SELECT m.id, m.nom AS module_nom, f.id AS formation_id, d.id AS dept_id
            FROM modules m
            JOIN formations f ON m.formation_id = f.id
            JOIN departements d ON f.dept_id = d.id
            LEFT JOIN examens e ON m.id = e.module_id
            WHERE e.id IS NULL
            LIMIT 10";

    $result = $conn->query($sql);
    $generated = 0;

    if ($result && $result->num_rows > 0) {
        while ($module = $result->fetch_assoc()) {
            $prof_sql = "SELECT id FROM utilisateurs WHERE dept_id = ? AND role = 'professeur' ORDER BY RAND() LIMIT 1";
            $stmt_prof = $conn->prepare($prof_sql);
            $stmt_prof->bind_param("i", $module['dept_id']);
            $stmt_prof->execute();
            $prof_res = $stmt_prof->get_result();
            $prof_id = $prof_res->num_rows > 0 ? $prof_res->fetch_assoc()['id'] : 1;

            $dates_possibles = [];
            for ($i = 7; $i <= 30; $i += 3) {
                $dates_possibles[] = date('Y-m-d', strtotime("+$i days"));
            }

            $times = ['09:00:00', '11:00:00', '14:00:00'];
            $duree = 90;
            $placed = false;

            foreach ($dates_possibles as $date) {
                if ($placed) break;

                $check_formation = "SELECT 1 FROM examens e JOIN modules m ON e.module_id = m.id WHERE m.formation_id = ? AND e.date_examen = ?";
                $stmt_form = $conn->prepare($check_formation);
                $stmt_form->bind_param("is", $module['formation_id'], $date);
                $stmt_form->execute();
                if ($stmt_form->get_result()->num_rows > 0) continue;

                $room_result = $conn->query("SELECT * FROM lieu_examen WHERE capacite >= 20 ORDER BY RAND() LIMIT 3");

                while ($room = $room_result->fetch_assoc()) {
                    foreach ($times as $heure) {
                        $end_time = date('H:i:s', strtotime($heure . " + $duree minutes"));

                        $check_salle = "SELECT 1 FROM examens WHERE salle_id = ? AND date_examen = ? AND heure_debut < ? AND ADDTIME(heure_debut, SEC_TO_TIME(duree_minutes*60)) > ?";
                        $stmt_salle = $conn->prepare($check_salle);
                        $stmt_salle->bind_param("isss", $room['id'], $date, $end_time, $heure);
                        $stmt_salle->execute();

                        $check_prof = "SELECT 1 FROM examens WHERE prof_id = ? AND date_examen = ? AND heure_debut < ? AND ADDTIME(heure_debut, SEC_TO_TIME(duree_minutes*60)) > ?";
                        $stmt_prof_check = $conn->prepare($check_prof);
                        $stmt_prof_check->bind_param("isss", $prof_id, $date, $end_time, $heure);
                        $stmt_prof_check->execute();

                        if ($stmt_salle->get_result()->num_rows == 0 && $stmt_prof_check->get_result()->num_rows == 0) {
                            $insert_sql = "INSERT INTO examens (module_id, prof_id, salle_id, date_examen, heure_debut, duree_minutes)
                                           VALUES (?, ?, ?, ?, ?, ?)";
                            $insert_stmt = $conn->prepare($insert_sql);
                            $insert_stmt->bind_param("iiisii", $module['id'], $prof_id, $room['id'], $date, $heure, $duree);
                            $insert_stmt->execute();
                            $generated++;
                            $placed = true;
                            break 3;
                        }
                    }
                }
            }
        }

        $generateResult = "<div style='background:#d4edda;color:#155724;padding:20px;border-radius:8px;'>
            <strong>$generated nouveaux examens planifiés définitivement !</strong><br>
            <em>Contraintes respectées (salle, prof, formation).</em>
        </div>";
    } else {
        $generateResult = "<div style='background:#fff3cd;padding:15px;border-radius:8px;'>Aucun module à planifier.</div>";
    }
}

// === 3. DÉTECTION CONFLITS ===
if (isset($_POST['detectConflicts'])) {
    $activeSection = 'conflits';
    $conflicts = [];

    $sql_salle = "SELECT m1.nom AS module1, m2.nom AS module2, le.nom AS salle, e1.date_examen, e1.heure_debut AS h1, e2.heure_debut AS h2
                  FROM examens e1
                  JOIN examens e2 ON e1.id < e2.id
                  JOIN modules m1 ON e1.module_id = m1.id
                  JOIN modules m2 ON e2.module_id = m2.id
                  JOIN lieu_examen le ON e1.salle_id = le.id
                  WHERE e1.salle_id = e2.salle_id AND e1.date_examen = e2.date_examen
                  AND (e1.heure_debut < ADDTIME(e2.heure_debut, SEC_TO_TIME(e2.duree_minutes*60))
                       AND ADDTIME(e1.heure_debut, SEC_TO_TIME(e1.duree_minutes*60)) > e2.heure_debut)";
    $result_salle = $conn->query($sql_salle);
    while ($row = $result_salle->fetch_assoc()) {
        $conflicts[] = "<strong style='color:#721c24;'>🟥 Salle {$row['salle']} - {$row['date_examen']}</strong><br>
                        • {$row['module1']} ({$row['h1']}) ↔ {$row['module2']} ({$row['h2']})";
    }

    $sql_prof = "SELECT m1.nom AS module1, m2.nom AS module2, CONCAT(COALESCE(u.prenom,''), ' ', u.nom) AS prof, e1.date_examen, e1.heure_debut AS h1, e2.heure_debut AS h2
                 FROM examens e1
                 JOIN examens e2 ON e1.id < e2.id
                 JOIN modules m1 ON e1.module_id = m1.id
                 JOIN modules m2 ON e2.module_id = m2.id
                 JOIN utilisateurs u ON e1.prof_id = u.id
                 WHERE e1.prof_id = e2.prof_id AND e1.date_examen = e2.date_examen
                 AND (e1.heure_debut < ADDTIME(e2.heure_debut, SEC_TO_TIME(e2.duree_minutes*60))
                      AND ADDTIME(e1.heure_debut, SEC_TO_TIME(e1.duree_minutes*60)) > e2.heure_debut)";
    $result_prof = $conn->query($sql_prof);
    while ($row = $result_prof->fetch_assoc()) {
        $conflicts[] = "<strong style='color:#856404;'>🟨 Prof {$row['prof']} - {$row['date_examen']}</strong><br>
                        • {$row['module1']} ({$row['h1']}) ↔ {$row['module2']} ({$row['h2']})";
    }

    if (!empty($conflicts)) {
        $conflictResult = "<div style='background:#f8d7da;color:#721c24;padding:20px;border-radius:8px;'>
            ⚠️ <strong>" . count($conflicts) . " conflit(s) détecté(s) :</strong>
            <ul style='margin:15px 0;list-style:none;padding-left:0;'>";
        foreach ($conflicts as $c) $conflictResult .= "<li style='margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #ddd;'>$c</li>";
        $conflictResult .= "</ul></div>";
    } else {
        $conflictResult = "<div style='background:#d4edda;color:#155724;padding:20px;border-radius:8px;'>
            ✅ <strong>Aucun conflit détecté !</strong> EDT cohérent.
        </div>";
    }
}

// === 4. OPTIMISATION SALLES ===
if (isset($_POST['optimizeResources'])) {
    $activeSection = 'ressources';

    $total_exams = $conn->query("SELECT COUNT(*) as tot FROM examens")->fetch_assoc()['tot'];
    $total_salles = $conn->query("SELECT COUNT(*) as tot FROM lieu_examen")->fetch_assoc()['tot'];
    $ideal_per_salle = $total_salles > 0 ? round($total_exams / $total_salles, 1) : 0;

    $loads_res = $conn->query("SELECT COUNT(e.id) as nb FROM lieu_examen le LEFT JOIN examens e ON le.id = e.salle_id GROUP BY le.id HAVING nb > 0");
    $loads = [];
    while ($r = $loads_res->fetch_assoc()) $loads[] = $r['nb'];
    $max_load = !empty($loads) ? max($loads) : 0;
    $min_load = !empty($loads) ? min($loads) : 0;

    $sql = "SELECT le.nom, le.capacite, le.type, COUNT(e.id) as nb_examens
            FROM lieu_examen le LEFT JOIN examens e ON le.id = e.salle_id
            GROUP BY le.id ORDER BY nb_examens DESC";
    $result = $conn->query($sql);

    $optimizeResult = "<div style='padding:15px;'>
        <h4 style='color:#004080;'>Utilisation des salles (idéal ≈ $ideal_per_salle examens/salle)</h4>
        <table style='width:100%;border-collapse:collapse;margin-top:10px;border:1px solid #ddd;'>
            <tr style='background:#004080;color:white;'>
                <th>Salle</th><th>Type</th><th>Capacité</th><th>Examens</th><th>Écart idéal</th><th>État</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        $nb = $row['nb_examens'];
        $ecart = $nb - $ideal_per_salle;
        $etat = abs($ecart) > 2 ? 'Déséquilibrée' : 'Équilibrée';
        $color = $nb > $ideal_per_salle + 2 ? '#ffb3b3' : ($nb < $ideal_per_salle - 2 ? '#fff3cd' : '#d4edda');

        $optimizeResult .= "<tr style='background:$color;'>
            <td style='padding:12px;'><strong>{$row['nom']}</strong></td>
            <td style='padding:12px;'>{$row['type']}</td>
            <td style='padding:12px;'>{$row['capacite']}</td>
            <td style='padding:12px;'><strong>$nb</strong></td>
            <td style='padding:12px;'>" . ($ecart >= 0 ? '+' : '') . "$ecart</td>
            <td style='padding:12px;'><span style='font-weight:bold;color:" . ($etat == 'Déséquilibrée' ? 'red' : 'green') . ";'>$etat</span></td>
        </tr>";
    }
    $optimizeResult .= "</table>
        <div style='margin-top:25px;padding:15px;background:#f0f8ff;border-left:4px solid #004080;border-radius:6px;'>
            <strong>Suggestions :</strong><br>";
    if ($total_exams == 0) {
        $optimizeResult .= "Aucun examen planifié.";
    } elseif ($max_load - $min_load > 3) {
        $optimizeResult .= "Déséquilibre important (max: $max_load, min: $min_load).<br>Recommandation : relancer une génération.";
    } else {
        $optimizeResult .= "Bonne répartition globale !";
    }
    $optimizeResult .= "</div></div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion Examens</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
        header { background: #004080; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .container { display: flex; min-height: calc(100vh - 70px); }
        aside { width: 220px; background: #e9ecef; padding: 20px 10px; }
        aside ul { list-style: none; padding: 0; margin: 0; }
        aside li { padding: 15px; margin: 8px 0; background: #004080; color: white; cursor: pointer; border-radius: 8px; text-align: center; font-weight: bold; }
        aside li:hover { background: #0066cc; }
        main { flex: 1; padding: 30px; }
        section { display: none; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        section.active { display: block; }
        button { padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 5px; }
        .btn-simulate { background: #007bff; color: white; }
        .btn-apply { background: #155724; color: white; font-weight: bold; }
        .btn-detect { background: #ffc107; color: black; font-weight: bold; }
        .btn-optimize { background: #dc3545; color: white; }
        .btn-pdf { background: #17a2b8; color: white; }
        h3 { color: #004080; margin-top: 0; }
        .result-box { min-height: 120px; padding: 20px; background: #f9f9f9; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h2>🗓️ Admin - Gestion Examens</h2>
    <div>Base: gestion_examens_db | <a href="logout.php" style="color:white;">Déconnexion</a></div>
</header>

<div class="container">
    <aside>
        <ul>
            <li onclick="showSection('generation')">⚙️ Générer EDT</li>
            <li onclick="showSection('conflits')">⚠️ Conflits</li>
            <li onclick="showSection('ressources')">📊 Salles</li>
            <li onclick="showSection('rapports')">📄 Rapports</li>
        </ul>
    </aside>

    <main>
        <!-- GÉNÉRATION -->
        <section id="generation" class="<?php echo $activeSection === 'generation' ? 'active' : ''; ?>">
            <h3>🚀 Génération automatique EDT</h3>
            <div style="margin-bottom:20px;">
                <form method="POST" style="display:inline-block;margin-right:10px;">
                    <button class="btn-simulate" name="simulateEDT">🔍 Prévisualiser (Simulation)</button>
                </form>
                <form method="POST" style="display:inline-block;">
                    <button class="btn-apply" name="generateEDT">💾 Appliquer définitivement</button>
                </form>
            </div>
            <div class="result-box">
                <?php echo $generateResult ?: '<em>Utilisez « Prévisualiser » pour tester avant d\'appliquer.</em>'; ?>
            </div>
        </section>

        <!-- CONFLITS -->
        <section id="conflits" class="<?php echo $activeSection === 'conflits' ? 'active' : ''; ?>">
            <h3>🔍 Détection des conflits</h3>
            <div style="margin-bottom:20px;">
                <form method="POST" style="display:inline-block;margin-right:10px;">
                    <button class="btn-detect" name="detectConflicts">Analyser les conflits</button>
                </form>
                <button class="btn-pdf" onclick="exportPDF('conflits')">📄 PDF</button>
            </div>
            <div class="result-box" id="content-conflits">
                <?php echo $conflictResult ?: '<em>Cliquez sur « Analyser » pour vérifier les chevauchements.</em>'; ?>
            </div>
        </section>

        <!-- SALLES -->
        <section id="ressources" class="<?php echo $activeSection === 'ressources' ? 'active' : ''; ?>">
            <h3>📊 Optimisation des salles</h3>
            <div style="margin-bottom:20px;">
                <form method="POST" style="display:inline-block;margin-right:10px;">
                    <button class="btn-optimize" name="optimizeResources">Analyser l'utilisation</button>
                </form>
                <button class="btn-pdf" onclick="exportPDF('ressources')">📄 PDF</button>
            </div>
            <div class="result-box" id="content-ressources">
                <?php echo $optimizeResult ?: '<em>Cliquez sur « Analyser » pour voir la répartition des examens par salle.</em>'; ?>
            </div>
        </section>

        <!-- RAPPORTS -->
   <section id="rapports" class="<?php echo $activeSection === 'rapports' ? 'active' : ''; ?>">
    <h3>📋 Tous les examens planifiés (sans les examens en conflit)</h3>
    <button class="btn-pdf" onclick="exportPDF('rapports')">📄 Exporter en PDF</button>
    
    <div class="result-box" id="content-rapports">
        <?php
        // === DÉTECTION DES IDs EN CONFLIT (salle + prof) ===
        $conflicted_exam_ids = [];

        // Conflits salle
        $sql_conflict_salle = "SELECT e1.id AS id1, e2.id AS id2
                               FROM examens e1
                               JOIN examens e2 ON e1.id < e2.id
                               WHERE e1.salle_id = e2.salle_id 
                               AND e1.date_examen = e2.date_examen
                               AND (e1.heure_debut < ADDTIME(e2.heure_debut, SEC_TO_TIME(e2.duree_minutes*60))
                                    AND ADDTIME(e1.heure_debut, SEC_TO_TIME(e1.duree_minutes*60)) > e2.heure_debut)";
        $res_salle = $conn->query($sql_conflict_salle);
        while ($row = $res_salle->fetch_assoc()) {
            $conflicted_exam_ids[] = $row['id1'];
            $conflicted_exam_ids[] = $row['id2'];
        }

        // Conflits professeur
        $sql_conflict_prof = "SELECT e1.id AS id1, e2.id AS id2
                              FROM examens e1
                              JOIN examens e2 ON e1.id < e2.id
                              WHERE e1.prof_id = e2.prof_id 
                              AND e1.date_examen = e2.date_examen
                              AND (e1.heure_debut < ADDTIME(e2.heure_debut, SEC_TO_TIME(e2.duree_minutes*60))
                                   AND ADDTIME(e1.heure_debut, SEC_TO_TIME(e1.duree_minutes*60)) > e2.heure_debut)";
        $res_prof = $conn->query($sql_conflict_prof);
        while ($row = $res_prof->fetch_assoc()) {
            $conflicted_exam_ids[] = $row['id1'];
            $conflicted_exam_ids[] = $row['id2'];
        }

        // Supprimer les doublons
        $conflicted_exam_ids = array_unique($conflicted_exam_ids);
        $nb_conflicts = count($conflicted_exam_ids);

        // Clause d'exclusion
        $where_conflict = '';
        if ($nb_conflicts > 0) {
            $ids_list = implode(',', array_map('intval', $conflicted_exam_ids));
            $where_conflict = "AND e.id NOT IN ($ids_list)";
        }

        // === AFFICHAGE DES EXAMENS SANS CONFLIT ===
        $all_exams_sql = "SELECT 
                            e.date_examen, 
                            e.heure_debut, 
                            e.duree_minutes,
                            m.nom AS module_nom, 
                            le.nom AS salle_nom, 
                            COALESCE(CONCAT(u.prenom, ' ', u.nom), u.nom, 'Non assigné') AS prof_display
                          FROM examens e 
                          JOIN modules m ON e.module_id = m.id 
                          JOIN lieu_examen le ON e.salle_id = le.id 
                          LEFT JOIN utilisateurs u ON e.prof_id = u.id 
                          WHERE 1=1 $where_conflict
                          ORDER BY e.date_examen ASC, e.heure_debut ASC";

        $all_exams = $conn->query($all_exams_sql);

        if ($all_exams && $all_exams->num_rows > 0) {
            echo "<table style='width:100%;border-collapse:collapse;border:1px solid #ddd;margin-top:10px;'>
                  <thead>
                    <tr style='background:#004080;color:white;'>
                      <th style='padding:10px;'>Date</th>
                      <th style='padding:10px;'>Heure</th>
                      <th style='padding:10px;'>Module</th>
                      <th style='padding:10px;'>Salle</th>
                      <th style='padding:10px;'>Prof</th>
                      <th style='padding:10px;'>Durée</th>
                    </tr>
                  </thead>
                  <tbody>";

            while ($exam = $all_exams->fetch_assoc()) {
                echo "<tr style='border-bottom:1px solid #eee;'>
                      <td style='padding:10px;text-align:center;'>{$exam['date_examen']}</td>
                      <td style='padding:10px;text-align:center;'>{$exam['heure_debut']}</td>
                      <td style='padding:10px;'><strong>{$exam['module_nom']}</strong></td>
                      <td style='padding:10px;text-align:center;'>{$exam['salle_nom']}</td>
                      <td style='padding:10px;text-align:center;'>{$exam['prof_display']}</td>
                      <td style='padding:10px;text-align:center;'>{$exam['duree_minutes']} min</td>
                  </tr>";
            }

            echo "</tbody></table>";

            if ($nb_conflicts > 0) {
                echo "<p style='margin-top:20px;color:#721c24;background:#f8d7da;padding:12px;border-radius:6px;'>
                      ℹ️ <strong>$nb_conflicts examen(s) en conflit</strong> ont été exclus de ce rapport.<br>
                      Consultez la section « Conflits » pour les détails.
                      </p>";
            }
        } else {
            echo "<p style='color:#666;font-style:italic;'>Aucun examen planifié sans conflit pour le moment.</p>";
        }
        ?>
    </div>
</section>
    </main>
</div>

<script>
const { jsPDF } = window.jspdf;
function showSection(id) {
    document.querySelectorAll("section").forEach(s => s.classList.remove("active"));
    document.getElementById(id).classList.add("active");
}
function exportPDF(section) {
    const doc = new jsPDF('p', 'mm', 'a4');
    const content = document.getElementById('content-' + section);
    let title = section === 'conflits' ? 'Rapport Conflits' : (section === 'ressources' ? 'Analyse Salles' : 'Planning Examens');
    doc.setFontSize(18); doc.text(title, 20, 20);
    doc.setFontSize(12); doc.text('Date: <?php echo date('d/m/Y H:i'); ?>', 20, 30);
    if (content && content.querySelector('table')) {
        doc.autoTable({html: content.querySelector('table'), startY: 40});
    } else {
        doc.text(content ? content.innerText : 'Aucun contenu', 20, 40);
    }
    doc.save(title + '.pdf');
}
</script>
</body>
</html>
