<?php
// Page des classements
// Version simplifiée avec affichage d'image
?>

<section id="standings" class="animate-in">
    <h2 data-translate="standings">Classement actuel</h2>
    
    <!-- Section des liens vers les classements par discipline -->
    <div class="discipline-rankings">
        <h3 data-translate="disciplineRankings">Classements par discipline</h3>
        <div class="discipline-links">
            <!-- Solution finale : boutons stylisés avec texte en dur (sans data-translate) -->
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-foot.jpg" target="_blank" class="discipline-button">
                Football
            </a>
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-tennis.jpg" target="_blank" class="discipline-button">
                Tennis
            </a>
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-tennis-de-table.jpg" target="_blank" class="discipline-button">
                Ping pong
            </a>
            <a href="https://portail.atscaf.fr/uploads/2025/06/classement-echecs.jpg" target="_blank" class="discipline-button">
                Chess
            </a>
        </div>
    </div>
    
    <style>
    /* Styles pour l'image du classement général */
    .general-standings-image {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 20px auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    /* Styles pour le titre du classement général */
    .general-standings-title {
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 30px 0 15px;
        color: #1a365d;
    }
    
    /* Styles pour les liens des classements par discipline */
    .discipline-rankings {
        margin-bottom: 30px;
    }
    
    .discipline-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }
    
    /* Solution finale : style de bouton complet */
    .discipline-button {
        display: block;
        background-color: #1a365d;
        color: white !important;
        padding: 10px 15px;
        text-align: left;
        text-decoration: none;
        font-weight: 500;
        border-radius: 5px;
        transition: all 0.3s ease;
        margin-bottom: 5px;
        border: none;
        width: 100%;
        max-width: 300px;
        font-size: 16px;
    }
    
    .discipline-button:hover {
        background-color: #4CAF50;
        color: white !important;
    }
    </style>
    
    <!-- Titre du classement général -->
    <h3 class="general-standings-title">GENERAL LEADERBOARD</h3>
    
    <!-- Image du classement général -->
    <img src="https://portail.atscaf.fr/uploads/2025/06/classement-general.jpg" alt="Classement général" class="general-standings-image">
</section>

