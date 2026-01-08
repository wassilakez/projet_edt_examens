<?php
session_start();
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_examens_db");

if ($conn->connect_error) { 
    die("Erreur de connexion : " . $conn->connect_error); 
}

// --- 1. SUPPRESSIONS ---
if (isset($_GET['del_user'])) { $conn->query("DELETE FROM utilisateurs WHERE id=".$_GET['del_user']); header("Location: admin.php#users"); exit; }
if (isset($_GET['del_dept'])) { 
    $id = $_GET['del_dept'];
    $conn->query("DELETE FROM modules WHERE formation_id IN (SELECT id FROM formations WHERE dept_id=$id)");
    $conn->query("DELETE FROM formations WHERE dept_id=$id");
    $conn->query("DELETE FROM departements WHERE id=$id"); 
    header("Location: admin.php#depts"); exit;
}
if (isset($_GET['del_room'])) { $conn->query("DELETE FROM lieu_examen WHERE id=".$_GET['del_room']); header("Location: admin.php#rooms"); exit; }
if (isset($_GET['del_form'])) { $conn->query("DELETE FROM formations WHERE id=".$_GET['del_form']); header("Location: admin.php#forms"); exit; }
if (isset($_GET['del_mod']))  { $conn->query("DELETE FROM modules WHERE id=".$_GET['del_mod']); header("Location: admin.php#mods"); exit; }
if (isset($_GET['del_exam'])) { $conn->query("DELETE FROM examens WHERE id=".$_GET['del_exam']); header("Location: admin.php#exams"); exit; }

// --- 2. AJOUTS ---
if (isset($_POST['add_user'])) {
    $u = $_POST['user'];
    $sql = "INSERT INTO utilisateurs (username, password, role, nom, prenom, dept_id, formation_id) 
            VALUES ('$u', '".$_POST['password']."', '".$_POST['role']."', '".$_POST['nom']."', '".$_POST['prenom']."', ".$_POST['dept_id'].", ".$_POST['formation_id'].")";
    $conn->query($sql);
    header("Location: admin.php#users"); exit;
}
if (isset($_POST['add_dept'])) { $conn->query("INSERT INTO departements (nom) VALUES ('".$_POST['nom']."')"); header("Location: admin.php#depts"); exit; }
if (isset($_POST['add_room'])) { 
    $type = $_POST['type'];
    $cap = intval($_POST['cap']);    
    $maxCap = ($type === 'Amphi') ? 300 : 20;
    if ($cap > $maxCap) $cap = $maxCap;
    if ($cap < 1) $cap = 1;
    $conn->query("INSERT INTO lieu_examen (nom, capacite, type) VALUES ('".$_POST['nom']."', ".$cap.", '".$type."')"); 
    header("Location: admin.php#rooms"); 
    exit; 
}
if (isset($_POST['add_form'])) { $conn->query("INSERT INTO formations (nom, dept_id) VALUES ('".$_POST['nom']."', ".$_POST['dept_id'].")"); header("Location: admin.php#forms"); exit; }
if (isset($_POST['add_mod']))  { $conn->query("INSERT INTO modules (nom, credits, formation_id) VALUES ('".$_POST['nom']."', ".$_POST['cred'].", ".$_POST['form_id'].")"); header("Location: admin.php#mods"); exit; }

// --- 3. MODIFICATIONS ---
if (isset($_POST['update_user'])) {
    $sql = "UPDATE utilisateurs SET nom='".$_POST['nom']."', prenom='".$_POST['prenom']."', role='".$_POST['role']."', password='".$_POST['password']."', dept_id=".$_POST['dept_id'].", formation_id=".$_POST['formation_id']." WHERE id=".$_POST['id'];
    $conn->query($sql);
    header("Location: admin.php#users"); exit;
}
if (isset($_POST['update_dept'])) { $conn->query("UPDATE departements SET nom='".$_POST['nom']."' WHERE id=".$_POST['id']); header("Location: admin.php#depts"); exit; }
if (isset($_POST['update_room'])) { 
    $type = $_POST['type'];
    $cap = intval($_POST['cap']);
    $maxCap = ($type === 'Amphi') ? 300 : 20;
    if ($cap > $maxCap) $cap = $maxCap;
    if ($cap < 1) $cap = 1;
    $conn->query("UPDATE lieu_examen SET nom='".$_POST['nom']."', capacite=".$cap.", type='".$type."' WHERE id=".$_POST['id']); 
    header("Location: admin.php#rooms"); 
    exit; 
}
if (isset($_POST['update_form'])) { $conn->query("UPDATE formations SET nom='".$_POST['nom']."', dept_id=".$_POST['dept_id']." WHERE id=".$_POST['id']); header("Location: admin.php#forms"); exit; }
if (isset($_POST['update_mod']))  { $conn->query("UPDATE modules SET nom='".$_POST['nom']."', credits=".$_POST['cred'].", formation_id=".$_POST['form_id']." WHERE id=".$_POST['id']); header("Location: admin.php#mods"); exit; }

