<?php
session_start();
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_examens_db");

if ($conn->connect_error) { 
    die("Erreur de connexion : " . $conn->connect_error); 
}

// --- 1. SUPPRESSIONS ---
if (isset($_GET['del_user'])) { 
    $conn->query("DELETE FROM utilisateurs WHERE id=".$_GET['del_user']); 
    header("Location: admin.php#users"); exit; 
}
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

// ... (idem pour add_dept, add_room, add_form, add_mod)

// --- 3. MODIFICATIONS ---
// --- 3. MODIFICATIONS ---
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $username = $conn->real_escape_string($_POST['username']);
    $role = $_POST['role'];
    
    // On récupère aussi dept_id et formation_id car ils sont importants pour tes 13k étudiants
    $dept_id = !empty($_POST['dept_id']) ? $_POST['dept_id'] : "NULL";
    $form_id = !empty($_POST['formation_id']) ? $_POST['formation_id'] : "NULL";

    $sql = "UPDATE utilisateurs SET 
            nom = '$nom', 
            prenom = '$prenom', 
            username = '$username', 
            role = '$role',
            dept_id = $dept_id,
            formation_id = $form_id
            WHERE id = $id";

    if ($conn->query($sql)) {
        header("Location: admin.php?success=modifié#users");
    } else {
        die("Erreur de mise à jour : " . $conn->error);
    }
    exit;
}if (isset($_POST['update_dept'])) { $conn->query("UPDATE departements SET nom='".$_POST['nom']."' WHERE id=".$_POST['id']); header("Location: admin.php#depts"); exit; }
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

// ... (idem que ton code initial)

// --- 4. PLANIFICATION (EXAMENS) ---
// Ajout / update examens (idem ton code initial)

// --- 5. CHARGEMENT DES DONNÉES ---
$users = $conn->query("SELECT u.*, d.nom as dept_nom, f.nom as formation_nom 
                       FROM utilisateurs u 
                       LEFT JOIN departements d ON u.dept_id = d.id 
                       LEFT JOIN formations f ON u.formation_id = f.id")->fetch_all(MYSQLI_ASSOC);

$depts = $conn->query("SELECT * FROM departements")->fetch_all(MYSQLI_ASSOC);
$rooms = $conn->query("SELECT * FROM lieu_examen")->fetch_all(MYSQLI_ASSOC);
$forms = $conn->query("SELECT f.*, d.nom as dept_nom FROM formations f LEFT JOIN departements d ON f.dept_id = d.id")->fetch_all(MYSQLI_ASSOC);
$mods  = $conn->query("SELECT m.*, f.nom as form_nom FROM modules m LEFT JOIN formations f ON m.formation_id = f.id")->fetch_all(MYSQLI_ASSOC);
$profs = $conn->query("SELECT * FROM utilisateurs WHERE role='professeur'")->fetch_all(MYSQLI_ASSOC);

// ✅ REQUÊTE EXAMENS CORRIGÉE : Ajout du statut pour voir validation doyen
$exams = $conn->query("
    SELECT e.*, 
           m.nom as mod_nom, 
           u.nom as prof_nom, 
           s.nom as salle_nom, 
           f.nom as form_nom,
           e.statut  -- <-- ajout du statut
    FROM examens e
    JOIN modules m ON e.module_id = m.id
    JOIN formations f ON m.formation_id = f.id
    JOIN utilisateurs u ON e.prof_id = u.id
    JOIN lieu_examen s ON e.salle_id = s.id
    ORDER BY e.date_examen DESC, e.heure_debut ASC
")->fetch_all(MYSQLI_ASSOC);

// --- STATS POUR LE DASHBOARD ---
$total_users = $conn->query("SELECT COUNT(*) as count FROM utilisateurs")->fetch_assoc()['count'];
$total_profs = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE role='professeur'")->fetch_assoc()['count'];
$total_etud  = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE role='etudiant'")->fetch_assoc()['count'];
$total_chef  = $conn->query("SELECT COUNT(*) as count FROM utilisateurs WHERE role='chef_dep'")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM lieu_examen")->fetch_assoc()['count'];
$total_mods  = $conn->query("SELECT COUNT(*) as count FROM modules")->fetch_assoc()['count'];
$total_form  = $conn->query("SELECT COUNT(*) as count FROM formations")->fetch_assoc()['count'];
$total_dept  = $conn->query("SELECT COUNT(*) as count FROM departements")->fetch_assoc()['count'];

// ⚡ Inclure le HTML de l'admin
include 'admin.html';
?>
