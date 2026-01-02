<?php
require "db.php";

$stmt = $pdo->query("SELECT * FROM examens");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
