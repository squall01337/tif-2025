<?php
// Page de gestion des résultats
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
    
    // Suppression d'un résultat
    if ($deleteId > 0) {
        $stmt = $pdo->prepare("DELETE FROM results WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "Résultat supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression du résultat.";
        }
    }
    
    // Traitement du formulaire d'ajout/modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $day = $_POST['day'];
        $date = $_POST['date'];
        $team1 = $_POST['team1'];
        $team2 = $_POST['team2'];
        $score1 = $_POST['score1'];
        $score2 = $_POST['score2'];
        $sport = $_POST['sport'];
        
        if (empty($day) || empty($date) || empty($team1) || empty($team2) || !isset($score1) || !isset($score2) || empty($sport)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            // Mise à jour ou insertion
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE results SET day = :day, date = :date, team1 = :team1, team2 = :team2, score1 = :score1, score2 = :score2, sport = :sport WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $action = "modifié";
            } else {
                $stmt = $pdo->prepare("INSERT INTO results (day, date, team1, team2, score1, score2, sport) VALUES (:day, :date, :team1, :team2, :score1, :score2, :sport)");
                $action = "ajouté";
            }
            
            $stmt->bindParam(':day', $day);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':team1', $team1);
            $stmt->bindParam(':team2', $team2);
            $stmt->bindParam(':score1', $score1);
            $stmt->bindParam(':score2', $score2);
            $stmt->bindParam(':sport', $sport);
            
            if ($stmt->execute()) {
                $message = "Résultat $action avec succès.";
                
                // La mise à jour automatique des classements a été désactivée
                // updateStandings($pdo, $team1, $team2, $score1, $score2);
                
                $editId = 0; // Réinitialiser le mode édition
            } else {
                $error = "Erreur lors de l'enregistrement du résultat.";
            }
        }
    }
    
    // Récupération des données pour l'édition
    $editData = null;
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM results WHERE id = :id");
        $stmt->bindParam(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch();
        
        if (!$editData) {
            $error = "Résultat non trouvé.";
            $editId = 0;
        }
    }
    
    // Récupération de tous les résultats
    $stmt = $pdo->prepare("SELECT * FROM results ORDER BY day, sport");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

// Fonction pour mettre à jour les classements
function updateStandings($pdo, $team1, $team2, $score1, $score2) {
    // Vérifier si les équipes existent déjà dans le classement
    $stmt = $pdo->prepare("SELECT * FROM standings WHERE team = :team");
    
    // Équipe 1
    $stmt->bindParam(':team', $team1);
    $stmt->execute();
    $team1Data = $stmt->fetch();
    
    // Équipe 2
    $stmt->bindParam(':team', $team2);
    $stmt->execute();
    $team2Data = $stmt->fetch();
    
    // Déterminer le résultat du match
    $team1Win = $score1 > $score2 ? 1 : 0;
    $team2Win = $score2 > $score1 ? 1 : 0;
    $draw = $score1 == $score2 ? 1 : 0;
    
    // Points (3 pour une victoire, 1 pour un match nul)
    $team1Points = $team1Win * 3 + $draw;
    $team2Points = $team2Win * 3 + $draw;
    
    // Mettre à jour ou créer l'équipe 1
    if ($team1Data) {
        $stmt = $pdo->prepare("UPDATE standings SET played = played + 1, wins = wins + :wins, draws = draws + :draws, losses = losses + :losses, points = points + :points WHERE team = :team");
    } else {
        $stmt = $pdo->prepare("INSERT INTO standings (team, played, wins, draws, losses, points) VALUES (:team, 1, :wins, :draws, :losses, :points)");
    }
    
    $stmt->bindParam(':team', $team1);
    $stmt->bindParam(':wins', $team1Win);
    $stmt->bindParam(':draws', $draw);
    $stmt->bindParam(':losses', $team2Win);
    $stmt->bindParam(':points', $team1Points);
    $stmt->execute();
    
    // Mettre à jour ou créer l'équipe 2
    if ($team2Data) {
        $stmt = $pdo->prepare("UPDATE standings SET played = played + 1, wins = wins + :wins, draws = draws + :draws, losses = losses + :losses, points = points + :points WHERE team = :team");
    } else {
        $stmt = $pdo->prepare("INSERT INTO standings (team, played, wins, draws, losses, points) VALUES (:team, 1, :wins, :draws, :losses, :points)");
    }
    
    $stmt->bindParam(':team', $team2);
    $stmt->bindParam(':wins', $team2Win);
    $stmt->bindParam(':draws', $draw);
    $stmt->bindParam(':losses', $team1Win);
    $stmt->bindParam(':points', $team2Points);
    $stmt->execute();
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
    <title>Administration - Résultats</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1>Gestion des Résultats</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-secondary">Déconnexion</a>
            </div>
        </div>
        
        <div class="admin-nav">
            <ul>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="schedule.php">Calendrier</a></li>
                <li><a href="results.php" class="active">Résultats</a></li>
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
            
            <h2><?php echo $editId > 0 ? 'Modifier un résultat' : 'Ajouter un résultat'; ?></h2>
            
            <form method="post" action="results.php<?php echo $editId > 0 ? '?edit=' . $editId : ''; ?>" class="admin-form">
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
                    <label for="score1">Score Équipe 1</label>
                    <input type="number" id="score1" name="score1" value="<?php echo $editData ? htmlspecialchars($editData['score1']) : '0'; ?>" min="0" required>
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
                    <label for="score2">Score Équipe 2</label>
                    <input type="number" id="score2" name="score2" value="<?php echo $editData ? htmlspecialchars($editData['score2']) : '0'; ?>" min="0" required>
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
                
                <div class="admin-actions">
                    <div class="admin-actions-left">
                        <button type="submit" class="btn btn-primary"><?php echo $editId > 0 ? 'Mettre à jour' : 'Ajouter'; ?></button>
                        <?php if ($editId > 0): ?>
                            <a href="results.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <h2>Liste des résultats</h2>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Date</th>
                        <th>Équipe 1</th>
                        <th>Score</th>
                        <th>Équipe 2</th>
                        <th>Sport</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="7">Aucun résultat trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td>Jour <?php echo htmlspecialchars($result['day']); ?></td>
                                <td><?php echo htmlspecialchars($result['date']); ?></td>
                                <td><?php echo htmlspecialchars($result['team1']); ?></td>
                                <td><?php echo htmlspecialchars($result['score1']); ?> - <?php echo htmlspecialchars($result['score2']); ?></td>
                                <td><?php echo htmlspecialchars($result['team2']); ?></td>
                                <td><?php echo htmlspecialchars($result['sport']); ?></td>
                                <td>
                                    <a href="results.php?edit=<?php echo $result['id']; ?>" class="btn btn-secondary">Modifier</a>
                                    <a href="results.php?delete=<?php echo $result['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce résultat ?')">Supprimer</a>
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
