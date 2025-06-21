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
                echo '<p class="president-speech-text" data-original-text="' . htmlspecialchars($speech['speech']) . '" data-no-translate="true">' . nl2br(htmlspecialchars($speech['speech'])) . '</p>';
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
    </div>
</section>
