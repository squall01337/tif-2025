<?php
// Page des classements
// Utilise les fonctions de récupération de données

// Récupérer les données de classement depuis la base de données
try {
    $pdo = connectDB();
    $standingsData = getStandings($pdo);
    
    // Si aucune donnée n'est trouvée, utiliser les données par défaut
    if (empty($standingsData)) {
        $defaultStandings = true;
    } else {
        $defaultStandings = false;
    }
    
} catch (PDOException $e) {
    // En cas d'erreur, utiliser les données par défaut
    $defaultStandings = true;
}
?>

<section id="standings" class="animate-in">
    <h2 data-translate="standings">Classement actuel</h2>
    
    <!-- Section des liens vers les classements par discipline -->
    <div class="discipline-rankings">
        <h3 data-translate="disciplineRankings">Classements par discipline</h3>
        <div class="discipline-links">
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-foot.jpg" target="_blank" class="discipline-link">
                <span data-translate="football">Football</span>
            </a>
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-tennis.jpg" target="_blank" class="discipline-link">
                <span data-translate="tennis">Tennis</span>
            </a>
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-tennis-de-table.jpg" target="_blank" class="discipline-link">
                <span data-translate="pingPong">Ping pong</span>
            </a>
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-echecs.jpg" target="_blank" class="discipline-link">
                <span data-translate="chess">Chess</span>
            </a>
        </div>
    </div>
    
    <div class="data-box">
        <table class="standings-table">
            <thead>
                <tr>
                    <th data-translate="position">Position</th>
                    <th data-translate="team">Équipe</th>
                    <th data-translate="played">Joué</th>
                    <th data-translate="won">Victoires</th>
                    <th data-translate="drawn">Nuls</th>
                    <th data-translate="lost">Défaites</th>
                    <th data-translate="points">Points</th>
                </tr>
            </thead>
            <tbody id="standingsBody">
                <?php
                // Afficher les données par défaut si nécessaire
                if ($defaultStandings) {
                    $teams = ["countryAustria", "countryFrance", "countryHungary", "countryLuxembourg", "countryGermany"];
                    $standings = [];
                    
                    foreach ($teams as $team) {
                        $played = rand(3, 10);
                        $wins = rand(0, $played);
                        $draws = rand(0, $played - $wins);
                        $losses = $played - $wins - $draws;
                        $points = $wins * 3 + $draws;
                        
                        $standings[] = [
                            'team' => $team,
                            'played' => $played,
                            'wins' => $wins,
                            'draws' => $draws,
                            'losses' => $losses,
                            'points' => $points
                        ];
                    }
                    
                    // Trier par points puis par victoires
                    usort($standings, function($a, $b) {
                        if ($a['points'] == $b['points']) {
                            return $b['wins'] - $a['wins'];
                        }
                        return $b['points'] - $a['points'];
                    });
                    
                    foreach ($standings as $index => $team) {
                        echo '<tr>';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td data-team-key="' . $team['team'] . '"></td>';
                        echo '<td>' . $team['played'] . '</td>';
                        echo '<td>' . $team['wins'] . '</td>';
                        echo '<td>' . $team['draws'] . '</td>';
                        echo '<td>' . $team['losses'] . '</td>';
                        echo '<td>' . $team['points'] . '</td>';
                        echo '</tr>';
                    }
                } else {
                    // Afficher les données de la base de données
                    foreach ($standingsData as $index => $team) {
                        echo '<tr>';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td data-team-key="' . htmlspecialchars($team['team']) . '"></td>';
                        echo '<td>' . htmlspecialchars($team['played']) . '</td>';
                        echo '<td>' . htmlspecialchars($team['wins']) . '</td>';
                        echo '<td>' . htmlspecialchars($team['draws']) . '</td>';
                        echo '<td>' . htmlspecialchars($team['losses']) . '</td>';
                        echo '<td>' . htmlspecialchars($team['points']) . '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>
