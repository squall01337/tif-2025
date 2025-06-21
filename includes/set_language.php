<?php
// Fichier pour enregistrer la préférence de langue dans la session
session_start();

if (isset($_POST['language'])) {
    $_SESSION['language'] = $_POST['language'];
    echo "Language set to: " . $_SESSION['language'];
} else {
    echo "No language parameter provided";
}
?>
