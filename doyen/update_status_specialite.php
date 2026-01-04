<?php
header('Content-Type: application/json');
require "db.php"; // ton fichier qui contient $pdo

$departement = $_POST['departement'] ?? '';
$niveau = $_POST['niveau'] ?? '';
$specialite = $_POST['specialite'] ?? '';
$statut = $_POST['statut'] ?? 'EN_ATTENTE';

// Récupérer toutes les formations correspondant à cette spécialité
$sqlFormations = "SELECT f.id FROM formations f
                  JOIN departements d ON d.id=f.dept_id
                  WHERE d.nom=:dep AND f.nom=:spec";
$stmt=$pdo->prepare($sqlFormations);
$stmt->execute(['dep'=>$departement,'spec'=>$specialite]);
$formations=$stmt->fetchAll(PDO::FETCH_COLUMN);

if(!$formations){
    echo json_encode(['success'=>false,'msg'=>'Formation introuvable']);
    exit;
}

// Mettre à jour tous les examens de ces formations
$in = str_repeat('?,',count($formations)-1).'?';
$sqlUpdate = "UPDATE examens SET statut=? WHERE module_id IN (SELECT id FROM modules WHERE formation_id IN ($in))";
$stmt=$pdo->prepare($sqlUpdate);
$stmt->execute(array_merge([$statut],$formations));

echo json_encode(['success'=>true]);
?>
