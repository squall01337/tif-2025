<?php
// Page de la galerie avec onglets Photos et Vidéos
// Utilise les fonctions de récupération de données

// Récupérer les images de la galerie depuis la base de données
try {
    $pdo = connectDB();
    
    // Paramètres de pagination pour les photos
    $photoPage = isset($_GET['photo_page']) ? (int)$_GET['photo_page'] : 1;
    $photoItemsPerPage = 16;
    
    // Paramètres de pagination pour les vidéos
    $videoPage = isset($_GET['video_page']) ? (int)$_GET['video_page'] : 1;
    $videoItemsPerPage = 8; // Moins de vidéos par page car elles sont plus grandes
    
    // Utiliser la fonction getGallery pour récupérer les images et les informations de pagination
    $galleryData = getGallery($pdo, $photoPage, $photoItemsPerPage);
    
    $totalPhotoItems = $galleryData['totalItems'];
    $totalPhotoPages = $galleryData['totalPages'];
    $galleryItems = $galleryData['items'];
    
    // Si aucune donnée n'est trouvée, utiliser les données par défaut
    if (empty($galleryItems) && $totalPhotoItems == 0) {
        $defaultGallery = true;
    } else {
        $defaultGallery = false;
    }
    
    // Pour les vidéos, nous utilisons des données par défaut pour la démonstration
    // Dans une implémentation complète, vous utiliseriez une fonction getVideos() similaire à getGallery()
    $defaultVideos = true;
    $totalVideoPages = 2;
    
} catch (PDOException $e) {
    // En cas d'erreur, utiliser les données par défaut
    $defaultGallery = true;
    $totalPhotoPages = 4;
    $photoPage = 1;
    
    $defaultVideos = true;
    $totalVideoPages = 2;
    $videoPage = 1;
}

// Déterminer quel onglet est actif
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'photos';
?>

<style>
/* Styles pour la galerie d'images */
.gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.03);
}

.gallery-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

/* Styles pour les onglets de la galerie */
.gallery-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.gallery-tabs .tab-button {
    padding: 0.75rem 2rem;
    border: none;
    background: #f0f0f0;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 1rem;
}

.gallery-tabs .tab-button.active {
    background: #4CAF50;
    color: white;
}

/* Styles pour le contenu des onglets */
.gallery-content {
    display: none;
}

.gallery-content.active {
    display: block;
}

/* Styles pour la galerie de vidéos */
.video-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.video-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    aspect-ratio: 16/9;
}

.video-item iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Pagination de la galerie */
.gallery-pagination, .video-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    gap: 10px;
}

.gallery-pagination button, .video-pagination button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    margin: 0;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.gallery-pagination button:hover:not(:disabled), .video-pagination button:hover:not(:disabled) {
    background-color: #45a049;
}

.gallery-pagination button:disabled, .video-pagination button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

