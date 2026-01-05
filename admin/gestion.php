<?php
// gestion.php - VERSION AMÉLIORÉE (mêmes fonctionnalités, mais plus réaliste et robuste)
include 'db.php';

$generateResult = '';
$conflictResult = '';
$optimizeResult = '';
$activeSection = 'generation';

// === 1. GÉNÉRATION EDT (améliorée) ===
if (isset($_POST['generateEDT'])) {
    $activeSection = 'generation';
    
    // Modules sans examen
    $sql = "SELECT m.id, m.nom, f.nom as formation, d.nom as dept, d.id as dept_id 
            FROM modules m 
            JOIN formations f ON m.formation_id = f.id 
            JOIN departements d ON f.dept_id = d.id 
            LEFT JOIN examens e ON m.id = e.module_id
            WHERE e.id IS NULL 
            LIMIT 5";
    
    $result = $conn->query($sql);
    $generated = 0;
    
    if ($result && $result->num_rows > 0) {
        while ($module = $result->fetch_assoc()) {
            // AMÉLIORATION : chercher un professeur réel du même département
            $prof_sql = "SELECT id FROM utilisateurs 
                         WHERE dept_id = ? AND role = 'professeur' 
                         ORDER BY RAND() LIMIT 1";
            $stmt_prof = $conn->prepare($prof_sql);
            $stmt_prof->bind_param("i", $module['dept_id']);
            $stmt_prof->execute();
            $prof_result = $stmt_prof->get_result();
            $prof_id = $prof_result->num_rows > 0 ? $prof_result->fetch_assoc()['id'] : 1; // fallback ID 1
            
            // AMÉLIORATION : dates variées sur 1 mois (tous les 3 jours)
            $dates_possibles = [];
            for ($i = 7; $i <= 30; $i += 3) {
                $dates_possibles[] = date('Y-m-d', strtotime("+$i days"));
            }
            
            $times = ['09:00:00', '11:00:00', '14:00:00'];
            $duree = 90;
            $placed = false;
            
            foreach ($dates_possibles as $date) {
                if ($placed) break;
                
                // Chercher une salle disponible (capacité >= 20)
                $room_sql = "SELECT * FROM lieu_examen WHERE capacite >= 20 ORDER BY RAND() LIMIT 3"; // plus de choix
                $room_result = $conn->query($room_sql);
                
                while ($room = $room_result->fetch_assoc()) {
                    foreach ($times as $heure) {
                        $end_time = date('H:i:s', strtotime($heure . " + $duree minutes"));
                        
                        // Vérification conflit SALLE
                        $check_salle = "SELECT 1 FROM examens 
                                        WHERE salle_id = ? AND date_examen = ?
                                        AND heure_debut < ? 
                                        AND ADDTIME(heure_debut, SEC_TO_TIME(duree_minutes*60)) > ?";
                        $stmt_salle = $conn->prepare($check_salle);
                        $stmt_salle->bind_param("isss", $room['id'], $date, $end_time, $heure);
                        $stmt_salle->execute();
                        
                        // Vérification conflit PROF (nouveau !)
                        $check_prof = "SELECT 1 FROM examens 
                                       WHERE prof_id = ? AND date_examen = ?
                                       AND heure_debut < ? 
                                       AND ADDTIME(heure_debut, SEC_TO_TIME(duree_minutes*60)) > ?";
                        $stmt_prof_check = $conn->prepare($check_prof);
                        $stmt_prof_check->bind_param("isss", $prof_id, $date, $end_time, $heure);
                        $stmt_prof_check->execute();
                        
                        if ($stmt_salle->get_result()->num_rows == 0 && $stmt_prof_check->get_result()->num_rows == 0) {
                            // Créneau libre → insertion
                            $insert_sql = "INSERT INTO examens (module_id, prof_id, salle_id, date_examen, heure_debut, duree_minutes) 
                                           VALUES (?, ?, ?, ?, ?, ?)";
                            $insert_stmt = $conn->prepare($insert_sql);
                            $insert_stmt->bind_param("iiisii", $module['id'], $prof_id, $room['id'], $date, $heure, $duree);
                            $insert_stmt->execute();
                            $generated++;
                            $placed = true;
                            break 3; // sort de toutes les boucles
                        }
                    }
                }
            }
        }
        
        $generateResult = "<div style='background:#d4edda;color:#155724;padding:15px;border-radius:8px;border:1px solid #c3e6cb;'>
            ✅ <strong>$generated nouveaux examens planifiés !</strong><br>
            <em>Dates et professeurs variés, contraintes salle + prof respectées.</em>
        </div>";
    } else {
        $generateResult = "<div style='background:#fff3cd;color:#856404;padding:15px;border-radius:8px;'>
            ℹ️ Aucun module sans examen trouvé. Tous les modules sont déjà planifiés !
        </div>";
    }
}

