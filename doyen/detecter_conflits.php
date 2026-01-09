<?php
header('Content-Type: application/json');
include "db.php";

$conflits = [];

// 1️⃣ Étudiants : max 1 examen / jour
$sql = "
SELECT u.id AS etudiant_id, u.nom, u.prenom, e.date_examen, COUNT(*) AS nb
FROM inscriptions i
JOIN examens e ON i.module_id=e.module_id
JOIN utilisateurs u ON i.etudiant_id=u.id
GROUP BY u.id, e.date_examen
HAVING nb>1
";
$res = $conn->query($sql);
while($r=$res->fetch_assoc()){
    $conflits[] = [
        'type'=>'Étudiant',
        'nom'=>$r['prenom'].' '.$r['nom'],
        'detail'=> $r['nb'] . " examens le même jour",
        'date'=> $r['date_examen'],
        'status'=>'warning'
    ];
}

// 2️⃣ Professeurs : max 3 examens / jour
$sql = "
SELECT p.id AS prof_id, p.nom, p.prenom, e.date_examen, COUNT(*) AS nb
FROM examens e
JOIN utilisateurs p ON e.prof_id=p.id
GROUP BY p.id, e.date_examen
HAVING nb>3
";
$res = $conn->query($sql);
while($r=$res->fetch_assoc()){
    $conflits[] = [
        'type'=>'Professeur',
        'nom'=>$r['prenom'].' '.$r['nom'],
        'detail'=> $r['nb'] . " examens le même jour",
        'date'=> $r['date_examen'],
        'status'=>'danger'
    ];
}

// 3️⃣ Salles : vérifier capacité
$sql = "
SELECT e.id, le.nom AS salle, f.nom AS formation, f.effectif, le.capacite, e.date_examen
FROM examens e
JOIN modules m ON e.module_id=m.id
JOIN formations f ON m.formation_id=f.id
JOIN lieu_examen le ON e.salle_id=le.id
WHERE f.effectif > le.capacite
";
$res = $conn->query($sql);
while($r=$res->fetch_assoc()){
    $conflits[] = [
        'type'=>'Salle',
        'nom'=> $r['salle'],
        'detail'=> "Capacité : ".$r['capacite'].", effectif formation : ".$r['effectif'],
        'date'=> $r['date_examen'],
        'status'=>'danger'
    ];
}

echo json_encode($conflits);
