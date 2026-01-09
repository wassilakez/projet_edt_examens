<?php
header('Content-Type: application/json');
include 'db.php';

$departement = $_POST['departement'] ?? '';
$niveau = $_POST['niveau'] ?? '';
$specialite = $_POST['specialite'] ?? '';
$statut = $_POST['statut'] ?? 'EN_ATTENTE';

if(!$departement || !$specialite){
    echo json_encode(['success'=>false, 'msg'=>'Paramètres manquants']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM formations WHERE nom=?");
$stmt->bind_param("s", $specialite);
$stmt->execute();
$stmt->bind_result($formation_id);
if(!$stmt->fetch()){
    echo json_encode(['success'=>false, 'msg'=>'Formation introuvable']);
    exit;
}
$stmt->close();

$stmt = $conn->prepare("UPDATE examens e 
                        JOIN modules m ON e.module_id = m.id 
                        SET e.statut=? 
                        WHERE m.formation_id=?");
$stmt->bind_param("si", $statut, $formation_id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success'=>$success]);
?>
