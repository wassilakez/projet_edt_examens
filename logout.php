<?php
session_start(); // On récupère la session actuelle
require_once('includes/connect.php');

// On vide toutes les variables de session
$_SESSION = array();

// On détruit la session sur le serveur
session_destroy();

// On redirige l'utilisateur vers la page d'accueil ou de login
header("Location: index.php");
exit();
?>