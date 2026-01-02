<?php
// Empêcher le cache pour que les champs restent vides après une déconnexion
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
require_once('includes/connect.php');

$erreur = null;

if (isset($_POST['submit_login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; 

    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['dept_id'] = $user['dept_id'];

            switch ($user['role']) {
                case 'admin': header("Location: admin/gestion.php"); break;
                case 'chef_dep': // <-- On ajoute ce cas
 header("Location: chefdepartement/chef.php"); 
        break;
                case 'doyen': header("Location: doyen/stats.php"); break;
                case 'professeur': header("Location: professeur/planning.php"); break;
                case 'etudiant': header("Location: etudiant/recherche.php"); break;
                default: header("Location: index.php");
            }
            exit();
        } else {
            $erreur = "Identifiants incorrects !";
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
    <title>Gestion des Examens - Connexion</title>
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --danger: #e74c3c;
            --success: #2ecc71;
            --text-light: #7f8c8d;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('index.avif');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            color: var(--white);
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        header h1 {
            font-size: 1.5rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            width: 100%;
            max-width: 420px;
            padding: 45px 35px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .auth-card:hover {
            transform: translateY(-5px);
        }

        .auth-card i.main-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            background: #f8f9fa;
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            display: inline-block;
        }

        .auth-card h2 {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            font-size: 1.8rem;
        }

        .auth-card p {
            color: var(--text-light);
            margin-bottom: 35px;
            font-size: 0.95rem;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            transition: 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            outline: none;
            font-family: inherit;
            font-size: 0.95rem;
            transition: 0.3s;
            background: #f8fafc;
        }

        .input-group input:focus {
            border-color: var(--accent);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .input-group input:focus + i {
            color: var(--accent);
        }

        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .login-btn:hover {
            background: var(--secondary);
            box-shadow: 0 8px 15px rgba(44, 62, 80, 0.2);
        }

        .logout-btn {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: var(--danger);
            text-decoration: none;
            border: 2px solid #fee2e2;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #fff5f5;
            border-color: var(--danger);
        }

        .error-msg {
            background: #fff5f5;
            color: var(--danger);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            border-left: 4px solid var(--danger);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        footer {
            padding: 20px;
            text-align: center;
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>

    <header>
        <h1><i class="fas fa-graduation-cap me-2"></i> Portail Académique</h1>
    </header>

    <main class="main-content">
        <div class="auth-card">
            <i class="fas fa-shield-halved main-icon"></i>
            <h2>Connexion</h2>
            <p>Accédez à votre espace sécurisé</p>

            <?php if($erreur): ?>
                <div class="error-msg">
                    <i class="fas fa-circle-exclamation"></i>
                    <?php echo $erreur; ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="input-group">
                    <i class="fas fa-id-badge"></i>
                    <input type="text" name="username" placeholder="Matricule / Identifiant" required autocomplete="off">
                </div>
                
                <div class="input-group">
                    <i class="fas fa-key"></i>
                    <input type="password" name="password" placeholder="Mot de passe" required autocomplete="new-password">
                </div>

                <div class="btn-container">
                    <button type="submit" name="submit_login" class="login-btn">
                        <span>Se connecter</span>
                        <i class="fas fa-arrow-right-to-bracket"></i>
                    </button>
                    
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-power-off"></i>
                        <span>Quitter la session</span>
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        &copy; 2026 Système de Gestion des Examens • Université de Recherche
    </footer>

</body>
</html>