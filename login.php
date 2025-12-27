<?php
session_start();

// On récupère le rôle choisi sur l'index (par défaut 'Utilisateur')
$role_choisi = isset($_GET['role']) ? $_GET['role'] : 'Utilisateur';

if (isset($_POST['submit_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simulation de sécurité : Mot de passe "123" pour tout le monde pour l'instant
    if ($password === "123") {
        $_SESSION['user_id'] = $username;
        
        // Redirection intelligente selon le matricule ou le rôle
        if ($username == "admin") {
            header("Location: admin/gestion.php");
        } elseif ($username == "doyen") {
            header("Location: doyen/stats.php");
        } elseif (strpos($username, 'P') === 0) { // Si commence par P
            header("Location: professeur/planning.php");
        } else {
            header("Location: etudiant/recherche.php");
        }
        exit();
    } else {
        $erreur = "Identifiant ou mot de passe incorrect !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - <?php echo ucfirst($role_choisi); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container" style="text-align:center; margin-top: 100px;">
        <h2>Connexion : Espace <?php echo ucfirst($role_choisi); ?></h2>
        
        <?php if(isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Matricule ou Identifiant" required><br><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br><br>
            <button type="submit" name="submit_login" class="button-link">Se Connecter</button>
        </form>
        <br>
        <a href="index.php">Retour à l'accueil</a>
    </div>
</body>
</html>