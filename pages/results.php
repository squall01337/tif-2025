<?php
// Page des résultats
// Utilise les fonctions de récupération de données

// Récupérer les résultats depuis la base de données
try {
    $pdo = connectDB();
    $resultsData = getResults($pdo);
    
    // Organiser les données par jour ET par sport
    $resultsByDayAndSport = [];
    if (!empty($resultsData)) {
        foreach ($resultsData as $result) {
            $day = $result['day'];
            $sport = $result['sport']; // Make sure 'sport' key exists and is suitable for keying

            if (!isset($resultsByDayAndSport[$day])) {
                $resultsByDayAndSport[$day] = [];
            }
            if (!isset($resultsByDayAndSport[$day][$sport])) {
                $resultsByDayAndSport[$day][$sport] = [];
            }
            $resultsByDayAndSport[$day][$sport][] = $result;
        }
    }
    
} catch (PDOException $e) {
    // En cas d'erreur, initialiser un tableau vide
    $resultsByDayAndSport = [];
}
?>

<section id="results" class="animate-in">
    <h2 data-translate="results">Résultats</h2>
    <div class="results-tabs"> <!-- These are DAY tabs -->
        <button class="tab-button active" data-day="1" data-translate="day1">Jour 1</button>
        <button class="tab-button" data-day="2" data-translate="day2">Jour 2</button>
        <button class="tab-button" data-day="3" data-translate="day3">Jour 3</button>
        <button class="tab-button" data-day="4" data-translate="day4">Jour 4</button>
        <button class="tab-button" data-day="5" data-translate="day5">Jour 5</button>
    </div>
    <div class="results-content" id="resultsContent">
        <?php
        // Afficher uniquement les données de la base de données
        if (empty($resultsByDayAndSport)) {
            // Si aucun résultat n'est disponible, afficher un message
            echo '<div class="data-box results-day" data-day="1" style="display: block;">'; // This is a DAY container
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
            for ($current_day_loop = 1; $current_day_loop <= 5; $current_day_loop++) {
                $display_style = ($current_day_loop == 1) ? 'block' : 'none';
                // Each of these is a DAY container
                echo '<div class="data-box results-day" data-day="' . $current_day_loop . '" style="display: ' . $display_style . ';">';

                
                if (isset($resultsByDayAndSport[$current_day_loop]) && !empty($resultsByDayAndSport[$current_day_loop])) {
                    $sports_for_day = array_keys($resultsByDayAndSport[$current_day_loop]);
                    
                    // Generate Sport Tabs
                    echo '<div class="sport-tabs">';
                    $first_sport = true;
                    foreach ($sports_for_day as $sport_key) {
                        $active_class = $first_sport ? 'active' : '';
                        // Use the sport key for data-sport attribute, and for translation key
                        echo '<button class="sport-tab-button ' . $active_class . '" data-sport="' . htmlspecialchars($sport_key) . '" data-translate="' . htmlspecialchars($sport_key) . '">' . htmlspecialchars($sport_key) . '</button>';
                        $first_sport = false;
                    }
                    echo '</div>'; // End of sport-tabs

                    // Generate Content for each Sport Tab
                    echo '<div class="sport-results-container">'; // Container for all sport contents for this day
                    $first_sport = true;
                    foreach ($sports_for_day as $sport_key) {
                        $sport_results = $resultsByDayAndSport[$current_day_loop][$sport_key];
                        $display_sport_style = $first_sport ? 'block' : 'none';

                        echo '<div class="sport-results-content" data-sport-content="' . htmlspecialchars($sport_key) . '" style="display: ' . $display_sport_style . ';">';
                        if (!empty($sport_results)) {
                            echo '<table class="schedule-table">';
                            echo '<thead>
                                    <tr>
                                        <th data-translate="date">Date</th>
                                        <th data-translate="match">Match</th>
                                        <th data-translate="result">Résultat</th>
                                    </tr>
                                  </thead>';
                            echo '<tbody>';
                            foreach ($sport_results as $result) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($result['date']) . '</td>';
                                echo '<td>
                                        <span data-match-country="' . htmlspecialchars($result['team1']) . '"></span>
                                        <span class="vs-text" data-translate="vs">vs</span>
                                        <span data-match-country="' . htmlspecialchars($result['team2']) . '"></span>
                                    </td>';
                                // Sport column is removed from table as it's now a tab context
                                echo '<td>' . htmlspecialchars($result['score1']) . ' - ' . htmlspecialchars($result['score2']) . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            // This case should ideally not happen if sport_key comes from non-empty $resultsByDayAndSport
                            echo '<p class="no-results-message" data-translate="noResultsForSport">Aucun résultat pour ce sport ce jour-là.</p>';
                        }
                        echo '</div>'; // End of sport-results-content for one sport
                        $first_sport = false;
                    }
                    echo '</div>'; // End of sport-results-container
                } else {
                    // Si aucun résultat n'existe pour ce jour, afficher un message
                    echo '<p class="no-results-message" data-translate="noResultsAvailable">Aucun résultat disponible pour le moment.</p>';
                }
                
                echo '</div>'; // End of results-day div
            }
        }
        ?>
    </div>
</section>
