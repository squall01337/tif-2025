// Fichier principal de JavaScript pour le site
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour mettre à jour le contenu selon la langue sélectionnée
    function updateContent(language) {
        // Ne traduire que les éléments qui n'ont pas data-no-translate="true"
        document.querySelectorAll('[data-translate]:not([data-no-translate="true"])').forEach(element => {
            const key = element.getAttribute('data-translate');
            if (translations[language] && translations[language][key]) {
                element.textContent = translations[language][key];
            }
        });
        document.querySelectorAll('td[data-team-key]:not([data-no-translate="true"])').forEach(td => {
            const key = td.getAttribute('data-team-key');
            if (translations[language] && translations[language][key]) {
                td.textContent = translations[language][key];
            }
        });
        document.querySelectorAll('[data-match-country]:not([data-no-translate="true"])').forEach(element => {
            const key = element.getAttribute('data-match-country');
            if (translations[language] && translations[language][key]) {
                element.textContent = translations[language][key];
            }
        });
        sortCountryCards(language);
        sortSportCards(language);
        
        // Traduire automatiquement les actualités si la fonction est disponible
        // Appeler translateNews pour toutes les langues, y compris le français
        if (window.translateNews) {
            console.log('Appel de translateNews avec la langue:', language);
            window.translateNews(language);
        }
        
        // Sauvegarder la langue dans localStorage
        localStorage.setItem('selectedLanguage', language);
        
        // Enregistrer la préférence de langue dans une session
        fetch('includes/set_language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'language=' + language
        });
    }

    function sortCountryCards(language) {
        const grid = document.querySelector('.country-grid');
        if (!grid) return;
        const cards = Array.from(grid.getElementsByClassName('country-card'));
        cards.sort((a, b) => {
            const nameA = a.querySelector('.country-info h3').textContent.trim();
            const nameB = b.querySelector('.country-info h3').textContent.trim();
            return nameA.localeCompare(nameB, language, { sensitivity: 'base' });
        });
        cards.forEach(card => grid.appendChild(card));
    }

    function sortSportCards(language) {
        const grid = document.querySelector('.sports-grid');
        if (!grid) return;
        const cards = Array.from(grid.getElementsByClassName('sport-card'));
        cards.sort((a, b) => {
            const nameA = a.querySelector('.sport-name').textContent.trim();
            const nameB = b.querySelector('.sport-name').textContent.trim();
            return nameA.localeCompare(nameB, language, { sensitivity: 'base' });
        });
        cards.forEach(card => grid.appendChild(card));
    }

    // Récupérer la langue sauvegardée dans localStorage
    const savedLanguage = localStorage.getItem('selectedLanguage');
    
    // Gestionnaire d'événement pour le sélecteur de langue
    const languageSelect = document.getElementById('languageSelect');
    if (languageSelect) {
        // Si une langue est sauvegardée, la définir comme valeur du sélecteur
        if (savedLanguage) {
            languageSelect.value = savedLanguage;
        }
        
        languageSelect.addEventListener('change', (e) => {
            const language = e.target.value;
            updateContent(language);
        });
    }

    // Initialiser la langue
    const currentLang = savedLanguage || (languageSelect ? languageSelect.value : 'fr');
    updateContent(currentLang);

    // Fonctionnalité des onglets du calendrier
    document.querySelectorAll('.schedule-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.schedule-tab').forEach(b => b.classList.remove('active'));
            tab.classList.add('active');
            const day = tab.getAttribute('data-day');
            document.querySelectorAll('.schedule-day').forEach(div => {
                div.style.display = (div.getAttribute('data-day') === day) ? 'block' : 'none';
            });
        });
    });

    // Fonctionnalité des onglets de résultats
    document.querySelectorAll('.results-tabs .tab-button').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.results-tabs .tab-button').forEach(b => b.classList.remove('active'));
            button.classList.add('active');
            const day = button.getAttribute('data-day');
            // Afficher les résultats du jour sélectionné
            document.querySelectorAll('.results-day').forEach(div => {
                div.style.display = (div.getAttribute('data-day') === day) ? 'block' : 'none';
            });
        });
    });

    // Fonctionnalité de pagination de la galerie
    const galleryPrevBtn = document.getElementById('prevGallery');
    const galleryNextBtn = document.getElementById('nextGallery');
    
    if (galleryPrevBtn && galleryNextBtn) {
        // Les boutons sont déjà configurés avec des liens dans le HTML
        // Aucun code JavaScript supplémentaire n'est nécessaire ici
    }

    // Animation des sections lors du chargement
    document.querySelectorAll('.animate-in').forEach(section => {
        if (section.style.display !== 'none') {
            section.style.opacity = '0';
            setTimeout(() => {
                section.style.opacity = '1';
            }, 100);
        }
    });
});
