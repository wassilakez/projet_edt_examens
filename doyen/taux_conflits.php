<?php
header('Content-Type: application/json');

// Connexion à la BDD
$host = "127.0.0.1";
$db   = "gestion_examens_db";
$user = "root";   // adapte selon ton config
$pass = "";       // adapte selon ton config
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch(Exception $e) {
    echo json_encode([]);
    exit();
}

// Récupérer tous les départements
$deps = $pdo->query("SELECT id, nom FROM departements")->fetchAll();

$result = [];

foreach($deps as $dep) {
    $dep_id = $dep['id'];
    $dep_nom = $dep['nom'];

    // Nombre total d'examens pour le département
    $stmt = $pdo->prepare("
        SELECT COUNT(e.id) as total_examens
        FROM examens e
        JOIN modules m ON e.module_id = m.id
        JOIN formations f ON m.formation_id = f.id
        WHERE f.dept_id = ?
    ");
    $stmt->execute([$dep_id]);
    $total_examens = $stmt->fetchColumn();

    // Nombre de conflits pour le département
    // Ici, on considère un conflit si un prof a plus de 3 examens ou une salle est doublement utilisée
    // Tu peux adapter selon ta logique exacte
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as conflits
        FROM (
            SELECT e1.id
            FROM examens e1
            JOIN modules m1 ON e1.module_id = m1.id
            JOIN formations f1 ON m1.formation_id = f1.id
            JOIN examens e2 ON e1.date_examen = e2.date_examen AND e1.heure_debut = e2.heure_debut
            JOIN modules m2 ON e2.module_id = m2.id
            WHERE f1.dept_id = ?
              AND e1.id <> e2.id
              AND (e1.salle_id = e2.salle_id OR e1.prof_id = e2.prof_id)
        ) sub
    ");
    $stmt->execute([$dep_id]);
    $conflits = $stmt->fetchColumn();

    $taux = $total_examens > 0 ? round(($conflits / $total_examens) * 100, 1) : 0;

    $result[] = [
        "nom" => $dep_nom,
        "conflits" => (int)$conflits,
        "examens" => (int)$total_examens,
        "taux" => $taux
    ];
}

echo json_encode($result);
