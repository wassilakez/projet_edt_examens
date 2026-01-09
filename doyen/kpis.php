<?php
header('Content-Type: application/json');
include "db.php";

$kpis = [];

// Récupérer tous les départements
$res_dep = $conn->query("SELECT id, nom FROM departements");
$departements = [];
while($d=$res_dep->fetch_assoc()){
    $departements[$d['id']] = [
        'nom'=>$d['nom'],
        'heures_profs'=>0,
        'examens'=>0,
        'salles_utilisees'=>0
    ];
}

// Récupérer total de salles par type
$total_salles = $conn->query("SELECT COUNT(*) as nb FROM lieu_examen");
$total_salles = $total_salles->fetch_assoc()['nb'] ?? 0;

// Récupérer données examens
$sql = "
SELECT f.dept_id, COUNT(e.id) AS nb_examens, SUM(e.duree_minutes) AS total_minutes, COUNT(DISTINCT e.salle_id) AS salles_utilisees
FROM examens e
JOIN modules m ON e.module_id=m.id
JOIN formations f ON m.formation_id=f.id
WHERE e.statut='VALIDE'
GROUP BY f.dept_id
";
$res = $conn->query($sql);
while($r = $res->fetch_assoc()){
    $dept_id = $r['dept_id'];
    $departements[$dept_id]['heures_profs'] = round($r['total_minutes']/60,1); // minutes -> heures
    $departements[$dept_id]['examens'] = $r['nb_examens'];
    $departements[$dept_id]['salles_utilisees'] = (int)$r['salles_utilisees'];
    $departements[$dept_id]['total_salles'] = (int)$total_salles;
}

echo json_encode(array_values($departements));
