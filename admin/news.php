<?php
// Page de gestion des actualités
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
    
    // Suppression d'une actualité
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "Actualité supprimée avec succès.";
        } else {
            $error = "Erreur lors de la suppression de l'actualité.";
        }
    }
    
    // Traitement du formulaire d'ajout/modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = $_POST['title'];
        $content = $_POST['content'];
        $date = $_POST['date'];
        
        if (empty($title) || empty($content) || empty($date)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            // Mise à jour ou insertion
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE news SET title = :title, content = :content, date = :date WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $action = "modifiée";
            } else {
                $stmt = $pdo->prepare("INSERT INTO news (title, content, date) VALUES (:title, :content, :date)");
                $action = "ajoutée";
            }
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':date', $date);
            
            if ($stmt->execute()) {
                $message = "Actualité $action avec succès.";
                $editId = 0; // Réinitialiser le mode édition
            } else {
                $error = "Erreur lors de l'enregistrement de l'actualité.";
            }
        }
    }
    
    // Récupération des données pour l'édition
    $editData = null;
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Actualité non trouvée.";
            $editId = 0;
        }
    }
    
    // Récupération de toutes les actualités
    $stmt = $pdo->prepare("SELECT * FROM news ORDER BY date DESC");
    $stmt->execute();
    $newsItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Actualités</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion des Actualités</h1>
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
                <li><a href="news.php" class="active">Actualités</a></li>
                <li><a href="gallery.php">Galerie</a></li>
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
            
            <h2><?php echo $editId > 0 ? 'Modifier une actualité' : 'Ajouter une actualité'; ?></h2>
            
            <form method="post" action="news.php<?php echo $editId > 0 ? '?edit=' . $editId : ''; ?>" class="admin-form">
                <?php if ($editId > 0): ?>
                    <input type="hidden" name="id" value="<?php echo $editId; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" value="<?php echo $editData ? htmlspecialchars($editData['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Contenu</label>
                    <textarea id="content" name="content" required><?php echo $editData ? htmlspecialchars($editData['content']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="date">Date (format: JJ/MM/AAAA)</label>
                    <input type="text" id="date" name="date" value="<?php echo $editData ? htmlspecialchars($editData['date']) : date('d/m/Y'); ?>" required>
                </div>
                
                <div class="admin-actions">
                    <div class="admin-actions-left">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="news.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <h2>Liste des actualités</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($newsItems)): ?>
                        <tr>
                            <td colspan="3">Aucune actualité trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($newsItems as $news): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($news['title']); ?></td>
                                <td><?php echo htmlspecialchars($news['date']); ?></td>
                                <td>
                                    <a href="news.php?edit=<?php echo $news['id']; ?>" class="btn btn-secondary">Modifier</a>
                                    <a href="news.php?delete=<?php echo $news['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
