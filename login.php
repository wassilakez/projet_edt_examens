<?php
session_start();

$role_choisi = isset($_GET['role']) ? $_GET['role'] : 'Utilisateur';

$role_config = [
    'professeur' => ['icon' => 'fas fa-chalkboard-teacher'],
    'etudiant' => ['icon' => 'fas fa-graduation-cap'],
    'admin' => ['icon' => 'fas fa-user-cog'],
    'doyen' => ['icon' => 'fas fa-university'],
    'chefdepartement' => ['icon' => 'fas fa-clipboard-list'],
    'Utilisateur' => ['icon' => 'fas fa-user']
];

$config = isset($role_config[$role_choisi]) ? $role_config[$role_choisi] : $role_config['Utilisateur'];

if (isset($_POST['submit_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($password === "123") {
        $_SESSION['user_id'] = $username;
        
        if ($username == "admin") {
            header("Location: admin/gestion.php");
        } elseif ($username == "doyen") {
            header("Location: doyen/stats.php");
        } elseif (strpos($username, 'P') === 0) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo ucfirst($role_choisi); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header" style="background-color: #3498db">
                <div class="login-icon"><i class="<?php echo $config['icon']; ?>"></i></div>
                <h2>Espace <?php echo ucfirst($role_choisi); ?></h2>
                <p>Connectez-vous pour accéder à votre espace</p>
            </div>
            
            <div class="login-body">
                <?php if(isset($erreur)): ?>
                <div class="error-message">
                    <span class="error-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <?php echo $erreur; ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="login-form">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" placeholder="Matricule ou Identifiant" required>
                    </div>
                    
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" placeholder="Mot de passe" required>
                    </div>
                    
                    <button type="submit" name="submit_login" class="login-btn" style="background-color: #3498db">
                        <span>Se Connecter</span>
                        <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span>
                    </button>
                </form>
                
                <div class="login-footer">
                    <a href="index.php" class="back-link">
                        <span><i class="fas fa-arrow-left"></i></span> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>