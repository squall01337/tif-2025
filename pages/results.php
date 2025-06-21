<?php
// Page des résultats
// Utilise les fonctions de récupération de données

// Récupérer les résultats depuis la base de données
try {
    $pdo = connectDB();
    $resultsData = getResults($pdo);
    
    // Organiser les données par jour
    $resultsByDay = [];
    foreach ($resultsData as $result) {
        $day = $result['day'];
        if (!isset($resultsByDay[$day])) {
            $resultsByDay[$day] = [];
        }
        $resultsByDay[$day][] = $result;
    }
    
} catch (PDOException $e) {
    // En cas d'erreur, initialiser un tableau vide
    $resultsByDay = [];
}
?>

<section id="results" class="animate-in">
    <h2 data-translate="results">Résultats</h2>
    <div class="results-tabs">
        <button class="tab-button active" data-day="1" data-translate="day1">Jour 1</button>
        <button class="tab-button" data-day="2" data-translate="day2">Jour 2</button>
        <button class="tab-button" data-day="3" data-translate="day3">Jour 3</button>
        <button class="tab-button" data-day="4" data-translate="day4">Jour 4</button>
        <button class="tab-button" data-day="5" data-translate="day5">Jour 5</button>
    </div>
    <div class="results-content" id="resultsContent">
        <?php
        // Afficher uniquement les données de la base de données
        if (empty($resultsByDay)) {
            // Si aucun résultat n'est disponible, afficher un message
            echo '<div class="data-box results-day" data-day="1" style="display: block;">';
            echo '<p class="no-results-message" data-translate="noResultsAvailable">Aucun résultat disponible pour le moment.</p>';
            echo '</div>';
            
            // Créer des conteneurs vides pour les autres jours
            for ($day = 2; $day <= 5; $day++) {
                echo '<div class="data-box results-day" data-day="' . $day . '" style="display: none;">';
                echo '<p class="no-results-message" data-translate="noResultsAvailable">Aucun résultat disponible pour le moment.</p>';
                echo '</div>';
            }
        } else {
            // Afficher les données de la base de données
            // Créer d'abord des conteneurs vides pour tous les jours
            for ($day = 1; $day <= 5; $day++) {
                $display = ($day == 1) ? 'block' : 'none';
                echo '<div class="data-box results-day" data-day="' . $day . '" style="display: ' . $display . ';">';
                
                if (isset($resultsByDay[$day]) && !empty($resultsByDay[$day])) {
                    // Si des résultats existent pour ce jour, les afficher
                    echo '<table class="schedule-table">';
                    echo '<thead>
                            <tr>
                                <th data-translate="date">Date</th>
                                <th data-translate="match">Match</th>
                                <th data-translate="sport">Sport</th>
                                <th data-translate="result">Résultat</th>
                            </tr>
                          </thead>';
                    
                    echo '<tbody>';
                    foreach ($resultsByDay[$day] as $result) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($result['date']) . '</td>';
                        echo '<td>
                                <span data-match-country="' . htmlspecialchars($result['team1']) . '"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="' . htmlspecialchars($result['team2']) . '"></span>
                            </td>';
                        echo '<td data-translate="' . htmlspecialchars($result['sport']) . '">' . htmlspecialchars($result['sport']) . '</td>';
                        echo '<td>' . htmlspecialchars($result['score1']) . ' - ' . htmlspecialchars($result['score2']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    // Si aucun résultat n'existe pour ce jour, afficher un message
                    echo '<p class="no-results-message" data-translate="noResultsAvailable">Aucun résultat disponible pour le moment.</p>';
                }
                
                echo '</div>';
            }
        }
        ?>
    </div>
</section>
