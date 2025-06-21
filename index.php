<?php
// Fichier principal qui charge les différentes pages selon le paramètre GET
require_once 'includes/header.php';

$page = getCurrentPage();
$file_path = 'pages/' . $page . '.php';

// Vérifier si le fichier existe
if (file_exists($file_path)) {
    include $file_path;
} else {
    // Page par défaut si le fichier n'existe pas
    include 'pages/home.php';
}

require_once 'includes/footer.php';
?>
