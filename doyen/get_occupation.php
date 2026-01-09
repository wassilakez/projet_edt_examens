<?php
header('Content-Type: application/json');
include "db.php";

// récupérer salles
$sql = "SELECT l.id, l.nom, l.capacite,
        (SELECT COUNT(*) FROM examens e WHERE e.salle_id = l.id) AS nb_examens
        FROM lieu_examen l";

$res = $conn->query($sql);
$data = [];
while($row = $res->fetch_assoc()){
    $taux = $row['capacite']>0 ? round($row['nb_examens']*100/$row['capacite'],2) : 0;
    $data[] = [
        'nom'=>$row['nom'],
        'capacite'=>$row['capacite'],
        'nb_examens'=>$row['nb_examens'],
        'taux'=>$taux
    ];
}
echo json_encode($data);
