<?php
// Page de gestion des classements
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
$resetStandings = isset($_GET['reset']) && $_GET['reset'] === 'true';

// Connexion à la base de données
try {
    $pdo = connectDB();
    
    // Réinitialiser tous les classements
    if ($resetStandings) {
        $stmt = $pdo->prepare("DELETE FROM standings");
        if ($stmt->execute()) {
            $message = "Classements réinitialisés avec succès.";
        } else {
            $error = "Erreur lors de la réinitialisation des classements.";
        }
    }
    
    // Suppression d'une équipe du classement
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM standings WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "Équipe supprimée du classement avec succès.";
        } else {
            $error = "Erreur lors de la suppression de l'équipe du classement.";
        }
    }
    
    // Traitement du formulaire d'ajout/modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $team = $_POST['team'];
        $played = $_POST['played'];
        $wins = $_POST['wins'];
        $draws = $_POST['draws'];
        $losses = $_POST['losses'];
        $points = $_POST['points'];
        
        if (empty($team) || !isset($played) || !isset($wins) || !isset($draws) || !isset($losses) || !isset($points)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            // Mise à jour ou insertion
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE standings SET team = :team, played = :played, wins = :wins, draws = :draws, losses = :losses, points = :points WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $action = "modifiée";
            } else {
                $stmt = $pdo->prepare("INSERT INTO standings (team, played, wins, draws, losses, points) VALUES (:team, :played, :wins, :draws, :losses, :points)");
                $action = "ajoutée";
            }
            
            $stmt->bindParam(':team', $team);
            $stmt->bindParam(':played', $played);
            $stmt->bindParam(':wins', $wins);
            $stmt->bindParam(':draws', $draws);
            $stmt->bindParam(':losses', $losses);
            $stmt->bindParam(':points', $points);
            
            if ($stmt->execute()) {
                $message = "Équipe $action avec succès.";
                $editId = 0; // Réinitialiser le mode édition
            } else {
                $error = "Erreur lors de l'enregistrement de l'équipe.";
            }
        }
    }
    
    // Récupération des données pour l'édition
    $editData = null;
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM standings WHERE id = :id");
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Équipe non trouvée.";
            $editId = 0;
        }
    }
    
    // Récupération de tous les classements
    $stmt = $pdo->prepare("SELECT * FROM standings ORDER BY points DESC, wins DESC");
    $stmt->execute();
    $standings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

// Liste des équipes pour les formulaires
$teams = ["countryAustria", "countryFrance", "countryHungary", "countryLuxembourg", "countryGermany"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Classements</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion des Classements</h1>
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
                <li><a href="standings.php" class="active">Classements</a></li>
                <li><a href="news.php">Actualités</a></li>
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
            
            <div class="admin-actions">
                <div class="admin-actions-right">
                    <a href="standings.php?reset=true" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser tous les classements ? Cette action est irréversible.')">Réinitialiser tous les classements</a>
                </div>
            </div>
            
            <h2><?php echo $editId > 0 ? 'Modifier une équipe' : 'Ajouter une équipe'; ?></h2>
            
            <form method="post" action="standings.php<?php echo $editId > 0 ? '?edit=' . $editId : ''; ?>" class="admin-form">
                <?php if ($editId > 0): ?>
                    <input type="hidden" name="id" value="<?php echo $editId; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="team">Équipe</label>
                    <select id="team" name="team" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team; ?>" <?php echo ($editData && $editData['team'] == $team) ? 'selected' : ''; ?>>
                                <?php echo $team; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="played">Matchs joués</label>
                    <input type="number" id="played" name="played" value="<?php echo $editData ? htmlspecialchars($editData['played']) : '0'; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="wins">Victoires</label>
                    <input type="number" id="wins" name="wins" value="<?php echo $editData ? htmlspecialchars($editData['wins']) : '0'; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="draws">Matchs nuls</label>
                    <input type="number" id="draws" name="draws" value="<?php echo $editData ? htmlspecialchars($editData['draws']) : '0'; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="losses">Défaites</label>
                    <input type="number" id="losses" name="losses" value="<?php echo $editData ? htmlspecialchars($editData['losses']) : '0'; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="points">Points</label>
                    <input type="number" id="points" name="points" value="<?php echo $editData ? htmlspecialchars($editData['points']) : '0'; ?>" min="0" required>
                </div>
                
                <div class="admin-actions">
                    <div class="admin-actions-left">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="standings.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <h2>Classement actuel</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Équipe</th>
                        <th>Joués</th>
                        <th>Victoires</th>
                        <th>Nuls</th>
                        <th>Défaites</th>
                        <th>Points</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($standings)): ?>
                        <tr>
                            <td colspan="8">Aucune équipe trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($standings as $index => $team): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($team['team']); ?></td>
                                <td><?php echo htmlspecialchars($team['played']); ?></td>
                                <td><?php echo htmlspecialchars($team['wins']); ?></td>
                                <td><?php echo htmlspecialchars($team['draws']); ?></td>
                                <td><?php echo htmlspecialchars($team['losses']); ?></td>
                                <td><?php echo htmlspecialchars($team['points']); ?></td>
                                <td>
                                    <a href="standings.php?edit=<?php echo $team['id']; ?>" class="btn btn-secondary">Modifier</a>
                                    <a href="standings.php?delete=<?php echo $team['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette équipe du classement ?')">Supprimer</a>
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
