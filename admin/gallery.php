<?php
// Page de gestion de la galerie
session_start();

// Vérification de l'authentification
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

// Initialisation des variables
$message = '';
$error = '';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;

// Connexion à la base de données
try {
    $pdo = connectDB();
    
    // Suppression d'une image
    if ($deleteId > 0) {
        // Récupérer le chemin de l'image avant de la supprimer
        $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetch();
        
        // Supprimer l'entrée de la base de données
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            // Supprimer le fichier si un chemin existe
            if ($image && !empty($image['image_path']) && file_exists('../' . $image['image_path'])) {
                unlink('../' . $image['image_path']);
            }
            $message = "Image supprimée avec succès.";
        } else {
            $error = "Erreur lors de la suppression de l'image.";
        }
    }
    
    // Traitement du formulaire d'ajout/modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $description = $_POST['description']; // Description commune pour toutes les images d'un batch
        $upload_dir = '../images/gallery/';

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if ($id > 0) { // Mode édition - gère une seule image de remplacement
            $title = $_POST['title_hidden_edit'] ?? basename($_FILES['images']['name'][0] ?? 'Image modifiée'); // Conserve l'ancien titre ou utilise le nouveau nom de fichier
            if (isset($_FILES['images']) && $_FILES['images']['error'][0] === UPLOAD_ERR_OK) {
                $image_path = '';
                $upload_success = true;
                
                $original_file_name = $_FILES['images']['name'][0];
                $file_name = time() . '_' . basename($original_file_name);
                $target_file = $upload_dir . $file_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                    $error = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés pour " . htmlspecialchars($original_file_name) . ".";
                    $upload_success = false;
                }

                if ($upload_success && move_uploaded_file($_FILES['images']['tmp_name'][0], $target_file)) {
                    $image_path = 'images/gallery/' . $file_name;
                } else if ($upload_success) { // move_uploaded_file a échoué mais pas à cause du type
                    $error = "Erreur lors de l'upload de l'image " . htmlspecialchars($original_file_name) . ".";
                    $upload_success = false;
                }

                if ($upload_success) {
                    // Récupérer l'ancien chemin pour le supprimer si une nouvelle image est uploadée
                    $stmtOldImage = $pdo->prepare("SELECT image_path FROM gallery WHERE id = :id");
                    $stmtOldImage->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmtOldImage->execute();
                    $oldImage = $stmtOldImage->fetch();

                    $stmt = $pdo->prepare("UPDATE gallery SET description = :description, image_path = :image_path, title = :title WHERE id = :id");
                    $stmt->bindParam(':image_path', $image_path);
                    $stmt->bindParam(':title', $title); // Utilise le nom du fichier comme titre
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':description', $description);

                    if ($stmt->execute()) {
                        if ($oldImage && !empty($oldImage['image_path']) && file_exists('../' . $oldImage['image_path']) && $oldImage['image_path'] !== $image_path) {
                            unlink('../' . $oldImage['image_path']);
                        }
                        $message = "Image modifiée avec succès.";
                        $editId = 0;
                    } else {
                        $error = "Erreur lors de la mise à jour de l'image.";
                    }
                }
            } else { // Pas de nouvelle image uploadée, on met juste à jour la description
                // Le titre n'est plus modifiable directement dans le formulaire d'édition.
                // Si on voulait permettre de changer le titre sans changer l'image, il faudrait un champ titre séparé.
                // Pour l'instant, on ne change que la description.
                $stmt = $pdo->prepare("UPDATE gallery SET description = :description WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description);
                if ($stmt->execute()) {
                    $message = "Description de l'image modifiée avec succès.";
                    $editId = 0;
                } else {
                    $error = "Erreur lors de la mise à jour de la description.";
                }
            }
        } else { // Mode ajout - gère plusieurs images
            if (isset($_FILES['images'])) {
                $total_files = count($_FILES['images']['name']);
                $files_uploaded_count = 0;

                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $original_file_name = $_FILES['images']['name'][$i];
                        $file_name = time() . '_' . basename($original_file_name);
                        $target_file = $upload_dir . $file_name;
                        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                        $upload_success_current_file = true;

                        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
                            $error .= "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés. Le fichier '" . htmlspecialchars($original_file_name) . "' n'a pas été uploadé. ";
                            $upload_success_current_file = false;
                        }

                        if ($upload_success_current_file) {
                            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                                $image_path = 'images/gallery/' . $file_name;
                                $title = $original_file_name; // Utiliser le nom original du fichier comme titre

                                $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path) VALUES (:title, :description, :image_path)");
                                $stmt->bindParam(':title', $title);
                                $stmt->bindParam(':description', $description);
                                $stmt->bindParam(':image_path', $image_path);

                                if ($stmt->execute()) {
                                    $files_uploaded_count++;
                                } else {
                                    $error .= "Erreur lors de l'enregistrement de l'image '" . htmlspecialchars($original_file_name) . "' dans la base de données. ";
                                    // Optionnel: supprimer le fichier si l'insertion DB échoue
                                    if(file_exists($target_file)) unlink($target_file);
                                }
                            } else {
                                $error .= "Erreur lors de l'upload du fichier '" . htmlspecialchars($original_file_name) . "'. ";
                            }
                        }
                    } elseif ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                        $error .= "Erreur avec le fichier '" . htmlspecialchars($_FILES['images']['name'][$i]) . "': code " . $_FILES['images']['error'][$i] . ". ";
                    }
                }

                if ($files_uploaded_count > 0) {
                    $message = "$files_uploaded_count image(s) ajoutée(s) avec succès.";
                    if (!empty($error)) { // S'il y a eu des erreurs partielles
                        $message .= " Certaines images n'ont pas pu être uploadées. Voir les erreurs ci-dessous.";
                    }
                } elseif (empty($error) && $total_files > 0 && $files_uploaded_count == 0) {
                     // Ce cas peut arriver si aucun fichier n'est sélectionné mais que le formulaire est soumis
                     // ou si tous les fichiers ont échoué avant même la tentative d'enregistrement DB.
                     if ($total_files > 0 && $_FILES['images']['error'][0] === UPLOAD_ERR_NO_FILE && $total_files === 1){
                         $error = "Aucun fichier n'a été sélectionné.";
                     } else if (empty($error)) { // Si $error est toujours vide, cela signifie qu'aucun fichier n'a été traité avec succès.
                         $error = "Aucune image n'a pu être traitée. Vérifiez les fichiers et réessayez.";
                     }
                } else if (empty($error) && $total_files == 0) {
                     $error = "Aucun fichier n'a été envoyé.";
                }

            } else {
                $error = "Aucune image n'a été sélectionnée pour l'upload.";
            }
        }
    }
    
    // Récupération des données pour l'édition
    $editData = null;
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = :id");
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Image non trouvée.";
            $editId = 0;
        }
    }
    
    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;
    
    // Compter le nombre total d'images
    $countStmt = $pdo->query("SELECT COUNT(*) FROM gallery");
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Récupération des images pour la page actuelle
    $stmt = $pdo->prepare("SELECT * FROM gallery ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $galleryItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Galerie</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion de la Galerie</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="schedule.php">Calendrier</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="standings.php">Classements</a></li>
                <li><a href="news.php">Actualités</a></li>
                <li><a href="gallery.php" class="active">Galerie</a></li>
                <li><a href="timeline.php">Chronologie</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <h2><?php echo $editId > 0 ? 'Modifier une image' : 'Ajouter une image'; ?></h2>
            
            <form method="post" action="gallery.php<?php echo $editId > 0 ? '?edit=' . $editId : ''; ?>" class="admin-form" enctype="multipart/form-data">
                <?php if ($editId > 0): ?>
                    <input type="hidden" name="id" value="<?php echo $editId; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo $editData ? htmlspecialchars($editData['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="images">Images</label>
                    <input type="file" id="images" name="images[]" multiple <?php echo $editId > 0 ? '' : 'required'; ?>>
                    <?php if ($editData && !empty($editData['image_path'])): ?>
                        <p>Image actuelle: <a href="../<?php echo htmlspecialchars($editData['image_path']); ?>" target="_blank"><?php echo htmlspecialchars($editData['image_path']); ?></a></p>
                        <p><i>Note: Si vous téléchargez une nouvelle image ici en mode édition, elle remplacera l'image actuelle. Pour ajouter plusieurs nouvelles images, annulez l'édition et utilisez le formulaire d'ajout.</i></p>
                    <?php endif; ?>
                </div>
                
                <div class="admin-actions">
                    <div class="admin-actions-left">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="gallery.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <h2>Liste des images</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($galleryItems)): ?>
                        <tr>
                            <td colspan="3">Aucune image trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($galleryItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td>
                                    <?php if (!empty($item['image_path'])): ?>
                                        <a href="../<?php echo htmlspecialchars($item['image_path']); ?>" target="_blank">Voir l'image</a>
                                    <?php else: ?>
                                        Aucune image
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="gallery.php?edit=<?php echo $item['id']; ?>" class="btn btn-secondary">Modifier</a>
                                    <a href="gallery.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="gallery.php?page=<?php echo $i; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
