<?php
// Page des mots des présidents
// Utilise les fonctions de récupération de données

// Récupérer les discours des présidents depuis la base de données
try {
    $pdo = connectDB();
    $presidentsData = getPresidentSpeeches($pdo);
} catch (PDOException $e) {
    // En cas d'erreur, initialiser un tableau vide
    $presidentsData = [];
}

// Fonction pour filtrer le HTML et ne garder que les balises autorisées
function filterAllowedHtml($text) {
    $allowedTags = '<span><br>';
    return strip_tags($text, $allowedTags);
}
?>

<section id="presidents" class="animate-in">
    <h2 data-translate="presidents">Les mots des présidents</h2>
    <div class="presidents-container data-box">
        <?php
        if (empty($presidentsData)) {
            // Si aucune donnée n'est trouvée, afficher un message
            echo '<div class="president-item">';
            echo '<p data-translate="noPresidentsData">Aucun discours présidentiel n\'est disponible pour le moment.</p>';
            echo '</div>';
        } else {
            // Afficher les données de la base de données
            foreach ($presidentsData as $speech) {
                echo '<div class="president-item">';
                echo '<div class="president-header">';
                echo '<h3 class="president-country" data-original-text="' . htmlspecialchars($speech['country']) . '" data-no-translate="true">' . htmlspecialchars($speech['country']) . '</h3>';
                echo '<span class="president-name">' . htmlspecialchars($speech['president_name']) . '</span>';
                echo '</div>';
                
                // Ajouter les classes et attributs pour la traduction dynamique
                echo '<div class="president-content">';
                
                // 1. Filtrer le HTML pour ne garder que les balises autorisées
                $filteredSpeech = filterAllowedHtml($speech['speech']);
                
                // 2. Stocker le texte original sans HTML pour la traduction
                $originalText = strip_tags($speech['speech']);
                
                // 3. Utiliser <div> au lieu de <p> pour éviter les problèmes de rendu
                echo '<div class="president-speech-text" data-original-text="' . htmlspecialchars($originalText) . '" data-no-translate="true">' . nl2br($filteredSpeech) . '</div>';
                
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
    </div>
</section>

<style>
/* Style explicite pour forcer l'affichage du texte en gras */
.president-speech-text .bold,
span.bold {
    font-weight: 900 !important;
    display: inline !important;
    color: inherit !important;
    font-family: inherit !important;
    font-size: inherit !important;
    background-color: transparent !important;
}
</style>
