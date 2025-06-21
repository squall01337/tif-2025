// Fichier pour la traduction automatique des contenus dynamiques via proxy PHP
document.addEventListener('DOMContentLoaded', function() {
    // Cache pour stocker les traductions déjà effectuées
    const translationCache = {};
    
    // Fonction pour appliquer le formatage en gras au chargement initial
    function applyBoldFormattingOnLoad() {
        console.log('Application du formatage en gras au chargement initial');
        
        // Appliquer le formatage en gras aux discours des présidents
        document.querySelectorAll('.president-speech-text').forEach(speech => {
            // Stocker le HTML original complet s'il n'est pas déjà stocké
            if (!speech.getAttribute('data-original-html')) {
                // Appliquer le formatage en gras avant de stocker le HTML original
                speech.setAttribute('data-original-html', speech.innerHTML);
            }
        });
    }
    
    // Appliquer le formatage en gras immédiatement au chargement
    applyBoldFormattingOnLoad();
    
    // Fonction pour traduire un texte via le proxy PHP
    async function translateText(text, sourceLang, targetLang) {
        // Vérifier si la traduction est déjà en cache
        const cacheKey = `${text}_${sourceLang}_${targetLang}`;
        if (translationCache[cacheKey]) {
            return translationCache[cacheKey];
        }
        
        // Si la langue source est la même que la langue cible, retourner le texte original
        if (sourceLang === targetLang) {
            return text;
        }
        
        try {
            // Utiliser notre proxy PHP local pour éviter les problèmes CORS
            const formData = new FormData();
            formData.append('text', text);
            formData.append('source', sourceLang);
            formData.append('target', targetLang);
            
            const response = await fetch('translate_proxy.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Vérifier que la réponse est valide
            if (!data.success || !data.translatedText) {
                throw new Error('Réponse de traduction invalide');
            }
            
            // Stocker la traduction dans le cache
            translationCache[cacheKey] = data.translatedText;
            
            return data.translatedText;
        } catch (error) {
            console.error('Erreur de traduction:', error);
            return text; // En cas d'erreur, retourner le texte original
        }
    }
    
    // Approche minimaliste pour préserver les balises span.bold et les espaces
    async function translateHtmlMinimal(html, sourceLang, targetLang) {
        // Si le contenu est vide, retourner une chaîne vide
        if (!html) return '';
        
        // Si les langues sont identiques, retourner le contenu original
        if (sourceLang === targetLang) return html;
        
        try {
            // 1. Remplacer temporairement les balises span.bold par des marqueurs spéciaux
            let processedHtml = html;
            
            // Remplacer les balises span.bold par des marqueurs
            const boldPattern = /<span class="bold">(.*?)<\/span>/g;
            const boldTexts = [];
            
            processedHtml = processedHtml.replace(boldPattern, function(match, content) {
                const marker = `__BOLD_${boldTexts.length}__`;
                boldTexts.push(content);
                return marker;
            });
            
            // 2. Traduire le texte avec les marqueurs
            const translatedText = await translateText(processedHtml, sourceLang, targetLang);
            
            // 3. Restaurer les balises span.bold
            let finalHtml = translatedText;
            
            // Traduire chaque texte en gras séparément
            for (let i = 0; i < boldTexts.length; i++) {
                const translatedBoldText = await translateText(boldTexts[i], sourceLang, targetLang);
                const marker = `__BOLD_${i}__`;
                // Utiliser une expression régulière pour remplacer le marqueur tout en préservant les espaces avant et après
                finalHtml = finalHtml.replace(
                    new RegExp(`(\\s*)${marker}(\\s*)`, 'g'), 
                    `$1<span class="bold">${translatedBoldText}</span>$2`
                );
            }
            
            return finalHtml;
        } catch (error) {
            console.error('Erreur lors de la traduction HTML:', error);
            return html; // En cas d'erreur, retourner le contenu original
        }
    }
    
    // Fonction pour traduire tous les contenus dynamiques (actualités, timeline et présidents)
    async function translateDynamicContent(targetLang) {
        // Définir la langue source (français)
        const sourceLang = 'fr';
        
        // Convertir les codes de langue pour l'API
        const langMap = {
            'fr': 'fr',
            'en': 'en',
            'de': 'de',
            'hu': 'hu',
            'nl': 'nl'
        };
        
        // Si la langue cible est le français, restaurer les textes originaux
        if (targetLang === 'fr') {
            console.log('Restauration des textes originaux en français');
            
            // Restaurer les titres des actualités
            document.querySelectorAll('.news-title[data-original-text][data-no-translate="true"]').forEach(title => {
                const originalText = title.getAttribute('data-original-text');
                if (originalText) {
                    title.textContent = originalText;
                }
            });
            
            // Restaurer le contenu des actualités
            document.querySelectorAll('.news-content-text[data-original-text][data-no-translate="true"]').forEach(content => {
                const originalText = content.getAttribute('data-original-text');
                if (originalText) {
                    content.innerHTML = nl2br(originalText);
                }
            });
            
            // Restaurer le contenu de la timeline
            document.querySelectorAll('.timeline-content-text[data-original-text][data-no-translate="true"]').forEach(content => {
                const originalText = content.getAttribute('data-original-text');
                if (originalText) {
                    content.innerHTML = nl2br(originalText);
                }
            });
            
            // Restaurer les noms de pays des présidents
            document.querySelectorAll('.president-country[data-original-text][data-no-translate="true"]').forEach(country => {
                const originalText = country.getAttribute('data-original-text');
                if (originalText) {
                    country.textContent = originalText;
                }
            });
            
            // Restaurer les discours des présidents
            document.querySelectorAll('.president-speech-text[data-original-text][data-no-translate="true"]').forEach(speech => {
                // Récupérer le HTML original complet
                const originalHtml = speech.getAttribute('data-original-html');
                if (originalHtml) {
                    // Utiliser le HTML original s'il existe
                    speech.innerHTML = originalHtml;
                } else {
                    // Sinon, utiliser le texte original avec nl2br
                    const originalText = speech.getAttribute('data-original-text');
                    if (originalText) {
                        speech.innerHTML = nl2br(originalText);
                    }
                }
            });
            
            return; // Sortir de la fonction après restauration
        }
        
        // Si la langue cible n'est pas supportée, ne pas traduire
        if (!langMap[targetLang]) {
            return;
        }
        
        console.log('Traduction des contenus dynamiques vers', targetLang);
        
        // 1. Traduire les titres des actualités
        const newsTitles = document.querySelectorAll('.news-title[data-original-text][data-no-translate="true"]');
        console.log('Titres d\'actualités à traduire:', newsTitles.length);
        
        for (const title of newsTitles) {
            const originalText = title.getAttribute('data-original-text');
            if (originalText) {
                // Afficher un indicateur de chargement
                title.innerHTML = '<em>Traduction en cours...</em>';
                
                try {
                    // Traduire le texte
                    const translatedText = await translateText(originalText, langMap[sourceLang], langMap[targetLang]);
                    
                    // Vérifier que la traduction n'est pas vide ou identique à l'original
                    if (translatedText && translatedText !== originalText) {
                        // Mettre à jour le contenu
                        title.textContent = translatedText;
                        console.log('Titre traduit avec succès');
                    } else {
                        // En cas de problème, revenir au texte original
                        title.textContent = originalText;
                        console.log('Échec de traduction du titre, texte original conservé');
                    }
                } catch (error) {
                    console.error('Erreur lors de la traduction du titre:', error);
                    title.textContent = originalText;
                }
            }
        }
        
        // 2. Traduire le contenu des actualités
        const newsContents = document.querySelectorAll('.news-content-text[data-original-text][data-no-translate="true"]');
        console.log('Contenus d\'actualités à traduire:', newsContents.length);
        
        for (const content of newsContents) {
            const originalText = content.getAttribute('data-original-text');
            if (originalText) {
                // Afficher un indicateur de chargement
                content.innerHTML = '<em>Traduction en cours...</em>';
                
                try {
                    // Traduire le texte
                    const translatedText = await translateText(originalText, langMap[sourceLang], langMap[targetLang]);
                    
                    // Vérifier que la traduction n'est pas vide ou identique à l'original
                    if (translatedText && translatedText !== originalText) {
                        // Mettre à jour le contenu avec les sauts de ligne préservés
                        content.innerHTML = translatedText.replace(/\n/g, '<br>');
                        console.log('Contenu d\'actualité traduit avec succès');
                    } else {
                        // En cas de problème, revenir au texte original
                        content.innerHTML = nl2br(originalText);
                        console.log('Échec de traduction du contenu, texte original conservé');
                    }
                } catch (error) {
                    console.error('Erreur lors de la traduction du contenu:', error);
                    content.innerHTML = nl2br(originalText);
                }
            }
        }
        
        // 3. Traduire le contenu de la timeline
        const timelineContents = document.querySelectorAll('.timeline-content-text[data-original-text][data-no-translate="true"]');
        console.log('Contenus de timeline à traduire:', timelineContents.length);
        
        for (const content of timelineContents) {
            const originalText = content.getAttribute('data-original-text');
            if (originalText) {
                // Afficher un indicateur de chargement
                content.innerHTML = '<em>Traduction en cours...</em>';
                
                try {
                    // Traduire le texte
                    const translatedText = await translateText(originalText, langMap[sourceLang], langMap[targetLang]);
                    
                    // Vérifier que la traduction n'est pas vide ou identique à l'original
                    if (translatedText && translatedText !== originalText) {
                        // Mettre à jour le contenu avec les sauts de ligne préservés
                        content.innerHTML = translatedText.replace(/\n/g, '<br>');
                        console.log('Contenu de timeline traduit avec succès');
                    } else {
                        // En cas de problème, revenir au texte original
                        content.innerHTML = nl2br(originalText);
                        console.log('Échec de traduction du contenu de timeline, texte original conservé');
                    }
                } catch (error) {
                    console.error('Erreur lors de la traduction du contenu de timeline:', error);
                    content.innerHTML = nl2br(originalText);
                }
            }
        }
        
        // 4. Traduire les noms de pays des présidents
        const presidentCountries = document.querySelectorAll('.president-country[data-original-text][data-no-translate="true"]');
        console.log('Pays des présidents à traduire:', presidentCountries.length);
        
        for (const country of presidentCountries) {
            const originalText = country.getAttribute('data-original-text');
            if (originalText) {
                // Afficher un indicateur de chargement
                country.innerHTML = '<em>Traduction en cours...</em>';
                
                try {
                    // Traduire le texte
                    const translatedText = await translateText(originalText, langMap[sourceLang], langMap[targetLang]);
                    
                    // Vérifier que la traduction n'est pas vide ou identique à l'original
                    if (translatedText && translatedText !== originalText) {
                        // Mettre à jour le contenu
                        country.textContent = translatedText;
                        console.log('Pays traduit avec succès');
                    } else {
                        // En cas de problème, revenir au texte original
                        country.textContent = originalText;
                        console.log('Échec de traduction du pays, texte original conservé');
                    }
                } catch (error) {
                    console.error('Erreur lors de la traduction du pays:', error);
                    country.textContent = originalText;
                }
            }
        }
        
        // 5. Traduire les discours des présidents
        const presidentSpeeches = document.querySelectorAll('.president-speech-text[data-original-text][data-no-translate="true"]');
        console.log('Discours des présidents à traduire:', presidentSpeeches.length);
        
        for (const speech of presidentSpeeches) {
            // Stocker le HTML original complet s'il n'est pas déjà stocké
            if (!speech.getAttribute('data-original-html')) {
                speech.setAttribute('data-original-html', speech.innerHTML);
            }
            
            const originalHtml = speech.getAttribute('data-original-html');
            const originalText = speech.getAttribute('data-original-text');
            
            if (originalHtml) {
                // Afficher un indicateur de chargement
                speech.innerHTML = '<em>Traduction en cours...</em>';
                
                try {
                    // Utiliser l'approche minimaliste pour préserver les balises span.bold et les espaces
                    const translatedHtml = await translateHtmlMinimal(originalHtml, langMap[sourceLang], langMap[targetLang]);
                    
                    // Mettre à jour le contenu avec le HTML traduit
                    speech.innerHTML = translatedHtml;
                    console.log('Discours traduit avec succès (HTML préservé)');
                } catch (error) {
                    console.error('Erreur lors de la traduction du discours (HTML):', error);
                    // En cas d'erreur, restaurer le HTML original
                    speech.innerHTML = originalHtml;
                }
            } else if (originalText) {
                // Fallback: utiliser le texte original si le HTML n'est pas disponible
                speech.innerHTML = '<em>Traduction en cours...</em>';
                
                try {
                    // Traduire le texte
                    const translatedText = await translateText(originalText, langMap[sourceLang], langMap[targetLang]);
                    
                    // Vérifier que la traduction n'est pas vide ou identique à l'original
                    if (translatedText && translatedText !== originalText) {
                        // Mettre à jour le contenu avec les sauts de ligne préservés
                        speech.innerHTML = translatedText.replace(/\n/g, '<br>');
                        console.log('Discours traduit avec succès (texte uniquement)');
                    } else {
                        // En cas de problème, revenir au texte original
                        speech.innerHTML = nl2br(originalText);
                        console.log('Échec de traduction du discours, texte original conservé');
                    }
                } catch (error) {
                    console.error('Erreur lors de la traduction du discours (texte):', error);
                    speech.innerHTML = nl2br(originalText);
                }
            }
        }
    }
    
    // Fonction utilitaire pour convertir les sauts de ligne en <br>
    function nl2br(str) {
        if (!str) return '';
        return str.replace(/\n/g, '<br>');
    }
    
    // Fonction pour coordonner les deux systèmes de traduction
    function initializeTranslationSystems() {
        // S'assurer que le formatage en gras est appliqué avant toute traduction
        applyBoldFormattingOnLoad();
        
        // Vérifier si translations.js est chargé
        if (window.translations) {
            console.log('Coordination des systèmes de traduction');
            
            // Écouter les changements de langue pour synchroniser les deux systèmes
            document.addEventListener('languageChanged', function(e) {
                const lang = e.detail.language;
                console.log('Langue changée vers:', lang);
                
                // Appeler translateDynamicContent avec la nouvelle langue
                translateDynamicContent(lang);
            });
        }
    }
    
    // Initialiser la coordination des systèmes de traduction
    initializeTranslationSystems();
    
    // Exposer la fonction de traduction globalement pour qu'elle soit accessible depuis main.js
    window.translateNews = translateDynamicContent; // Garder le même nom pour la compatibilité
});
