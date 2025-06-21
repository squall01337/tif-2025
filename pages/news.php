<?php
// Page des actualités
// Utilise les fonctions de récupération de données

// Récupérer les actualités depuis la base de données
try {
    $pdo = connectDB();
    $newsData = getNews($pdo);
} catch (PDOException $e) {
    // En cas d'erreur, initialiser un tableau vide
    $newsData = [];
}
?>

<section id="news" class="animate-in">
    <h2 data-translate="news">Dernières Actualités</h2>
    <div class="news-grid">
        <?php
        if (empty($newsData)) {
            // Si aucune actualité n'est trouvée, afficher un message
            echo '<div class="news-card" style="background: #e9f5ff; padding: 1rem; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1);">';
            echo '<div class="news-content">';
            echo '<h3 data-translate="noNewsTitle">Aucune actualité disponible</h3>';
            echo '<p data-translate="noNewsText">Aucune actualité n\'est disponible pour le moment. Veuillez revenir plus tard.</p>';
            echo '</div>';
            echo '</div>';
        } else {
            // Afficher les données de la base de données
            foreach ($newsData as $news) {
                echo '<div class="news-card" style="background: #e9f5ff; padding: 1rem; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1);">';
                echo '<div class="news-content">';
                // Utiliser class="news-title" et data-original-text pour LibreTranslate
                // Ajouter data-no-translate="true" pour empêcher explicitement translations.js de traduire cet élément
                echo '<h3 class="news-title" data-original-text="' . htmlspecialchars($news['title']) . '" data-no-translate="true">' . htmlspecialchars($news['title']) . '</h3>';
                // Même chose pour le contenu
                echo '<p class="news-content-text" data-original-text="' . htmlspecialchars($news['content']) . '" data-no-translate="true">' . nl2br(htmlspecialchars($news['content'])) . '</p>';
                echo '<div class="news-date">' . htmlspecialchars($news['date']) . '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
    </div>
</section>
