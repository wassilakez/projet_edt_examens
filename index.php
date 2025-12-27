<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Examens - Accueil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Université - Gestion des Examens</h1>
    </header>

    <div class="container" style="text-align: center; margin-top: 50px;">
        <h2>Bienvenue sur la plateforme</h2>
        <p>Veuillez sélectionner votre profil pour continuer :</p>
        
        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 30px; flex-wrap: wrap;">
            <a href="login.php?role=etudiant" class="button-link">Espace Étudiant</a>
            <a href="login.php?role=professeur" class="button-link">Espace Professeur</a>
            <a href="login.php?role=admin" class="button-link">Espace Admin</a>
            <a href="login.php?role=doyen" class="button-link">Espace Doyen</a>
        </div>
    </div>
</body>
</html>