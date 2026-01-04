<?php 
header('Content-Type: application/json');

$host = '127.0.0.1';
$db   = 'gestion_examens_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn,$user,$pass,$options);

    $sql = "SELECT 
                d.nom AS departement,
                f.nom AS specialite,
                CASE 
                    WHEN f.nom LIKE 'Licence%' THEN 'Licence'
                    WHEN f.nom LIKE 'Master%' THEN 'Master'
                    ELSE 'Autre'
                END AS niveau,
                m.nom AS module,
                e.date_examen,
                l.nom AS salle,
                CONCAT(u.nom,' ',u.prenom) AS surveillant,
                IFNULL(e.statut,'EN_ATTENTE') AS statut
            FROM formations f
            LEFT JOIN departements d ON d.id = f.dept_id
            LEFT JOIN modules m ON m.formation_id = f.id
            LEFT JOIN examens e ON e.module_id = m.id
            LEFT JOIN utilisateurs u ON u.id = e.prof_id
            LEFT JOIN lieu_examen l ON l.id = e.salle_id
            ORDER BY d.nom, niveau, specialite, e.date_examen";

    $stmt=$pdo->query($sql);
    $data=$stmt->fetchAll();
    echo json_encode($data);

} catch (\PDOException $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
