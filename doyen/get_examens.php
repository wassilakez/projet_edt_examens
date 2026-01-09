<?php
header('Content-Type: application/json');
include 'db.php';

$sql = "SELECT 
          d.nom AS departement,
          f.nom AS specialite,
          CASE 
            WHEN f.nom LIKE 'Licence%' THEN 'Licence'
            WHEN f.nom LIKE 'Master%' THEN 'Master'
            WHEN f.nom LIKE 'Doctorat%' THEN 'Doctorat'
            ELSE 'N/A'
          END AS niveau,
          m.nom AS module,
          e.date_examen,
          le.nom AS salle,
          CONCAT(u.nom,' ',u.prenom) AS surveillant,
          e.statut
        FROM examens e
        LEFT JOIN modules m ON e.module_id = m.id
        LEFT JOIN formations f ON m.formation_id = f.id
        LEFT JOIN departements d ON f.dept_id = d.id
        LEFT JOIN utilisateurs u ON e.prof_id = u.id
        LEFT JOIN lieu_examen le ON e.salle_id = le.id
        ORDER BY d.nom, niveau, f.nom, e.date_examen";

$result = $conn->query($sql);
$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}
echo json_encode($data);
?>
