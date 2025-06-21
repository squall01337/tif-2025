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
    
    // Si aucune donnée n'est trouvée, utiliser les données par défaut
    if (empty($resultsByDay)) {
        $defaultResults = true;
    } else {
        $defaultResults = false;
    }
    
} catch (PDOException $e) {
    // En cas d'erreur, utiliser les données par défaut
    $defaultResults = true;
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
        // Afficher les données par défaut si nécessaire
        if ($defaultResults) {
        ?>
            <div class="data-box results-day" data-day="1" style="display: block;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="result">Résultat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-translate="matchDate1"></td>
                            <td>
                                <span data-match-country="countryAustria"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="countryFrance"></span>
                            </td>
                            <td data-translate="soccer">Football</td>
                            <td>2 - 1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box results-day" data-day="2" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="result">Résultat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-translate="matchDate2"></td>
                            <td>
                                <span data-match-country="countryHungary"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="countryLuxembourg"></span>
                            </td>
                            <td data-translate="tennis">Tennis</td>
                            <td>3 - 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box results-day" data-day="3" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="result">Résultat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-translate="matchDate3"></td>
                            <td>
                                <span data-match-country="countryGermany"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="countryAustria"></span>
                            </td>
                            <td data-translate="pingPong">Tennis de Table</td>
                            <td>3 - 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box results-day" data-day="4" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="result">Résultat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-translate="matchDate4"></td>
                            <td>
                                <span data-match-country="countryFrance"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="countryHungary"></span>
                            </td>
                            <td data-translate="chess">Échecs</td>
                            <td>1 - 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box results-day" data-day="5" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="result">Résultat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-translate="matchDate5"></td>
                            <td>
                                <span data-match-country="countryAustria"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="countryGermany"></span>
                            </td>
                            <td data-translate="soccer">Football</td>
                            <td>2 - 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php
        } else {
            // Afficher les données de la base de données
            foreach ($resultsByDay as $day => $results) {
                $display = ($day == 1) ? 'block' : 'none';
                echo '<div class="data-box results-day" data-day="' . $day . '" style="display: ' . $display . ';">';
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
                foreach ($results as $result) {
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
                echo '</div>';
            }
        }
        ?>
    </div>
</section>
