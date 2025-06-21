<?php
// Page de la chronologie
// Utilise les fonctions de récupération de données

// Récupérer les données de la chronologie depuis la base de données
try {
    $pdo = connectDB();
    $timelineData = getTimeline($pdo);
} catch (PDOException $e) {
    // En cas d'erreur, initialiser un tableau vide
    $timelineData = [];
}
?>

<section id="timeline" class="animate-in">
    <h2 data-translate="timeline">Au fil des ans</h2>
    <div class="timeline-container data-box">
        <?php
        if (empty($timelineData)) {
            // Si aucune donnée n'est trouvée, afficher un message
            echo '<div class="timeline-item">';
            echo '<span class="timeline-date">-</span>';
            echo '<p data-translate="noTimelineData">Aucune donnée historique n\'est disponible pour le moment.</p>';
            echo '</div>';
        } else {
            // Afficher les données de la base de données
            foreach ($timelineData as $item) {
                echo '<div class="timeline-item">';
                echo '<span class="timeline-date">' . htmlspecialchars($item['year']) . '</span>';
                // Ajouter les classes et attributs pour la traduction dynamique
                echo '<p class="timeline-content-text" data-original-text="' . htmlspecialchars($item['content']) . '" data-no-translate="true">' . nl2br(htmlspecialchars($item['content'])) . '</p>';
                echo '</div>';
            }
        }
        ?>
    </div>
</section>
