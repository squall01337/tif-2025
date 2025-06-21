<?php
// Vérification de l'authentification pour toutes les pages d'administration
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: index.php');
        exit;
    }
}

// Rediriger vers la page de connexion si non connecté
checkAdminAuth();

// Inclure les fonctions de base de données
require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Tableau de bord</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Tableau de bord d'administration</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                <li><a href="schedule.php">Calendrier</a></li>
                <li><a href="presidents.php">Mots des présidents</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="standings.php">Classements</a></li>
                <li><a href="news.php">Actualités</a></li>
                <li><a href="gallery.php">Galerie</a></li>
                <li><a href="timeline.php">Chronologie</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <h2>Bienvenue dans l'interface d'administration</h2>
            <p>Utilisez le menu de navigation ci-dessus pour gérer les différentes sections du site.</p>
            
            <div class="admin-sections">
                <div class="admin-section-card">
                    <h3>Calendrier</h3>
                    <p>Gérer les matchs à venir du tournoi.</p>
                    <a href="schedule.php" class="btn btn-primary">Gérer</a>
                </div>
                
                <div class="admin-section-card">
                    <h3>Mots des présidents</h3>
                    <p>Gérer les discours des présidents des pays participants.</p>
                    <a href="presidents.php" class="btn btn-primary">Gérer</a>
                </div>
                
                <div class="admin-section-card">
                    <h3>Résultats</h3>
                    <p>Mettre à jour les résultats des matchs.</p>
                    <a href="results.php" class="btn btn-primary">Gérer</a>
                </div>
                
                <div class="admin-section-card">
                    <h3>Classements</h3>
                    <p>Gérer les classements des équipes.</p>
                    <a href="standings.php" class="btn btn-primary">Gérer</a>
                </div>
                
                <div class="admin-section-card">
                    <h3>Actualités</h3>
                    <p>Publier et gérer les actualités du tournoi.</p>
                    <a href="news.php" class="btn btn-primary">Gérer</a>
                </div>
                
                <div class="admin-section-card">
                    <h3>Galerie</h3>
                    <p>Ajouter et gérer les images de la galerie.</p>
                    <a href="gallery.php" class="btn btn-primary">Gérer</a>
                </div>
                
                <div class="admin-section-card">
                    <h3>Chronologie</h3>
                    <p>Mettre à jour la chronologie historique du tournoi.</p>
                    <a href="timeline.php" class="btn btn-primary">Gérer</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
