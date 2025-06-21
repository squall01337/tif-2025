<?php
// Fichier d'inclusion pour l'en-tête du site
session_start();

// Configuration de la base de données
$db_config = [
    'host' => 'ms8063-001.eu.clouddb.ovh.net:35606',
    'dbname' => 'atscafapp',
    'username' => 'bukoapp',
    'password' => 'Sephicarotte13370405'
];

// Inclure les fonctions de gestion du contenu
require_once __DIR__ . '/functions.php';

// Fonction pour se connecter à la base de données
function connectDB() {
    global $db_config;
    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // En production, on pourrait logger l'erreur plutôt que de l'afficher
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

// Fonction pour récupérer la page actuelle
function getCurrentPage() {
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
    $allowed_pages = ['home', 'schedule', 'presidents', 'results', 'standings', 'news', 'gallery', 'timeline'];
    
    if (!in_array($page, $allowed_pages)) {
        $page = 'home';
    }
    
    return $page;
}

// Fonction pour récupérer la langue actuelle
function getCurrentLanguage() {
    return isset($_SESSION['language']) ? $_SESSION['language'] : 'fr';
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <base href="." />
    <title>International Sports Tournament</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive-consolidated.css" rel="stylesheet">
    <link href="css/responsive-logo.css" rel="stylesheet">
    <link href="css/discipline-rankings.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <!-- Logo desktop -->
    <img src="https://portail.atscaf.fr/uploads/2025/06/Logotif.png" alt="ATSCAF logo" class="logo logo-desktop">
    <!-- Logo mobile -->
    <img src="https://portail.atscaf.fr/uploads/2025/05/Logotifrespo.png" alt="ATSCAF logo mobile" class="logo logo-mobile">
    
    <div class="language-selector">
        <select id="languageSelect">
            <option value="fr">Français</option>
            <option value="en">English</option>
            <option value="de">Deutsch</option>
            <option value="hu">Magyar</option>
            <option value="nl">Nederlands</option>
        </select>
    </div>
    <h1 data-translate="tournamentTitle">International Sports Tournament 2025</h1>
    <p data-translate="participatingCountries">Austria • France • Hungary • Luxembourg • Germany</p>
</header>

<nav class="nav">
    <ul>
        <li><a href="index.php?page=home" <?php echo (getCurrentPage() == 'home') ? 'class="active"' : ''; ?> data-translate="home">Accueil</a></li>
        <li><a href="index.php?page=schedule" <?php echo (getCurrentPage() == 'schedule') ? 'class="active"' : ''; ?> data-translate="schedule">Calendrier</a></li>
        <li><a href="index.php?page=presidents" <?php echo (getCurrentPage() == 'presidents') ? 'class="active"' : ''; ?> data-translate="presidents">Les mots des présidents</a></li>
        <li><a href="index.php?page=results" <?php echo (getCurrentPage() == 'results') ? 'class="active"' : ''; ?> data-translate="results">Résultats</a></li>
        <li><a href="index.php?page=standings" <?php echo (getCurrentPage() == 'standings') ? 'class="active"' : ''; ?> data-translate="standings">Classement</a></li>
        <li><a href="index.php?page=news" <?php echo (getCurrentPage() == 'news') ? 'class="active"' : ''; ?> data-translate="news">Actualités</a></li>
        <li><a href="index.php?page=gallery" <?php echo (getCurrentPage() == 'gallery') ? 'class="active"' : ''; ?> data-translate="gallery">Galerie</a></li>
        <li><a href="index.php?page=timeline" <?php echo (getCurrentPage() == 'timeline') ? 'class="active"' : ''; ?> data-translate="timeline">Au fil des ans</a></li>
    </ul>
</nav>

<main class="content">

