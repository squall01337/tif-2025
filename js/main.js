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

    // Gallery Modal Functionality
    const modal = document.getElementById("galleryModal");
    if (modal) {
        const modalImg = document.getElementById("modalImage");
        const captionText = document.getElementById("caption");
        const galleryItems = document.querySelectorAll(".gallery-item");
        const closeBtn = document.querySelector(".close-button");
        const prevBtn = document.querySelector(".prev-button");
        const nextBtn = document.querySelector(".next-button");
        let currentIndex = 0;
        let currentGalleryImages = [];

        galleryItems.forEach((item, index) => {
            const img = item.querySelector("img");
            if (img) { // Ensure there's an image to click on
                img.onclick = function() {
                    // Populate currentGalleryImages with actual image sources and titles from the current page
                    currentGalleryImages = [];
                    document.querySelectorAll(".gallery-item img").forEach(galleryImg => {
                        currentGalleryImages.push({
                            src: galleryImg.src,
                            alt: galleryImg.alt
                        });
                    });

                    // modal.style.display = "block"; // Replaced by classList
                    modal.classList.add('is-open');
                    document.body.style.overflow = "hidden"; // Prevent background scroll
                    // Find the index of the clicked image within the current page's gallery items
                    currentIndex = Array.from(document.querySelectorAll(".gallery-item img")).findIndex(gImg => gImg.src === this.src);
                    updateModalContent();
                }
            }
        });

        function updateModalContent() {
            if (currentGalleryImages.length > 0 && currentIndex >= 0 && currentIndex < currentGalleryImages.length) {
                modalImg.src = currentGalleryImages[currentIndex].src;
                captionText.innerHTML = currentGalleryImages[currentIndex].alt; // Update caption even if hidden
            }
            // Hide/show nav buttons
            prevBtn.style.display = currentIndex === 0 ? "none" : "block";
            nextBtn.style.display = currentIndex === currentGalleryImages.length - 1 ? "none" : "block";
        }

        function showNextImage() {
            if (currentIndex < currentGalleryImages.length - 1) {
                currentIndex++;
                updateModalContent();
            }
        }

        function showPrevImage() {
            if (currentIndex > 0) {
                currentIndex--;
                updateModalContent();
            }
        }

        if (closeBtn) {
            closeBtn.onclick = function() {
                // modal.style.display = "none"; // Replaced by classList
                modal.classList.remove('is-open');
                document.body.style.overflow = ""; // Restore background scroll
            }
        }

        if (prevBtn) {
            prevBtn.onclick = showPrevImage;
        }

        if (nextBtn) {
            nextBtn.onclick = showNextImage;
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            // if (modal.style.display === "block") { // Replaced by classList check
            if (modal.classList.contains('is-open')) {
                if (e.key === "ArrowLeft") {
                    showPrevImage();
                } else if (e.key === "ArrowRight") {
                    showNextImage();
                } else if (e.key === "Escape") {
                    // modal.style.display = "none"; // Replaced by classList
                    modal.classList.remove('is-open');
                    document.body.style.overflow = ""; // Restore background scroll
                }
            }
        });

        // Swipe navigation for touch devices
        let touchstartX = 0;
        let touchendX = 0;

        modal.addEventListener('touchstart', function(event) {
            touchstartX = event.changedTouches[0].screenX;
        }, false);

        modal.addEventListener('touchend', function(event) {
            touchendX = event.changedTouches[0].screenX;
            handleSwipeGesture();
        }, false);

        function handleSwipeGesture() {
            if (touchendX < touchstartX && (touchstartX - touchendX > 50)) { // Swiped left
                showNextImage();
            }
            if (touchendX > touchstartX && (touchendX - touchstartX > 50)) { // Swiped right
                showPrevImage();
            }
        }
    }
});
