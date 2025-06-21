<?php
// Page de gestion de la chronologie
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
    
    // Suppression d'un événement
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM timeline WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "Événement supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression de l'événement.";
        }
    }
    
    // Traitement du formulaire d'ajout/modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $year = $_POST['year'];
        $content = $_POST['content'];
        
        if (empty($year) || empty($content)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            // Mise à jour ou insertion
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE timeline SET year = :year, content = :content WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $action = "modifié";
            } else {
                $stmt = $pdo->prepare("INSERT INTO timeline (year, content) VALUES (:year, :content)");
                $action = "ajouté";
            }
            
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':content', $content);
            
            if ($stmt->execute()) {
                $message = "Événement $action avec succès.";
                $editId = 0; // Réinitialiser le mode édition
            } else {
                $error = "Erreur lors de l'enregistrement de l'événement.";
            }
        }
    }
    
    // Récupération des données pour l'édition
    $editData = null;
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM timeline WHERE id = :id");
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Événement non trouvé.";
            $editId = 0;
        }
    }
    
    // Récupération de tous les événements
    $stmt = $pdo->prepare("SELECT * FROM timeline ORDER BY year");
    $stmt->execute();
    $timelineItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Chronologie</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion de la Chronologie</h1>
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
                <li><a href="gallery.php">Galerie</a></li>
                <li><a href="timeline.php" class="active">Chronologie</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <h2><?php echo $editId > 0 ? 'Modifier un événement' : 'Ajouter un événement'; ?></h2>
            
            <form method="post" action="timeline.php<?php echo $editId > 0 ? '?edit=' . $editId : ''; ?>" class="admin-form">
                <?php if ($editId > 0): ?>
                    <input type="hidden" name="id" value="<?php echo $editId; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="year">Année</label>
                    <input type="text" id="year" name="year" value="<?php echo $editData ? htmlspecialchars($editData['year']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Contenu</label>
                    <textarea id="content" name="content" required><?php echo $editData ? htmlspecialchars($editData['content']) : ''; ?></textarea>
                </div>
                
                <div class="admin-actions">
                    <div class="admin-actions-left">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="timeline.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <h2>Liste des événements</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Année</th>
                        <th>Contenu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($timelineItems)): ?>
                        <tr>
                            <td colspan="3">Aucun événement trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($timelineItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['year']); ?></td>
                                <td><?php echo htmlspecialchars(substr($item['content'], 0, 100)) . (strlen($item['content']) > 100 ? '...' : ''); ?></td>
                                <td>
                                    <a href="timeline.php?edit=<?php echo $item['id']; ?>" class="btn btn-secondary">Modifier</a>
                                    <a href="timeline.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">Supprimer</a>
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