// === 2. DÉTECTION CONFLITS (améliorée : salle + prof) ===
if (isset($_POST['detectConflicts'])) {
    $activeSection = 'conflits';
    $conflicts = [];
    
    // Conflits salle
    $sql_salle = "SELECT e1.id as id1, e2.id as id2, 
                  m1.nom as module1, m2.nom as module2,
                  le1.nom as salle, e1.date_examen,
                  e1.heure_debut as h1, e2.heure_debut as h2
                  FROM examens e1 
                  JOIN examens e2 ON e1.id < e2.id
                  JOIN modules m1 ON e1.module_id = m1.id
                  JOIN modules m2 ON e2.module_id = m2.id
                  JOIN lieu_examen le1 ON e1.salle_id = le1.id
                  WHERE e1.salle_id = e2.salle_id 
                  AND e1.date_examen = e2.date_examen
                  AND (e1.heure_debut < ADDTIME(e2.heure_debut, SEC_TO_TIME(e2.duree_minutes*60))
                   AND ADDTIME(e1.heure_debut, SEC_TO_TIME(e1.duree_minutes*60)) > e2.heure_debut)";
    $result_salle = $conn->query($sql_salle);
    while ($row = $result_salle->fetch_assoc()) {
        $conflicts[] = "🟥 <strong>Salle {$row['salle']} - {$row['date_examen']}</strong><br>
                        {$row['module1']} ({$row['h1']}) ↔ {$row['module2']} ({$row['h2']})";
    }
    
    // AMÉLIORATION : Conflits professeur
    $sql_prof = "SELECT e1.id as id1, e2.id as id2, 
                 m1.nom as module1, m2.nom as module2,
                 u.nom as prof, e1.date_examen,
                 e1.heure_debut as h1, e2.heure_debut as h2
                 FROM examens e1 
                 JOIN examens e2 ON e1.id < e2.id
                 JOIN modules m1 ON e1.module_id = m1.id
                 JOIN modules m2 ON e2.module_id = m2.id
                 JOIN utilisateurs u ON e1.prof_id = u.id
                 WHERE e1.prof_id = e2.prof_id 
                 AND e1.date_examen = e2.date_examen
                 AND (e1.heure_debut < ADDTIME(e2.heure_debut, SEC_TO_TIME(e2.duree_minutes*60))
                  AND ADDTIME(e1.heure_debut, SEC_TO_TIME(e1.duree_minutes*60)) > e2.heure_debut)";
    $result_prof = $conn->query($sql_prof);
    while ($row = $result_prof->fetch_assoc()) {
        $conflicts[] = "🟨 <strong>Prof {$row['prof']} - {$row['date_examen']}</strong><br>
                        {$row['module1']} ({$row['h1']}) ↔ {$row['module2']} ({$row['h2']})";
    }
    
    if (!empty($conflicts)) {
        $conflictResult = "<div style='background:#f8d7da;color:#721c24;padding:15px;border-radius:8px;border:1px solid #f5c6cb;'>
            ⚠️ <strong>" . count($conflicts) . " conflit(s) détecté(s) :</strong><br>
            <ul style='margin:10px 0;'>";
        foreach ($conflicts as $c) {
            $conflictResult .= "<li>$c</li>";
        }
        $conflictResult .= "</ul></div>";
    } else {
        $conflictResult = "<div style='background:#d4edda;color:#155724;padding:15px;border-radius:8px;'>
            ✅ <strong>Aucun conflit détecté !</strong> EDT cohérent (salles + professeurs).</div>";
    }
}

// === 3. OPTIMISATION RESSOURCES (calculs améliorés) ===
if (isset($_POST['optimizeResources'])) {
    $activeSection = 'ressources';
    
    // Total examens et salles pour calcul idéal
    $total_exams = $conn->query("SELECT COUNT(*) as tot FROM examens")->fetch_assoc()['tot'];
    $total_salles = $conn->query("SELECT COUNT(*) as tot FROM lieu_examen")->fetch_assoc()['tot'];
    $ideal_per_salle = $total_salles > 0 ? round($total_exams / $total_salles, 1) : 0;
    
    $sql = "SELECT le.nom, le.capacite, le.type, COUNT(e.id) as nb_examens
            FROM lieu_examen le 
            LEFT JOIN examens e ON le.id = e.salle_id
            GROUP BY le.id
            ORDER BY nb_examens DESC";
    
    $result = $conn->query($sql);
    
    $optimizeResult = "<div style='padding:15px;'>
        <h4 style='color:#004080;margin-top:0;'>📊 Utilisation des salles (idéal ≈ $ideal_per_salle examens/salle)</h4>
        <table style='width:100%;border-collapse:collapse;margin-top:10px;border:1px solid #ddd;'>
            <tr style='background:#004080;color:white;'>
                <th style='padding:12px;'>Salle</th>
                <th style='padding:12px;'>Type</th>
                <th style='padding:12px;'>Capacité</th>
                <th style='padding:12px;'>Examens</th>
                <th style='padding:12px;'>Écart idéal</th>
                <th style='padding:12px;'>État</th>
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
    $optimizeResult .= "</table></div>";
}
?>