/* Styles pour les numéros de page */
.gallery-page-numbers, .video-page-numbers {
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-number {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    background-color: #f1f1f1;
    transition: all 0.3s;
    font-weight: 500;
}

.page-number:hover {
    background-color: #ddd;
}

.page-number.active {
    background-color: #4CAF50;
    color: white;
    cursor: default;
}

.page-ellipsis {
    margin: 0 2px;
    color: #666;
}
</style>

<section id="gallery" class="animate-in">
    <h2 data-translate="gallery">Galerie du Tournoi</h2>
    
    <!-- Onglets de navigation -->
    <div class="gallery-tabs">
        <a href="?page=gallery&tab=photos" class="tab-button <?php echo ($activeTab == 'photos') ? 'active' : ''; ?>" data-tab="photos" data-translate="photos">Photos</a>
        <a href="?page=gallery&tab=videos" class="tab-button <?php echo ($activeTab == 'videos') ? 'active' : ''; ?>" data-tab="videos" data-translate="videos">Vidéos</a>
    </div>
    
    <!-- Contenu de l'onglet Photos -->
    <div class="gallery-content <?php echo ($activeTab == 'photos') ? 'active' : ''; ?>" id="photos-content">
        <div class="gallery">
            <?php
            // Afficher les données par défaut si nécessaire
            if ($defaultGallery) {
                $galleryItemsTotal = 64;
                $photoItemsPerPage = 16;
                $totalPhotoPages = ceil($galleryItemsTotal / $photoItemsPerPage);
                
                $start = ($photoPage - 1) * $photoItemsPerPage;
                $end = min($start + $photoItemsPerPage, $galleryItemsTotal);
                
                for ($i = $start; $i < $end; $i++) {
                    echo '<div class="gallery-item">';
                    echo '<svg viewBox="0 0 250 250">';
                    echo '<rect width="250" height="250" fill="#cccccc"></rect>';
                    echo '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666666" font-size="20">Photo ' . ($i+1) . '</text>';
                    echo '</svg>';
                    echo '</div>';
                }
            } else {
                // Afficher les données de la base de données
                foreach ($galleryItems as $image) {
                    echo '<div class="gallery-item">';
                    if (!empty($image['image_path'])) {
                        echo '<img src="' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '">';
                    } else {
                        echo '<svg viewBox="0 0 250 250">';
                        echo '<rect width="250" height="250" fill="#cccccc"></rect>';
                        echo '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666666" font-size="20">' . htmlspecialchars($image['title']) . '</text>';
                        echo '</svg>';
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>
        
        <div class="gallery-pagination">
            <a href="?page=gallery&tab=photos&photo_page=<?php echo max(1, $photoPage - 1); ?>">
                <button id="prevGallery" <?php echo ($photoPage <= 1) ? 'disabled' : ''; ?>>Précédent</button>
            </a>
            
            <div class="gallery-page-numbers">
                <?php
                // Afficher les numéros de page
                $maxPagesToShow = 5; // Nombre maximum de pages à afficher
                $startPage = max(1, min($photoPage - floor($maxPagesToShow / 2), $totalPhotoPages - $maxPagesToShow + 1));
                $startPage = max(1, $startPage); // S'assurer que startPage n'est pas inférieur à 1
                $endPage = min($totalPhotoPages, $startPage + $maxPagesToShow - 1);
                
                // Afficher "..." si nécessaire au début
                if ($startPage > 1) {
                    echo '<a href="?page=gallery&tab=photos&photo_page=1" class="page-number">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                }
                
                // Afficher les numéros de page
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $photoPage) ? 'active' : '';
                    echo '<a href="?page=gallery&tab=photos&photo_page=' . $i . '" class="page-number ' . $activeClass . '">' . $i . '</a>';
                }
                
                // Afficher "..." si nécessaire à la fin
                if ($endPage < $totalPhotoPages) {
                    if ($endPage < $totalPhotoPages - 1) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                    echo '<a href="?page=gallery&tab=photos&photo_page=' . $totalPhotoPages . '" class="page-number">' . $totalPhotoPages . '</a>';
                }
                ?>
            </div>
            
            <a href="?page=gallery&tab=photos&photo_page=<?php echo min($totalPhotoPages, $photoPage + 1); ?>">
                <button id="nextGallery" <?php echo ($photoPage >= $totalPhotoPages) ? 'disabled' : ''; ?>>Suivant</button>
            </a>
        </div>
    </div>
    
    <!-- Contenu de l'onglet Vidéos -->
    <div class="gallery-content <?php echo ($activeTab == 'videos') ? 'active' : ''; ?>" id="videos-content">
        <div class="video-gallery">
            <?php
            // Vidéos par défaut pour la démonstration
            if ($defaultVideos) {
                $videoItemsTotal = 12;
                $videoItemsPerPage = 8;
                $totalVideoPages = ceil($videoItemsTotal / $videoItemsPerPage);
                
                $start = ($videoPage - 1) * $videoItemsPerPage;
                $end = min($start + $videoItemsPerPage, $videoItemsTotal);
                
                // Liste de vidéos YouTube de démonstration (sports)
                $demoVideos = [
                    ['id' => 'dQw4w9WgXcQ', 'title' => 'Highlights du tournoi 2024'],
                    ['id' => 'jNQXAC9IVRw', 'title' => 'Interview des participants'],
                    ['id' => 'QH2-TGUlwu4', 'title' => 'Résumé du match final'],
                    ['id' => 'ZyhrYis509A', 'title' => 'Cérémonie d\'ouverture'],
                    ['id' => 'y6120QOlsfU', 'title' => 'Meilleurs moments - Football'],
                    ['id' => 'L_jWHffIx5E', 'title' => 'Meilleurs moments - Tennis'],
                    ['id' => 'kJQP7kiw5Fk', 'title' => 'Meilleurs moments - Ping Pong'],
                    ['id' => 'fJ9rUzIMcZQ', 'title' => 'Meilleurs moments - Échecs'],
                    ['id' => 'JGwWNGJdvx8', 'title' => 'Remise des trophées'],
                    ['id' => 'YVkUvmDQ3HY', 'title' => 'Coulisses du tournoi'],
                    ['id' => 'hT_nvWreIhg', 'title' => 'Préparation des équipes'],
                    ['id' => 'CevxZvSJLk8', 'title' => 'Réactions après le tournoi']
                ];
                
                for ($i = $start; $i < $end; $i++) {
                    $video = $demoVideos[$i % count($demoVideos)];
                    echo '<div class="video-item">';
                    echo '<iframe src="https://www.youtube.com/embed/' . $video['id'] . '" title="' . htmlspecialchars($video['title']) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    echo '</div>';
                }
            } else {
                // Dans une implémentation complète, vous afficheriez ici les vidéos de la base de données
                // Code similaire à celui des photos
            }
            ?>
        </div>
        
        <div class="video-pagination">
            <a href="?page=gallery&tab=videos&video_page=<?php echo max(1, $videoPage - 1); ?>">
                <button id="prevVideo" <?php echo ($videoPage <= 1) ? 'disabled' : ''; ?>>Précédent</button>
            </a>
            
            <div class="video-page-numbers">
                <?php
                // Afficher les numéros de page
                $maxPagesToShow = 5; // Nombre maximum de pages à afficher
                $startPage = max(1, min($videoPage - floor($maxPagesToShow / 2), $totalVideoPages - $maxPagesToShow + 1));
                $startPage = max(1, $startPage); // S'assurer que startPage n'est pas inférieur à 1
                $endPage = min($totalVideoPages, $startPage + $maxPagesToShow - 1);
                
                // Afficher "..." si nécessaire au début
                if ($startPage > 1) {
                    echo '<a href="?page=gallery&tab=videos&video_page=1" class="page-number">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                }
                
                // Afficher les numéros de page
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $videoPage) ? 'active' : '';
                    echo '<a href="?page=gallery&tab=videos&video_page=' . $i . '" class="page-number ' . $activeClass . '">' . $i . '</a>';
                }
                
                // Afficher "..." si nécessaire à la fin
                if ($endPage < $totalVideoPages) {
                    if ($endPage < $totalVideoPages - 1) {
                        echo '<span class="page-ellipsis">...</span>';
                    }
                    echo '<a href="?page=gallery&tab=videos&video_page=' . $totalVideoPages . '" class="page-number">' . $totalVideoPages . '</a>';
                }
                ?>
            </div>
            
            <a href="?page=gallery&tab=videos&video_page=<?php echo min($totalVideoPages, $videoPage + 1); ?>">
                <button id="nextVideo" <?php echo ($videoPage >= $totalVideoPages) ? 'disabled' : ''; ?>>Suivant</button>
            </a>
        </div>
    </div>
</section>

<script>
// JavaScript pour la gestion des onglets est géré par les liens href
// Aucun JavaScript supplémentaire n'est nécessaire ici car nous utilisons des liens avec paramètres d'URL
</script>

