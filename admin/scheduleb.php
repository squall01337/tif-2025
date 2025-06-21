<?php
// Page de gestion du calendrier
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
    
    // Suppression d'un match
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "Match supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression du match.";
        }
    }
    
    // Traitement du formulaire d'ajout/modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $day = $_POST['day'];
        $date = $_POST['date'];
        $team1 = $_POST['team1'];
        $team2 = $_POST['team2'];
        $sport = $_POST['sport'];
        $time = $_POST['time'];
        
        if (empty($day) || empty($date) || empty($team1) || empty($team2) || empty($sport) || empty($time)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            // Mise à jour ou insertion
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE schedule SET day = :day, date = :date, team1 = :team1, team2 = :team2, sport = :sport, time = :time WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $action = "modifié";
            } else {
                $stmt = $pdo->prepare("INSERT INTO schedule (day, date, team1, team2, sport, time) VALUES (:day, :date, :team1, :team2, :sport, :time)");
                $action = "ajouté";
            }
            
            $stmt->bindParam(':day', $day);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':team1', $team1);
            $stmt->bindParam(':team2', $team2);
            $stmt->bindParam(':sport', $sport);
            $stmt->bindParam(':time', $time);
            
            if ($stmt->execute()) {
                $message = "Match $action avec succès.";
                $editId = 0; // Réinitialiser le mode édition
            } else {
                $error = "Erreur lors de l'enregistrement du match.";
            }
        }
    }
    
    // Récupération des données pour l'édition
    $editData = null;
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM schedule WHERE id = :id");
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Match non trouvé.";
            $editId = 0;
        }
    }
    
    // Récupération de tous les matchs
    $stmt = $pdo->prepare("SELECT * FROM schedule ORDER BY day, time");
    $stmt->execute();
    $matches = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

// Liste des équipes et des sports pour les formulaires
$teams = ["countryAustria", "countryFrance", "countryHungary", "countryLuxembourg", "countryGermany"];
$sports = ["soccer", "pingPong", "tennis", "chess"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Calendrier</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion du Calendrier</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="schedule.php" class="active">Calendrier</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="standings.php">Classements</a></li>
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
            
            <h2><?php echo $editId > 0 ? 'Modifier un match' : 'Ajouter un match'; ?></h2>
            
            <form method="post" action="schedule.php<?php echo $editId > 0 ? '?edit=' . $editId : ''; ?>" class="admin-form">
                <?php if ($editId > 0): ?>
                    <input type="hidden" name="id" value="<?php echo $editId; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="day">Jour</label>
                    <select id="day" name="day" required>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($editData && $editData['day'] == $i) ? 'selected' : ''; ?>>
                                Jour <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="text" id="date" name="date" value="<?php echo $editData ? htmlspecialchars($editData['date']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="team1">Équipe 1</label>
                    <select id="team1" name="team1" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team; ?>" <?php echo ($editData && $editData['team1'] == $team) ? 'selected' : ''; ?>>
                                <?php echo $team; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="team2">Équipe 2</label>
                    <select id="team2" name="team2" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team; ?>" <?php echo ($editData && $editData['team2'] == $team) ? 'selected' : ''; ?>>
                                <?php echo $team; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sport">Sport</label>
                    <select id="sport" name="sport" required>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?php echo $sport; ?>" <?php echo ($editData && $editData['sport'] == $sport) ? 'selected' : ''; ?>>
                                <?php echo $sport; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="time">Heure</label>
                    <input type="text" id="time" name="time" value="<?php echo $editData ? htmlspecialchars($editData['time']) : ''; ?>" required>
                </div>
                
                <div class="admin-actions">
                    <div class="admin-actions-left">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="schedule.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <h2>Liste des matchs</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Date</th>
                        <th>Équipe 1</th>
                        <th>Équipe 2</th>
                        <th>Sport</th>
                        <th>Heure</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($matches)): ?>
                        <tr>
                            <td colspan="7">Aucun match trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td>Jour <?php echo htmlspecialchars($match['day']); ?></td>
                                <td><?php echo htmlspecialchars($match['date']); ?></td>
                                <td><?php echo htmlspecialchars($match['team1']); ?></td>
                                <td><?php echo htmlspecialchars($match['team2']); ?></td>
                                <td><?php echo htmlspecialchars($match['sport']); ?></td>
                                <td><?php echo htmlspecialchars($match['time']); ?></td>
                                <td>
                                    <a href="schedule.php?edit=<?php echo $match['id']; ?>" class="btn btn-secondary">Modifier</a>
                                    <a href="schedule.php?delete=<?php echo $match['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?')">Supprimer</a>
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
