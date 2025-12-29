<?php
session_start();
// Connexion à la base de données (Assurez-vous que le dossier 'includes' et le fichier 'connect.php' existent)
require_once('includes/connect.php');

// 1. Récupération du rôle depuis l'URL (par défaut 'etudiant')
$role_choisi = isset($_GET['role']) ? $_GET['role'] : 'etudiant';

// Configuration des icônes selon le rôle
$role_config = [
    'professeur' => ['icon' => 'fas fa-chalkboard-teacher'],
    'etudiant' => ['icon' => 'fas fa-graduation-cap'],
    'admin' => ['icon' => 'fas fa-user-cog'],
    'doyen' => ['icon' => 'fas fa-university'],
    'chefdepartement' => ['icon' => 'fas fa-clipboard-list'],
    'Utilisateur' => ['icon' => 'fas fa-user']
];

$config = isset($role_config[$role_choisi]) ? $role_config[$role_choisi] : $role_config['Utilisateur'];

// 2. Traitement de la connexion lors du clic sur le bouton
if (isset($_POST['submit_login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; 

    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            if ($user['role'] !== $role_choisi && !($user['role'] == 'admin' || $user['role'] == 'doyen')) {
                $erreur = "Accès refusé : Ce matricule n'est pas autorisé dans l'espace " . ucfirst($role_choisi) . ".";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nom'] = $user['nom'];
                
                switch ($user['role']) {
                    case 'admin': header("Location: admin/gestion.php"); break;
                    case 'doyen': header("Location: doyen/stats.php"); break;
                    case 'professeur': header("Location: professeur/planning.php"); break;
                    case 'etudiant': header("Location: etudiant/recherche.php"); break;
                    default: header("Location: index.php");
                }
                exit();
            }
        } else {
            $erreur = "Matricule inconnu ou mot de passe incorrect !";
        }
    } catch (PDOException $e) {
        $erreur = "Erreur technique : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo ucfirst($role_choisi); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page" style="
    font-family: 'Poppins', sans-serif; 
    /* AJOUT DE L'IMAGE DE FOND ICI */
    
    background-image: linear-gradient(rgba(0, 0, 0, 0.08), rgba(0, 0, 0, 0.11)), url('img.webp');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    /* FIN DU BACKGROUND */
    display: flex; 
    justify-content: center; 
    align-items: center; 
    height: 100vh; 
    margin: 0;">

    <div class="login-card" style="background: white; width: 400px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); overflow: hidden;">
        
        <div class="login-header" style="background-color: #2c0880ff; color: white; padding: 30px; text-align: center;">
            <div class="login-icon" style="font-size: 40px; margin-bottom: 10px;">
                <i class="<?php echo $config['icon']; ?>"></i>
            </div>
            <h2 style="margin: 0; font-size: 24px;">Espace <?php echo ucfirst($role_choisi); ?></h2>
            <p style="margin: 5px 0 0; opacity: 0.8;">Veuillez vous identifier</p>
        </div>
        
        <div class="login-body" style="padding: 40px;">
            <?php if(isset($erreur)): ?>
                <div class="error-message" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $erreur; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php?role=<?php echo $role_choisi; ?>">
                <div class="input-group" style="margin-bottom: 20px; position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 15px; top: 15px; color: #bdc3c7;"></i>
                    <input type="text" name="username" placeholder="Matricule ou Identifiant" required
                           style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none;">
                </div>
                
                <div class="input-group" style="margin-bottom: 30px; position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 15px; top: 15px; color: #bdc3c7;"></i>
                    <input type="password" name="password" placeholder="Mot de passe" required
                           style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none;">
                </div>
                
                <button type="submit" name="submit_login" class="login-btn"
                        style="background-color: #2a1092ff; color: white; border: none; width: 100%; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                    Se Connecter <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </button>
            </form>
            
            <div style="margin-top: 25px; text-align: center;">
                <a href="index.php" style="text-decoration: none; color: #95a5a6; font-size: 14px;">
                    <i class="fas fa-chevron-left"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

</body>
</html>