<?php
header('Content-Type: application/json');
include "db.php";

$data = [];

// Récupérer tous les départements
$res_dep = $conn->query("SELECT id, nom FROM departements");
$departements = [];
while($d = $res_dep->fetch_assoc()){
    $departements[$d['id']] = ['nom'=>$d['nom'], 'salles'=>0, 'total_salles'=>0];
}

// Total de chaque type de salle (Amphi, Salle, Labo)
$salles_res = $conn->query("SELECT id, type FROM lieu_examen");
$total_salles = [];
while($s = $salles_res->fetch_assoc()){
    $total_salles[$s['type']] = isset($total_salles[$s['type']]) ? $total_salles[$s['type']]+1 : 1;
}

// Occupation des salles par département
$sql = "
SELECT f.dept_id, le.type, COUNT(DISTINCT e.salle_id) AS nb_utilisees
FROM examens e
JOIN modules m ON e.module_id=m.id
JOIN formations f ON m.formation_id=f.id
JOIN lieu_examen le ON e.salle_id=le.id
WHERE e.statut='VALIDE'
GROUP BY f.dept_id, le.type
";
$res = $conn->query($sql);
while($r = $res->fetch_assoc()){
    $dept_id = $r['dept_id'];
    $type = $r['type'];
    $departements[$dept_id]['salles_'.$type] = (int)$r['nb_utilisees'];
}

// Ajouter total de chaque type
foreach($departements as $id=>$d){
    $departements[$id]['total_amphi'] = $total_salles['Amphi'] ?? 0;
    $departements[$id]['total_salle'] = $total_salles['Salle'] ?? 0;
    $departements[$id]['total_labo'] = $total_salles['Labo'] ?? 0;
}

// Retour JSON
echo json_encode(array_values($departements));
