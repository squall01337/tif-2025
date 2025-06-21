/**
 * JavaScript pour la gestion des onglets de la galerie
 * Ce script est optionnel car nous utilisons déjà des liens avec paramètres d'URL
 * Il peut être utilisé pour une expérience utilisateur améliorée avec transitions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité des onglets de la galerie
    document.querySelectorAll('.gallery-tabs .tab-button').forEach(button => {
        button.addEventListener('click', function(e) {
            // Si nous voulons gérer les onglets en JavaScript sans rechargement de page
            // Décommenter ce bloc et commenter le href dans les liens
            /*
            e.preventDefault();
            
            // Mettre à jour les classes actives des onglets
            document.querySelectorAll('.gallery-tabs .tab-button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Afficher le contenu correspondant
            const tabId = this.getAttribute('data-tab');
            document.querySelectorAll('.gallery-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabId + '-content').classList.add('active');
            
            // Mettre à jour l'URL sans recharger la page
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            */
        });
    });
    
    // Gestion des vidéos YouTube - Optimisation des performances
    // Charger les iframes YouTube uniquement lorsqu'elles sont visibles
    const videoItems = document.querySelectorAll('.video-item');
    
    // Observer les éléments vidéo pour charger les iframes uniquement lorsqu'elles sont visibles
    if ('IntersectionObserver' in window) {
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const iframe = entry.target.querySelector('iframe');
                    if (iframe && iframe.dataset.src) {
                        iframe.src = iframe.dataset.src;
                        iframe.removeAttribute('data-src');
                    }
                    videoObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        videoItems.forEach(item => {
            videoObserver.observe(item);
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas IntersectionObserver
        videoItems.forEach(item => {
            const iframe = item.querySelector('iframe');
            if (iframe && iframe.dataset.src) {
                iframe.src = iframe.dataset.src;
                iframe.removeAttribute('data-src');
            }
        });
    }
});