// --- 4. PLANIFICATION (EXAMENS) ---
if (isset($_POST['add_exam'])) {
    $m = $_POST['module_id'];
    $p = $_POST['prof_id'];
    $s = $_POST['salle_id'];
    $d = $_POST['date_examen'];
    $h = $_POST['heure_debut'];
    $dur = $_POST['duree_minutes'];

    // Vérification Conflit Salle
    $check = $conn->query("SELECT * FROM examens WHERE salle_id=$s AND date_examen='$d' AND (
        ('$h' BETWEEN heure_debut AND ADDTIME(heure_debut, SEC_TO_TIME($dur * 60))) OR 
        (ADDTIME('$h', SEC_TO_TIME($dur * 60)) BETWEEN heure_debut AND ADDTIME(heure_debut, SEC_TO_TIME($dur * 60)))
    )");

    if ($check->num_rows > 0) {
        header("Location: admin.php#exams&msg=conflit");
    } else {
        $sql = "INSERT INTO examens (module_id, prof_id, salle_id, date_examen, heure_debut, duree_minutes) 
                VALUES ($m, $p, $s, '$d', '$h', $dur)";
        $conn->query($sql);
        header("Location: admin.php#exams&msg=success");
    }
    exit;
}

if (isset($_POST['update_exam'])) {
    $sql = "UPDATE examens SET 
            module_id=".$_POST['module_id'].", 
            prof_id=".$_POST['prof_id'].", 
            salle_id=".$_POST['salle_id'].", 
            date_examen='".$_POST['date_examen']."', 
            heure_debut='".$_POST['heure_debut']."', 
            duree_minutes=".$_POST['duree_minutes']." 
            WHERE id=".$_POST['id'];
    $conn->query($sql);
    header("Location: admin.php#exams");
    exit;
}

// --- 5. CHARGEMENT DES DONNÉES ---
$users = $conn->query("SELECT u.*, d.nom as dept_nom, f.nom as formation_nom FROM utilisateurs u LEFT JOIN departements d ON u.dept_id = d.id LEFT JOIN formations f ON u.formation_id = f.id")->fetch_all(MYSQLI_ASSOC);
$depts = $conn->query("SELECT * FROM departements")->fetch_all(MYSQLI_ASSOC);
$rooms = $conn->query("SELECT * FROM lieu_examen")->fetch_all(MYSQLI_ASSOC);
$forms = $conn->query("SELECT f.*, d.nom as dept_nom FROM formations f LEFT JOIN departements d ON f.dept_id = d.id")->fetch_all(MYSQLI_ASSOC);
$mods  = $conn->query("SELECT m.*, f.nom as form_nom FROM modules m LEFT JOIN formations f ON m.formation_id = f.id")->fetch_all(MYSQLI_ASSOC);
$profs = $conn->query("SELECT * FROM utilisateurs WHERE role='professeur'")->fetch_all(MYSQLI_ASSOC);

// REQUÊTE CORRIGÉE : Ajout de la jointure formation pour le filtrage admin.html
$exams = $conn->query("SELECT e.*, m.nom as mod_nom, u.nom as prof_nom, s.nom as salle_nom, f.nom as form_nom 
                       FROM examens e 
                       JOIN modules m ON e.module_id = m.id 
                       JOIN formations f ON m.formation_id = f.id
                       JOIN utilisateurs u ON e.prof_id = u.id
                       JOIN lieu_examen s ON e.salle_id = s.id 
                       ORDER BY e.date_examen DESC, e.heure_debut ASC")->fetch_all(MYSQLI_ASSOC);

// STATS POUR LE DASHBOARD
$total_users = $conn->query("SELECT COUNT(*) as count FROM utilisateurs")->fetch_assoc()['count'];
$total_profs = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE role='professeur'")->fetch_assoc()['count'];
$total_etud  = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE role='etudiant'")->fetch_assoc()['count'];
$total_chef  = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE role='chef_dep'")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM lieu_examen")->fetch_assoc()['count'];
$total_mods  = $conn->query("SELECT COUNT(*) as count FROM modules")->fetch_assoc()['count'];
$total_form  = $conn->query("SELECT COUNT(*) as count FROM formations")->fetch_assoc()['count'];
$total_dept  = $conn->query("SELECT COUNT(*) as count FROM departements")->fetch_assoc()['count'];

include 'admin.html';
?>