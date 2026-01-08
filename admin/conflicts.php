<?php
session_start();
require_once 'generate_timetable.php';

$conn = new mysqli("localhost", "root", "", "gestion_examens_db");

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$teacherConflicts = TimetableGenerator::detectTeacherConflicts($conn);

$savedConflicts = [];
$result = $conn->query("SELECT * FROM conflicts ORDER BY 
    CASE severity 
        WHEN 'critical' THEN 1 
        WHEN 'warning' THEN 2 
        WHEN 'info' THEN 3 
    END, created_at DESC LIMIT 50");
if ($result) {
    $savedConflicts = $result->fetch_all(MYSQLI_ASSOC);
}

$allConflicts = array_merge($teacherConflicts, $savedConflicts);

$workloadStats = [];
$result = $conn->query("SELECT u.id, u.nom, u.prenom, d.nom as dept_nom, COUNT(e.id) as total_surveillances
        FROM utilisateurs u
        LEFT JOIN examens e ON u.id = e.prof_id
        LEFT JOIN departements d ON u.dept_id = d.id
        WHERE u.role = 'professeur'
        GROUP BY u.id, u.nom, u.prenom, d.nom
        ORDER BY total_surveillances DESC");
if ($result) {
    $workloadStats = $result->fetch_all(MYSQLI_ASSOC);
}

$counts = array_column($workloadStats, 'total_surveillances');
$workloadBalance = [
    'min' => !empty($counts) ? min($counts) : 0,
    'max' => !empty($counts) ? max($counts) : 0,
    'avg' => !empty($counts) ? round(array_sum($counts) / count($counts), 1) : 0,
    'total' => array_sum($counts)
];

$criticalCount = count(array_filter($allConflicts, fn($c) => ($c['severity'] ?? '') === 'critical'));
$warningCount = count(array_filter($allConflicts, fn($c) => ($c['severity'] ?? '') === 'warning'));

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Conflits & Charge de Travail - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background: #1a1d20; color: white; position: fixed; width: 250px; z-index: 100; }
        .main { margin-left: 260px; padding: 20px; }
        .severity-critical { background: #f8d7da; }
        .severity-warning { background: #fff3cd; }
        .severity-info { background: #cff4fc; }
        .conflict-card { border-radius: 10px; margin-bottom: 15px; }
        .pulse-critical { animation: pulse-red 2s infinite; }
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        }
    </style>
</head>
<body class="bg-light">

<div class="sidebar p-3 shadow">
    <h4 class="text-center text-warning fw-bold">ADMIN PANEL</h4><hr>
    <a href="admin.php#dash" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a href="admin.php#users" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-people me-2"></i> Utilisateurs</a>
    <a href="admin.php#depts" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-diagram-3 me-2"></i> Départements</a>
    <a href="admin.php#rooms" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-building me-2"></i> Salles</a>
    <a href="admin.php#forms" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-mortarboard me-2"></i> Formations</a>
    <a href="admin.php#mods" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-book me-2"></i> Modules</a>
    <a href="admin.php#exams" class="btn btn-dark w-100 text-start mb-2"><i class="bi bi-calendar-check me-2"></i> Planification</a>
    <a href="conflicts.php" class="btn btn-warning w-100 text-start mb-2 py-2 fw-bold text-dark shadow-sm">
        <i class="bi bi-exclamation-triangle me-2"></i> Conflits & Charge
        <?php if ($criticalCount > 0): ?>
            <span class="badge bg-danger ms-1"><?= $criticalCount ?></span>
        <?php endif; ?>
    </a>
    <a href="../logout.php" class="btn btn-outline-danger w-100 mt-5"><i class="bi bi-box-arrow-left me-2"></i> Déconnexion</a>
</div>

<div class="main">
    <h2 class="mb-4"><i class="bi bi-clipboard-data text-warning"></i> Conflits & Répartition de Charge</h2>
    
    <?php if ($criticalCount > 0): ?>
    <div class="alert alert-danger pulse-critical">
        <i class="bi bi-exclamation-octagon me-2"></i>
        <strong>ATTENTION !</strong> <?= $criticalCount ?> conflit(s) critique(s) détecté(s) - des professeurs ou salles sont assignés en double !
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Surveillances</h6>
                    <h2><?= $workloadBalance['total'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h6>Moyenne/Professeur</h6>
                    <h2><?= $workloadBalance['avg'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h6>Min - Max</h6>
                    <h2><?= $workloadBalance['min'] ?> - <?= $workloadBalance['max'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm <?= ($workloadBalance['max'] - $workloadBalance['min'] <= 1) ? 'bg-success' : 'bg-warning' ?> text-white">
                <div class="card-body text-center">
                    <h6>Équilibre</h6>
                    <h2><?= ($workloadBalance['max'] - $workloadBalance['min'] <= 1) ? '✓ OK' : '⚠ Déséquilibré' ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Répartition de la Charge de Surveillance</h5>
        </div>
        <div class="card-body">
            <?php if (empty($workloadStats)): ?>
                <div class="alert alert-info">Aucun professeur trouvé.</div>
            <?php else: ?>
                <?php 
                $maxCount = max(array_column($workloadStats, 'total_surveillances'));
                $maxCount = $maxCount > 0 ? $maxCount : 1;
                foreach ($workloadStats as $prof): 
                    $percentage = ($prof['total_surveillances'] / $maxCount) * 100;
                    $isBalanced = abs($prof['total_surveillances'] - $workloadBalance['avg']) <= 1;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold">
                            <?= htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']) ?>
                            <small class="text-muted">(<?= htmlspecialchars($prof['dept_nom'] ?? 'Sans département') ?>)</small>
                        </span>
                        <span class="badge <?= $isBalanced ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= $prof['total_surveillances'] ?> surveillance(s)
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar <?= $isBalanced ? 'bg-success' : 'bg-warning' ?>" 
                             role="progressbar" 
                             style="width: <?= $percentage ?>%">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Conflits et Problèmes Détectés</h5>
            <div>
                <?php if ($criticalCount > 0): ?>
                    <span class="badge bg-light text-danger"><?= $criticalCount ?> Critique(s)</span>
                <?php endif; ?>
                <?php if ($warningCount > 0): ?>
                    <span class="badge bg-light text-warning"><?= $warningCount ?> Avertissement(s)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($allConflicts)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Aucun conflit détecté. La planification est optimale !
                </div>
            <?php else: ?>
                <?php foreach ($allConflicts as $c): ?>
                <div class="card conflict-card severity-<?= htmlspecialchars($c['severity'] ?? 'warning') ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <?php if (($c['severity'] ?? '') == 'critical'): ?>
                                    <span class="badge bg-danger mb-2"><?= htmlspecialchars($c['type'] ?? 'CONFLIT') ?></span>
                                <?php elseif (($c['severity'] ?? '') == 'warning'): ?>
                                    <span class="badge bg-warning text-dark mb-2"><?= htmlspecialchars($c['type'] ?? 'AVERTISSEMENT') ?></span>
                                <?php else: ?>
                                    <span class="badge bg-info mb-2"><?= htmlspecialchars($c['type'] ?? 'INFO') ?></span>
                                <?php endif; ?>
                                
                                <h6 class="mb-1">
                                    <?php if (isset($c['prof'])): ?>
                                        <i class="bi bi-person-fill text-primary me-1"></i><?= htmlspecialchars($c['prof']) ?>
                                    <?php elseif (isset($c['salle'])): ?>
                                        <i class="bi bi-building text-secondary me-1"></i><?= htmlspecialchars($c['salle']) ?>
                                    <?php elseif (isset($c['module'])): ?>
                                        <i class="bi bi-book text-info me-1"></i><?= htmlspecialchars($c['module']) ?>
                                    <?php endif; ?>
                                </h6>
                                
                                <p class="mb-1 text-danger fw-bold"><?= htmlspecialchars($c['reason'] ?? '') ?></p>
                                
                                <?php if (isset($c['date'])): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($c['date'])) ?>
                                        <?php if (isset($c['heure'])): ?>
                                            à <?= $c['heure'] ?>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($c['module1']) && isset($c['module2'])): ?>
                            <div class="text-end">
                                <small class="d-block text-muted">Examens en conflit:</small>
                                <span class="badge bg-secondary"><?= htmlspecialchars($c['module1']) ?></span>
                                <span class="badge bg-secondary"><?= htmlspecialchars($c['module2']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4">
        <h6>Légende des conflits :</h6>
        <div class="d-flex gap-3 flex-wrap">
            <div><span class="badge bg-warning text-dark">CONTRAINTE_ÉGALITÉ</span> - Examen non planifié pour maintenir l'égalité des charges</div>
            <div><span class="badge bg-warning text-dark">NON_PLANIFIÉ</span> - Pas de créneau/salle/professeur disponible</div>
            <div><span class="badge bg-danger">CONFLIT_PROFESSEUR</span> - Professeur assigné à 2 examens au même moment</div>
            <div><span class="badge bg-danger">CONFLIT_SALLE</span> - Salle assignée à 2 examens au même moment</div>
            <div><span class="badge bg-warning text-dark">SURCHARGE_PROFESSEUR</span> - Plus de 3 examens/jour</div>
            <div><span class="badge bg-info">DÉSÉQUILIBRE_CHARGE</span> - Distribution inégale des surveillances</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
