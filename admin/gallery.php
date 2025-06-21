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
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        if (empty($title)) {
            $error = "Le titre est obligatoire.";
        } else {
            // Gestion de l'upload d'image
            $image_path = '';
            $upload_success = true;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../images/gallery/';
                
                // Créer le répertoire s'il n'existe pas
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Vérifier le type de fichier
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $error = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
                    $upload_success = false;
                }
                
                // Déplacer le fichier uploadé
                if ($upload_success && move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'images/gallery/' . $file_name;
                } else {
                    $error = "Erreur lors de l'upload de l'image.";
                    $upload_success = false;
                }
            }
            
            if ($upload_success) {
                // Mise à jour ou insertion
                if ($id > 0) {
                    if (!empty($image_path)) {
                        // Si une nouvelle image est uploadée, mettre à jour le chemin
                        $stmt = $pdo->prepare("UPDATE gallery SET title = :title, description = :description, image_path = :image_path WHERE id = :id");
                        $stmt->bindParam(':image_path', $image_path);
                    } else {
                        // Sinon, garder l'ancien chemin
                        $stmt = $pdo->prepare("UPDATE gallery SET title = :title, description = :description WHERE id = :id");
                    }
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $action = "modifiée";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path) VALUES (:title, :description, :image_path)");
                    $stmt->bindParam(':image_path', $image_path);
                    $action = "ajoutée";
                }
                
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                
                if ($stmt->execute()) {
                    $message = "Image $action avec succès.";
                    $editId = 0; // Réinitialiser le mode édition
                } else {
                    $error = "Erreur lors de l'enregistrement de l'image.";
                }
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
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" value="<?php echo $editData ? htmlspecialchars($editData['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo $editData ? htmlspecialchars($editData['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" <?php echo $editId > 0 ? '' : 'required'; ?>>
                    <?php if ($editData && !empty($editData['image_path'])): ?>
                        <p>Image actuelle: <a href="../<?php echo htmlspecialchars($editData['image_path']); ?>" target="_blank"><?php echo htmlspecialchars($editData['image_path']); ?></a></p>
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
