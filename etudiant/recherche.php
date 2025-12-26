<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Salle - Étudiant</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Espace Étudiant</h1>
    </header>

    <div class="container">
        <a href="../index.php" style="text-decoration: none;">⬅ Retour à l'accueil</a>
        
        <div style="background: white; padding: 30px; border-radius: 10px; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            
        <h2>🔍 Trouver ma place d'examen</h2>
            <p>Saisissez votre numéro de matricule pour connaître votre affectation.</p>
            
            <form action="recherche.php" method="GET" style="margin-top: 20px;">
                <input type="text" name="matricule" placeholder="Ex: 2121350088..." required style="width: 70%; font-size: 16px;">
                <button type="submit">Rechercher</button>
            </form>
        </div>

        <div style="margin-top: 30px;">
            <h3>Votre résultat :</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Salle</th>
                        <th>Bâtiment</th>
                        <th>N° Table</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jean Dupont</td>
                        <td>Amphi A</td>
                        <td>Faculté des Sciences</td>
                        <td>124</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>