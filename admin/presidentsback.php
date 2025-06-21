<?php
// Page d'administration des discours des présidents
// Vérification de l'authentification pour toutes les pages d'administration
session_start();

// Fonction pour vérifier si l'utilisateur est connecté
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

// Traitement des actions
$message = '';
$error = '';

// Récupérer les données pour l'édition
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editData = null;

if ($editId > 0) {
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT * FROM president_speeches WHERE id = ?");
        $stmt->execute([$editId]);
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Discours introuvable.";
            $editId = 0;
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des données: " . $e->getMessage();
    }
}

// Traitement de la suppression
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("DELETE FROM president_speeches WHERE id = ?");
        $stmt->execute([$deleteId]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Discours supprimé avec succès.";
        } else {
            $error = "Discours introuvable.";
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Traitement de l'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $presidentName = isset($_POST['president_name']) ? trim($_POST['president_name']) : '';
    $speech = isset($_POST['speech']) ? trim($_POST['speech']) : '';
    $position = isset($_POST['position']) ? (int)$_POST['position'] : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validation
    if (empty($country) || empty($presidentName) || empty($speech)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        try {
            $pdo = connectDB();
            
            if ($id > 0) {
                // Mise à jour
                $stmt = $pdo->prepare("UPDATE president_speeches SET country = ?, president_name = ?, speech = ?, position = ? WHERE id = ?");
                $stmt->execute([$country, $presidentName, $speech, $position, $id]);
                $message = "Discours mis à jour avec succès.";
            } else {
                // Ajout
                $stmt = $pdo->prepare("INSERT INTO president_speeches (country, president_name, speech, position) VALUES (?, ?, ?, ?)");
                $stmt->execute([$country, $presidentName, $speech, $position]);
                $message = "Discours ajouté avec succès.";
            }
            
            // Réinitialiser les données d'édition
            $editId = 0;
            $editData = null;
        } catch (PDOException $e) {
            $error = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    }
}

// Récupérer tous les discours
try {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT * FROM president_speeches ORDER BY position ASC");
    $speeches = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données: " . $e->getMessage();
    $speeches = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Mots des présidents</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion des mots des présidents</h1>
            <div>
                <a href="dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
                <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="schedule.php">Calendrier</a></li>
                <li><a href="presidents.php" class="active">Mots des présidents</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="standings.php">Classements</a></li>
                <li><a href="news.php">Actualités</a></li>
                <li><a href="gallery.php">Galerie</a></li>
                <li><a href="timeline.php">Chronologie</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="admin-form-container">
                <h2><?php echo $editId > 0 ? 'Modifier un discours' : 'Ajouter un discours'; ?></h2>
                <form method="post" action="presidents.php" class="admin-form">
                    <input type="hidden" name="id" value="<?php echo $editId; ?>">
                    
                    <div class="form-group">
                        <label for="country">Pays:</label>
                        <input type="text" id="country" name="country" value="<?php echo $editData ? htmlspecialchars($editData['country']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="president_name">Nom du président:</label>
                        <input type="text" id="president_name" name="president_name" value="<?php echo $editData ? htmlspecialchars($editData['president_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="speech">Discours:</label>
                        <textarea id="speech" name="speech" rows="10" required><?php echo $editData ? htmlspecialchars($editData['speech']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="number" id="position" name="position" value="<?php echo $editData ? (int)$editData['position'] : count($speeches) + 1; ?>" min="1">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="presidents.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="admin-table-container">
                <h2>Discours existants</h2>
                <?php if (empty($speeches)): ?>
                    <p>Aucun discours n'a été ajouté.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Pays</th>
                                <th>Président</th>
                                <th>Discours (extrait)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($speeches as $speech): ?>
                                <tr>
                                    <td><?php echo (int)$speech['position']; ?></td>
                                    <td><?php echo htmlspecialchars($speech['country']); ?></td>
                                    <td><?php echo htmlspecialchars($speech['president_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($speech['speech'], 0, 100)) . '...'; ?></td>
                                    <td>
                                        <a href="presidents.php?edit=<?php echo $speech['id']; ?>" class="btn btn-small">Modifier</a>
                                        <a href="presidents.php?delete=<?php echo $speech['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce discours?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
