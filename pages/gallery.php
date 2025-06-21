<?php
// Page de la galerie
// Utilise les fonctions de récupération de données

// Récupérer les images de la galerie depuis la base de données
try {
    $pdo = connectDB();
    $page = isset($_GET['gallery_page']) ? (int)$_GET['gallery_page'] : 1;
    $itemsPerPage = 16;
    
    // Utiliser la fonction getGallery pour récupérer les images et les informations de pagination
    $galleryData = getGallery($pdo, $page, $itemsPerPage);
    
    $totalItems = $galleryData['totalItems'];
    $totalPages = $galleryData['totalPages'];
    $galleryItems = $galleryData['items'];
    
    // Si aucune donnée n'est trouvée, utiliser les données par défaut
    if (empty($galleryItems) && $totalItems == 0) {
        $defaultGallery = true;
    } else {
        $defaultGallery = false;
    }
    
} catch (PDOException $e) {
    // En cas d'erreur, utiliser les données par défaut
    $defaultGallery = true;
    $totalPages = 4;
    $page = 1;
}
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

/* Pagination de la galerie */
.gallery-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    gap: 10px;
}

.gallery-pagination button {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    margin: 0;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.gallery-pagination button:hover:not(:disabled) {
    background-color: #45a049;
}

.gallery-pagination button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

/* Styles pour les numéros de page */
.gallery-page-numbers {
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
    
    <div class="gallery">
        <?php
        // Afficher les données par défaut si nécessaire
        if ($defaultGallery) {
            $galleryItemsTotal = 64;
            $itemsPerPage = 16;
            $totalPages = ceil($galleryItemsTotal / $itemsPerPage);
            
            $start = ($page - 1) * $itemsPerPage;
            $end = min($start + $itemsPerPage, $galleryItemsTotal);
            
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
        <a href="?page=gallery&gallery_page=<?php echo max(1, $page - 1); ?>">
            <button id="prevGallery" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>Précédent</button>
        </a>
        
        <div class="gallery-page-numbers">
            <?php
            // Afficher les numéros de page
            $maxPagesToShow = 5; // Nombre maximum de pages à afficher
            $startPage = max(1, min($page - floor($maxPagesToShow / 2), $totalPages - $maxPagesToShow + 1));
            $startPage = max(1, $startPage); // S'assurer que startPage n'est pas inférieur à 1
            $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
            
            // Afficher "..." si nécessaire au début
            if ($startPage > 1) {
                echo '<a href="?page=gallery&gallery_page=1" class="page-number">1</a>';
                if ($startPage > 2) {
                    echo '<span class="page-ellipsis">...</span>';
                }
            }
            
            // Afficher les numéros de page
            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $page) ? 'active' : '';
                echo '<a href="?page=gallery&gallery_page=' . $i . '" class="page-number ' . $activeClass . '">' . $i . '</a>';
            }
            
            // Afficher "..." si nécessaire à la fin
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span class="page-ellipsis">...</span>';
                }
                echo '<a href="?page=gallery&gallery_page=' . $totalPages . '" class="page-number">' . $totalPages . '</a>';
            }
            ?>
        </div>
        
        <a href="?page=gallery&gallery_page=<?php echo min($totalPages, $page + 1); ?>">
            <button id="nextGallery" <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>>Suivant</button>
        </a>
    </div>
</section>
