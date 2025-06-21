<section id="schedule" class="animate-in">
    <h2 data-translate="schedule">Calendrier du Tournoi</h2>
    <div class="results-tabs">
        <button class="tab-button schedule-tab active" data-day="1" data-translate="day1">Jour 1</button>
        <button class="tab-button schedule-tab" data-day="2" data-translate="day2">Jour 2</button>
        <button class="tab-button schedule-tab" data-day="3" data-translate="day3">Jour 3</button>
        <button class="tab-button schedule-tab" data-day="4" data-translate="day4">Jour 4</button>
        <button class="tab-button schedule-tab" data-day="5" data-translate="day5">Jour 5</button>
    </div>
    <div class="results-content" id="scheduleContent">
        <?php
        // Récupérer les données du calendrier depuis la base de données
        try {
            $pdo = connectDB();
            $scheduleData = getSchedule($pdo);
            
            // Organiser les données par jour
            $scheduleByDay = [];
            foreach ($scheduleData as $match) {
                $day = $match['day'];
                if (!isset($scheduleByDay[$day])) {
                    $scheduleByDay[$day] = [];
                }
                $scheduleByDay[$day][] = $match;
            }
            
            // Si aucune donnée n'est trouvée, utiliser les données par défaut
            if (empty($scheduleByDay)) {
                $defaultSchedule = true;
            } else {
                $defaultSchedule = false;
            }
            
        } catch (PDOException $e) {
            // En cas d'erreur, utiliser les données par défaut
            $defaultSchedule = true;
        }
        
        // Afficher les données par défaut si nécessaire
        if ($defaultSchedule) {
        ?>
            <div class="data-box schedule-day" data-day="1">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="time">Heure</th>
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
                            <td>20:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box schedule-day" data-day="2" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="time">Heure</th>
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
                            <td>20:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box schedule-day" data-day="3" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="time">Heure</th>
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
                            <td>21:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box schedule-day" data-day="4" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="time">Heure</th>
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
                            <td>19:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="data-box schedule-day" data-day="5" style="display: none;">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="time">Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-translate="matchDate1"></td>
                            <td>
                                <span data-match-country="countryAustria"></span>
                                <span class="vs-text" data-translate="vs">vs</span>
                                <span data-match-country="countryGermany"></span>
                            </td>
                            <td data-translate="soccer">Football</td>
                            <td>20:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php
        } else {
            // Afficher les données de la base de données
            foreach ($scheduleByDay as $day => $matches) {
                $display = ($day == 1) ? 'block' : 'none';
                echo '<div class="data-box schedule-day" data-day="' . $day . '" style="display: ' . $display . ';">';
                echo '<table class="schedule-table">';
                echo '<thead>
                        <tr>
                            <th data-translate="date">Date</th>
                            <th data-translate="match">Match</th>
                            <th data-translate="sport">Sport</th>
                            <th data-translate="time">Heure</th>
                        </tr>
                      </thead>';
                
                echo '<tbody>';
                foreach ($matches as $match) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($match['date']) . '</td>';
                    echo '<td>
                            <span data-match-country="' . htmlspecialchars($match['team1']) . '"></span>
                            <span class="vs-text" data-translate="vs">vs</span>
                            <span data-match-country="' . htmlspecialchars($match['team2']) . '"></span>
                        </td>';
                    echo '<td data-translate="' . htmlspecialchars($match['sport']) . '">' . htmlspecialchars($match['sport']) . '</td>';
                    echo '<td>' . htmlspecialchars($match['time']) . '</td>';
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
