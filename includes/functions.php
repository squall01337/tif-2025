<?php
// Fichier de fonctions pour la gestion du contenu

/**
 * Récupère tous les matchs du calendrier
 * @param PDO $pdo Instance de connexion PDO
 * @param int $day Jour spécifique (optionnel)
 * @return array Tableau des matchs
 */
function getSchedule($pdo, $day = null) {
    try {
        if ($day !== null) {
            $stmt = $pdo->prepare("SELECT * FROM schedule WHERE day = :day ORDER BY time");
            $stmt->bindParam(':day', $day, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM schedule ORDER BY day, time");
        }
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du calendrier: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère tous les résultats
 * @param PDO $pdo Instance de connexion PDO
 * @param string $sport Sport spécifique (optionnel)
 * @param int $day Jour spécifique (optionnel)
 * @return array Tableau des résultats
 */
function getResults($pdo, $sport = null, $day = null) {
    try {
        $query = "SELECT * FROM results";
        $params = [];
        
        if ($sport !== null && $day !== null) {
            $query .= " WHERE sport = :sport AND day = :day";
            $params[':sport'] = $sport;
            $params[':day'] = $day;
        } elseif ($sport !== null) {
            $query .= " WHERE sport = :sport";
            $params[':sport'] = $sport;
        } elseif ($day !== null) {
            $query .= " WHERE day = :day";
            $params[':day'] = $day;
        }
        
        $query .= " ORDER BY day, sport";
        
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des résultats: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère le classement actuel
 * @param PDO $pdo Instance de connexion PDO
 * @return array Tableau du classement
 */
function getStandings($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM standings ORDER BY points DESC, wins DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du classement: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les actualités
 * @param PDO $pdo Instance de connexion PDO
 * @param int $limit Nombre d'actualités à récupérer (optionnel)
 * @return array Tableau des actualités
 */
function getNews($pdo, $limit = null) {
    try {
        $query = "SELECT * FROM news ORDER BY date DESC";
        
        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $pdo->prepare($query);
        
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des actualités: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les images de la galerie
 * @param PDO $pdo Instance de connexion PDO
 * @param int $page Numéro de page
 * @param int $itemsPerPage Nombre d'éléments par page
 * @return array Tableau contenant les images et les informations de pagination
 */
function getGallery($pdo, $page = 1, $itemsPerPage = 16) {
    try {
        // Calculer l'offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Compter le nombre total d'images
        $countStmt = $pdo->query("SELECT COUNT(*) FROM gallery");
        $totalItems = $countStmt->fetchColumn();
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Récupérer les images pour la page actuelle
        $stmt = $pdo->prepare("SELECT * FROM gallery ORDER BY id LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de la galerie: " . $e->getMessage());
        return [
            'items' => [],
            'totalItems' => 0,
            'totalPages' => 1,
            'currentPage' => 1
        ];
    }
}

/**
 * Récupère les événements de la chronologie
 * @param PDO $pdo Instance de connexion PDO
 * @return array Tableau des événements
 */
function getTimeline($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM timeline ORDER BY year");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de la chronologie: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les discours des présidents
 * @param PDO $pdo Instance de connexion PDO
 * @return array Tableau des discours
 */
function getPresidentSpeeches($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM president_speeches ORDER BY position");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des discours des présidents: " . $e->getMessage());
        return [];
    }
}

/**
 * Vérifie les identifiants d'un utilisateur administrateur
 * @param PDO $pdo Instance de connexion PDO
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe
 * @return bool|array Données de l'utilisateur si authentification réussie, false sinon
 */
function checkAdminCredentials($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification des identifiants: " . $e->getMessage());
        return false;
    }
}

/**
 * Génère un mot de passe hashé
 * @param string $password Mot de passe en clair
 * @return string Mot de passe hashé
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}