<!-- Le reste du HTML reste IDENTIQUE à ton code original -->
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin - Gestion Examens Réels</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
    header { background: #004080; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
    .container { display: flex; min-height: calc(100vh - 70px); }
    aside { width: 220px; background: #ddd; padding: 20px 10px; }
    aside ul { list-style: none; padding: 0; margin: 0; }
    aside li { padding: 15px; margin: 8px 0; background: #004080; color: white; cursor: pointer; border-radius: 5px; text-align: center; }
    aside li:hover { background: #0066cc; }
    main { flex: 1; padding: 30px; }
    section { display: none; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    section.active { display: block; }
    button { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 5px; }
    .btn-generate { background: #28a745; color: white; }
    .btn-detect { background: #ffc107; color: black; }
    .btn-optimize { background: #dc3545; color: white; }
    .btn-pdf { background: #007bff; color: white; }
    h3 { color: #004080; margin-top: 0; }
    .result-box { min-height: 120px; padding: 20px; background: #f9f9f9; border-radius: 8px; margin-top: 20px; }
  </style>
</head>
<body>

<header>
  <h2>🗓️ Admin - Gestion Examens (Réel)</h2>
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
    <section id="generation" class="<?php echo $activeSection === 'generation' ? 'active' : ''; ?>">
      <h3>🚀 Génération automatique EDT</h3>
      <form method="POST">
        <button class="btn-generate" name="generateEDT">Générer nouveaux examens</button>
      </form>
      <div class="result-box"><?php echo $generateResult ?: '<em>Planifie automatiquement les modules sans examen (contraintes salle + prof).</em>'; ?></div>
    </section>

    <section id="conflits" class="<?php echo $activeSection === 'conflits' ? 'active' : ''; ?>">
      <h3>🔍 Détection conflits</h3>
      <form method="POST" style="display:inline;">
        <button class="btn-detect" name="detectConflicts">Analyser conflits</button>
      </form>
      <button class="btn-pdf" onclick="exportPDF('conflits')">📄 PDF</button>
      <div class="result-box" id="content-conflits"><?php echo $conflictResult ?: '<em>Cliquez pour analyser les chevauchements (salles + professeurs).</em>'; ?></div>
    </section>

    <section id="ressources" class="<?php echo $activeSection === 'ressources' ? 'active' : ''; ?>">
      <h3>📊 Optimisation salles</h3>
      <form method="POST" style="display:inline;">
        <button class="btn-optimize" name="optimizeResources">Analyser utilisation</button>
      </form>
      <button class="btn-pdf" onclick="exportPDF('ressources')">📄 PDF</button>
      <div class="result-box" id="content-ressources"><?php echo $optimizeResult ?: '<em>Statistiques réelles avec répartition idéale.</em>'; ?></div>
    </section>

<section id="rapports" class="<?php echo $activeSection === 'rapports' ? 'active' : ''; ?>">
  <h3>📋 Tous les examens planifiés (sans les examens en conflit)</h3>
  <button class="btn-pdf" onclick="exportPDF('rapports')">📄 Exporter en PDF</button>
  
  <div class="result-box" id="content-rapports">
    <?php
    // === Récupérer les IDs des examens en conflit (salle OU prof) ===
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

    // Construire la condition WHERE pour exclure les conflits
    $where_conflict = '';
    if ($nb_conflicts > 0) {
        $ids_list = implode(',', array_map('intval', $conflicted_exam_ids)); // sécurité
        $where_conflict = "AND e.id NOT IN ($ids_list)";
    }

    // === Requête finale : tous les examens SANS ceux en conflit ===
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

        // Message d'information sur les conflits exclus
        if ($nb_conflicts > 0) {
            echo "<p style='margin-top:20px;color:#721c24;background:#f8d7da;padding:12px;border-radius:6px;font-style:italic;'>
                  ℹ️ Les examens en conflit détecté(s) (<strong>$nb_conflicts examen(s)</strong>) ont été exclus de ce rapport.<br>
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
    document.querySelectorAll("section").forEach(sec => sec.classList.remove("active"));
    document.getElementById(id).classList.add("active");
}
function exportPDF(section) {
    const doc = new jsPDF('p', 'mm', 'a4');
    const content = document.getElementById('content-' + section);
    const title = section === 'conflits' ? 'Rapport Conflits' : 'Analyse Salles';
    doc.setFontSize(18); doc.text(title, 20, 20);
    doc.setFontSize(12); doc.text('Date: <?php echo date('d/m/Y H:i'); ?>', 20, 30);
    if (content.querySelector('table')) doc.autoTable({html: content.querySelector('table'), startY: 40});
    else doc.html(content, {callback: function(doc) { doc.save(title + '.pdf'); }, x: 20, y: 40});
}
</script>

</body>
</html>
