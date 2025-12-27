<?php
// Simulation d'une base de données pour le test
// La Personne 4 remplacera cela par une vraie requête SQL plus tard
if (isset($_POST['submit_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Logique de redirection basée sur les identifiants
    if ($username == "admin") {
        header("Location: admin/gestion.php");
        exit();
    } elseif ($username == "doyen") {
        header("Location: doyen/stats.php");
        exit();
    } elseif (strpos($username, 'P') === 0) {
        // Si le matricule commence par P (ex: P2024), c'est un Professeur
        header("Location: professeur/planning.php");
        exit();
    } else {
        // Par défaut, on considère que c'est un étudiant
        header("Location: etudiant/recherche.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Université</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container" style="display: flex; justify-content: center; align-items: center; height: 100vh;">
        <div class="card" style="width: 400px; padding: 30px; text-align: center;">
            <h2>Authentification</h2>
            <p>Accès sécurisé à la plateforme</p>
            
            <form action="login.php" method="POST" style="margin-top: 20px;">
                <div style="margin-bottom: 15px; text-align: left;">
                    <label>Identifiant (Matricule)</label>
                    <input type="text" name="username" placeholder="Entrez votre ID" required 
                           style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px; text-align: left;">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required 
                           style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <button type="submit" name="submit_login" class="button-link" style="width: 100%; border: none; cursor: pointer;">
                    Se connecter
                </button>
            </form>
            
            <p style="margin-top: 15px;">
                <a href="index.php" style="color: #666; font-size: 0.9em;">Retour à l'accueil</a>
            </p>
        </div>
    </div>
</body>
</html>