<?php
require "db.php";

$statut = $_POST['statut'] ?? '';

if (!in_array($statut, ['VALIDE','REJETE','EN_ATTENTE'])) {
    exit("Statut invalide");
}

$pdo->prepare("UPDATE examens SET statut=?")->execute([$statut);
echo "OK";
