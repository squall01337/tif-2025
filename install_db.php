<?php
// Script pour créer les tables de la base de données

// Inclure les fonctions de base de données
require_once 'includes/header.php';

// Initialisation des variables
$message = '';
$error = '';

// Connexion à la base de données
try {
    $pdo = connectDB();
    
    // Lire le fichier SQL
    $sql = file_get_contents('database_schema.sql');
    
    // Exécuter les requêtes SQL
    $pdo->exec($sql);
    
    // Vérifier si l'utilisateur admin existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'squall009'");
    $stmt->execute();
    $userExists = (int)$stmt->fetchColumn();
    
    // Si l'utilisateur n'existe pas, l'ajouter avec un mot de passe en texte brut
    if ($userExists === 0) {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (:username, :password, :email)");
        $username = 'squall009';
        $password = 'Sephicarotte0405'; // Mot de passe en texte brut pour le test
        $email = 'admin@example.com';
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
    
    $message = "Les tables ont été créées avec succès.";
    
} catch (PDOException $e) {
    $error = "Erreur lors de la création des tables: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation de la base de données</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            background: #007bff;
            color: white;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installation de la base de données</h1>
        
        <?php if (!empty($message)): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <p>Ce script crée les tables nécessaires pour le site du tournoi.</p>
        
        <a href="index.php" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>
