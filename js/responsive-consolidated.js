/**
 * Script JavaScript consolidé pour la responsivité du site du Tournoi International des Finances
 * Combine les fonctionnalités de:
 * - Menu hamburger avec texte MENU
 * - Étiquettes dynamiques pour les tableaux
 * - Coloration des équipes selon le score
 * - Coloration des scores selon le résultat
 * - Galerie responsive avec navigation tactile
 */

document.addEventListener('DOMContentLoaded', function() {
  // ===== MENU HAMBURGER AVEC TEXTE MENU =====
  function setupMobileMenu() {
    const nav = document.querySelector('.nav');
    if (!nav) return;
    
    const navUl = nav.querySelector('ul');
    if (!navUl) return;
    
    // Créer le bouton hamburger avec texte MENU
    const menuToggle = document.createElement('button');
    menuToggle.className = 'menu-toggle hamburger-menu';
    menuToggle.innerHTML = '&#9776;'; // Icône hamburger
    menuToggle.setAttribute('aria-label', 'Menu');
    
    // Insérer le bouton avant la liste
    nav.insertBefore(menuToggle, navUl);
    
    // Ajouter l'événement de clic
    menuToggle.addEventListener('click', function() {
      navUl.classList.toggle('show');
    });
    
    console.log('Menu mobile configuré avec succès');
  }

  // ===== ÉTIQUETTES DYNAMIQUES POUR LES TABLEAUX =====
  function addTableLabels() {
    // Pour le tableau de calendrier
    const scheduleTables = document.querySelectorAll('.schedule-table');
    scheduleTables.forEach(table => {
      if (!table.querySelector('thead')) return;
      
      const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, i) => {
          if (headers[i]) {
            cell.setAttribute('data-label', headers[i]);
          }
        });
      });
    });
    
    // Pour le tableau de classement
    const standingsTables = document.querySelectorAll('.standings-table');
    standingsTables.forEach(table => {
      if (!table.querySelector('thead')) return;
      
      const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, i) => {
          if (headers[i]) {
            cell.setAttribute('data-label', headers[i]);
          }
        });
      });
    });
    
    console.log('Étiquettes de tableaux ajoutées avec succès');
  }

  // ===== COLORATION DES ÉQUIPES ET DES SCORES SELON LE RÉSULTAT =====
  function colorTeamsAndScores() {
    // Sélectionner tous les tableaux de résultats
    const resultsTables = document.querySelectorAll('.results-day .schedule-table');
    
    resultsTables.forEach(table => {
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        // Récupérer la cellule qui contient les équipes (2ème cellule)
        const matchCell = row.querySelector('td:nth-child(2)');
        if (!matchCell) return;
        
        // Récupérer la cellule qui contient le score (4ème cellule)
        const scoreCell = row.querySelector('td:nth-child(4)');
        if (!scoreCell) return;
        
        // Récupérer les équipes
        const team1 = matchCell.querySelector('span:nth-child(1)');
        const team2 = matchCell.querySelector('span:nth-child(3)');
        if (!team1 || !team2) return;
        
        // Récupérer le score
        const scoreText = scoreCell.textContent.trim();
        const scores = scoreText.split('-').map(s => parseInt(s.trim()));
        
        if (scores.length !== 2) return;
        
        const score1 = scores[0];
        const score2 = scores[1];
        
        // Remplacer le contenu de la cellule de score par des spans pour chaque score
        const scoreContent = scoreText.split('-');
        if (scoreContent.length === 2) {
          // Créer les spans pour les scores
          const scoreSpan1 = document.createElement('span');
          scoreSpan1.textContent = scoreContent[0].trim();
          scoreSpan1.className = 'score-value';
          
          const separator = document.createElement('span');
          separator.textContent = ' - ';
          separator.className = 'score-separator';
          
          const scoreSpan2 = document.createElement('span');
          scoreSpan2.textContent = scoreContent[1].trim();
          scoreSpan2.className = 'score-value';
          
          // Vider la cellule et ajouter les nouveaux éléments
          scoreCell.textContent = '';
          scoreCell.appendChild(scoreSpan1);
          scoreCell.appendChild(separator);
          scoreCell.appendChild(scoreSpan2);
          
          // Appliquer les classes selon le score
          if (score1 > score2) {
            // Équipe 1 gagne
            team1.classList.add('team-winner');
            team2.classList.add('team-loser');
            scoreSpan1.classList.add('score-winner');
            scoreSpan2.classList.add('score-loser');
          } else if (score1 < score2) {
            // Équipe 2 gagne
            team1.classList.add('team-loser');
            team2.classList.add('team-winner');
            scoreSpan1.classList.add('score-loser');
            scoreSpan2.classList.add('score-winner');
          } else {
            // Match nul
            team1.classList.add('team-draw');
            team2.classList.add('team-draw');
            scoreSpan1.classList.add('score-draw');
            scoreSpan2.classList.add('score-draw');
          }
        }
      });
    });
    
    console.log('Coloration des équipes et des scores appliquée avec succès');
  }

  // ===== GALERIE RESPONSIVE SIMPLE =====
  function setupGalleryModal() {
    // Vérifier si la galerie existe sur la page
    const gallerySection = document.getElementById('gallery');
    if (!gallerySection) return;
    
    console.log('Configuration de la galerie responsive');
    
    // Variables globales pour la galerie
    window.currentGalleryIndex = 0;
    window.galleryImages = [];
    
    // Récupérer tous les éléments de la galerie et leurs informations
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach((item, index) => {
      const src = item.getAttribute('data-src');
      const caption = item.getAttribute('data-caption');
      
      if (src && caption) {
        window.galleryImages.push({
          src: src,
          caption: caption
        });
        
        // Ajouter l'événement de clic pour ouvrir la modale
        item.addEventListener('click', function() {
          openGalleryModal(src, caption, index);
        });
      }
    });
    
    // Ajouter les gestionnaires d'événements tactiles pour mobile
    setupTouchEvents();
    
    console.log('Galerie responsive configurée avec succès');
  }
  
  // Fonction pour configurer les événements tactiles (uniquement sur mobile)
  function setupTouchEvents() {
    const modal = document.getElementById('galleryModal');
    if (!modal) return;
    
    // Vérifier si on est sur mobile
    const isMobile = window.innerWidth <= 767;
    if (!isMobile) return;
    
    let touchStartX = 0;
    let touchEndX = 0;
    
    modal.addEventListener('touchstart', function(e) {
      touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    modal.addEventListener('touchend', function(e) {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, false);
    
    function handleSwipe() {
      const swipeThreshold = 50;
      if (touchEndX < touchStartX - swipeThreshold) {
        // Swipe vers la gauche (image suivante)
        showNextImage();
      } else if (touchEndX > touchStartX + swipeThreshold) {
        // Swipe vers la droite (image précédente)
        showPrevImage();
      }
    }
  }
  
  // Fonctions de navigation de la galerie
  window.openGalleryModal = function(imageSrc, caption, index) {
    const modal = document.getElementById('galleryModal');
    if (!modal) return;
    
    // S'assurer que la modale a le bon contenu
    let modalContent = modal.querySelector('.gallery-modal-content');
    if (!modalContent) {
      // Créer l'élément image s'il n'existe pas
      modalContent = document.createElement('img');
      modalContent.className = 'gallery-modal-content';
      modal.appendChild(modalContent);
    }
    
    const captionElement = modal.querySelector('.gallery-modal-caption');
    
    // Définir l'index courant
    window.currentGalleryIndex = index !== undefined ? index : 0;
    
    // Mettre à jour l'image
    if (imageSrc.startsWith('placeholder-')) {
      // Pour les placeholders, créer un SVG
      modalContent.outerHTML = `
        <svg class="gallery-modal-content" viewBox="0 0 250 250" width="250" height="250">
          <rect width="250" height="250" fill="#cccccc"></rect>
          <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#666666" font-size="20">${caption}</text>
        </svg>
      `;
    } else {
      // Pour les vraies images
      if (modalContent.tagName !== 'IMG') {
        modalContent.outerHTML = '<img class="gallery-modal-content" src="" alt="">';
        modalContent = modal.querySelector('.gallery-modal-content');
      }
      modalContent.src = imageSrc;
      modalContent.alt = caption;
    }
    
    // Mettre à jour la légende
    if (captionElement) {
      captionElement.textContent = caption;
    }
    
    // Afficher le modal avec la classe show
    modal.classList.add('show');
    modal.style.display = 'flex';
    
    // Désactiver le défilement du body
    document.body.style.overflow = 'hidden';
  };
  
  window.closeGalleryModal = function() {
    const modal = document.getElementById('galleryModal');
    if (!modal) return;
    
    // Cacher le modal
    modal.classList.remove('show');
    modal.style.display = 'none';
    
    // Réactiver le défilement du body
    document.body.style.overflow = '';
  };
  
  window.showPrevImage = function() {
    if (window.currentGalleryIndex > 0) {
      window.currentGalleryIndex--;
      const currentImage = window.galleryImages[window.currentGalleryIndex];
      if (currentImage) {
        openGalleryModal(currentImage.src, currentImage.caption, window.currentGalleryIndex);
      }
    }
  };
  
  window.showNextImage = function() {
    if (window.galleryImages && window.currentGalleryIndex < window.galleryImages.length - 1) {
      window.currentGalleryIndex++;
      const currentImage = window.galleryImages[window.currentGalleryIndex];
      if (currentImage) {
        openGalleryModal(currentImage.src, currentImage.caption, window.currentGalleryIndex);
      }
    }
  };
  
  // Ajouter des gestionnaires d'événements pour la navigation au clavier
  document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('galleryModal');
    if (modal && modal.style.display === 'block') {
      if (event.key === 'Escape') {
        closeGalleryModal();
      } else if (event.key === 'ArrowLeft') {
        showPrevImage();
      } else if (event.key === 'ArrowRight') {
        showNextImage();
      }
    }
  });

  // ===== INITIALISATION =====
  // Configurer le menu mobile
  setupMobileMenu();
  
  // Ajouter les étiquettes aux tableaux
  addTableLabels();
  
  // Colorer les équipes et les scores
  colorTeamsAndScores();
  
  // Configurer la galerie responsive
  setupGalleryModal();
  
  // Réexécuter la coloration lorsqu'on change d'onglet de jour
  const tabButtons = document.querySelectorAll('.results-tabs .tab-button');
  tabButtons.forEach(button => {
    button.addEventListener('click', function() {
      // Laisser le temps au contenu de s'afficher
      setTimeout(colorTeamsAndScores, 100);
    });
  });
  
  console.log('Initialisation responsive terminée');
});

