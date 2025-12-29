<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Examens - Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* RESET & BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

         body {
    font-family: 'Poppins', sans-serif;
    /* Mise à jour du chemin et ajout des réglages de taille */
    background-image: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.15)), url('index.avif'); /* Retrait du / si l'image est à côté du fichier */
    
    background-attachment: fixed;      /* L'image reste fixe lors du scroll */
    background-size: cover;            /* L'image couvre tout l'écran */
    background-position: center;       /* L'image est centrée */
    background-repeat: no-repeat;      /* Évite les répétitions */
    color: #2c3e50;
    min-height: 100vh;
}

        /* HEADER */
        header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        header h1 {
            font-size: 1.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* CONTAINER */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-text h2 {
            color: #2c3e50;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        /* GRID DES PROFILS */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            width: 100%;
            max-width: 1000px;
        }

        /* CARTES DE PROFIL */
        .profile-card {
            background: white;
            padding: 40px 20px;
            border-radius: 20px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-card i {
            font-size: 3rem;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .profile-card h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
        }

        /* COULEURS SPECIFIQUES PAR PROFIL */
        /* Étudiant */
        .student { border-bottom: 5px solid #3498db; }
        .student i { color: #3498db; }
        
        /* Professeur */
        .teacher { border-bottom: 5px solid #2ecc71; }
        .teacher i { color: #2ecc71; }
        
        /* Admin */
        .admin { border-bottom: 5px solid #e74c3c; }
        .admin i { color: #e74c3c; }
        
        /* Doyen */
        .doyen { border-bottom: 5px solid #f1c40f; }
        .doyen i { color: #f1c40f; }

        /* EFFET HOVER */
        .profile-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            background: #fff;
        }

        .profile-card:hover i {
            transform: scale(1.1);
        }

        /* FOOTER */
        footer {
            padding: 20px;
            text-align: center;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        /* RESPONSIVE */
        @media (max-width: 600px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1><i class="fas fa-university me-2"></i> Université de Recherche</h1>
    </header>

    <main class="main-content">
        <div class="welcome-text">
            <h2>Portail de Gestion des Examens</h2>
            <p>Choisissez votre profil pour accéder à votre espace personnalisé</p>
        </div>

        <div class="profile-grid">
            <a href="login.php?role=etudiant" class="profile-card student">
                <i class="fas fa-user-graduate"></i>
                <h3>Espace Étudiant</h3>
            </a>

            <a href="login.php?role=professeur" class="profile-card teacher">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Espace Professeur</h3>
            </a>

            <a href="login.php?role=admin" class="profile-card admin">
                <i class="fas fa-user-shield"></i>
                <h3>Espace Admin</h3>
            </a>

            <a href="login.php?role=doyen" class="profile-card doyen">
                <i class="fas fa-user-tie"></i>
                <h3>Espace Doyen</h3>
            </a>
        </div>
    </main>

    <footer>
        &copy; 2025 Système de Gestion Académique - Tous droits réservés
    </footer>

</body>
</html>