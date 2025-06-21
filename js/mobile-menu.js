// Code JavaScript pour le menu hamburger mobile
document.addEventListener('DOMContentLoaded', function() {
  // Créer le bouton hamburger
  const nav = document.querySelector('.nav');
  const navUl = nav.querySelector('ul');
  
  const menuToggle = document.createElement('button');
  menuToggle.className = 'menu-toggle';
  menuToggle.innerHTML = '&#9776;'; // Icône hamburger
  menuToggle.setAttribute('aria-label', 'Menu');
  
  // Insérer le bouton avant la liste
  nav.insertBefore(menuToggle, navUl);
  
  // Ajouter l'événement de clic
  menuToggle.addEventListener('click', function() {
    navUl.classList.toggle('show');
  });
});
