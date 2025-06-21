// Fichier pour la traduction automatique des contenus dynamiques via proxy PHP
document.addEventListener('DOMContentLoaded', function() {
    // Cache pour stocker les traductions déjà effectuées
    const translationCache = {};
    
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
    
    // Fonction améliorée pour extraire le contenu HTML et préserver les balises <span class="bold">
    // ainsi que les espaces entre les mots
    function extractHtmlSegments(htmlContent) {
        // Créer un conteneur temporaire pour parser le HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        
        // Tableau pour stocker les segments (texte normal et texte en gras)
        const segments = [];
        
        // Fonction récursive pour parcourir les nœuds
        function processNode(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                // C'est un nœud texte, l'ajouter comme segment de texte normal
                // Même si c'est juste un espace, on le préserve
                if (node.textContent !== '') {
                    segments.push({
                        type: 'text',
                        content: node.textContent
                    });
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                // C'est un élément HTML
                if (node.nodeName === 'SPAN' && node.classList.contains('bold')) {
                    // C'est un span avec la classe bold, l'ajouter comme segment en gras
                    segments.push({
                        type: 'bold',
                        content: node.textContent
                    });
                } else if (node.nodeName === 'SPAN') {
                    // C'est un span sans la classe bold, l'ajouter comme segment span générique
                    // On capture les attributs pour les préserver
                    const attributes = {};
                    for (let i = 0; i < node.attributes.length; i++) {
                        const attr = node.attributes[i];
                        if (attr.name !== 'class' || attr.value !== 'bold') {
                            attributes[attr.name] = attr.value;
                        }
                    }
                    
                    // Créer un segment de type span avec ses attributs et son contenu
                    segments.push({
                        type: 'span',
                        attributes: attributes,
                        content: node.textContent
                    });
                } else if (node.nodeName === 'BR') {
                    // C'est un saut de ligne, l'ajouter comme segment spécial
                    segments.push({
                        type: 'linebreak',
                        content: '\n'
                    });
                } else {
                    // Pour les autres éléments, parcourir leurs enfants
                    for (const childNode of node.childNodes) {
                        processNode(childNode);
                    }
                }
            }
        }
        
        // Parcourir tous les nœuds du conteneur temporaire
        for (const childNode of tempDiv.childNodes) {
            processNode(childNode);
        }
        
        return segments;
    }
    
    // Fonction améliorée pour reconstruire le HTML à partir des segments traduits
    // en préservant les espaces entre les mots
    function reconstructHtml(segments) {
        let html = '';
        
        for (const segment of segments) {
            if (segment.type === 'text') {
                html += segment.content;
            } else if (segment.type === 'bold') {
                html += `<span class="bold">${segment.content}</span>`;
            } else if (segment.type === 'span') {
                // Reconstruire les attributs du span
                let attrs = '';
                for (const [name, value] of Object.entries(segment.attributes)) {
                    attrs += ` ${name}="${value}"`;
                }
                html += `<span${attrs}>${segment.content}</span>`;
            } else if (segment.type === 'linebreak') {
                html += '<br>';
            }
        }
        
        return html;
    }
    
    // Fonction pour traduire du contenu HTML en préservant les balises
    async function translateHtml(htmlContent, sourceLang, targetLang) {
        // Si le contenu est vide, retourner une chaîne vide
        if (!htmlContent) return '';
        
        // Si les langues sont identiques, retourner le contenu original
        if (sourceLang === targetLang) return htmlContent;
        
        try {
            // 1. Extraire les segments (texte normal, texte en gras, spans génériques, sauts de ligne)
            const segments = extractHtmlSegments(htmlContent);
            
            // 2. Traduire chaque segment de texte
            for (const segment of segments) {
                if (segment.type === 'text' || segment.type === 'bold' || segment.type === 'span') {
                    // Traduire uniquement les segments de texte, de texte en gras et de spans génériques
                    segment.content = await translateText(segment.content, sourceLang, targetLang);
                }
            }
            
            // 3. Reconstruire le HTML avec les segments traduits
            return reconstructHtml(segments);
        } catch (error) {
            console.error('Erreur lors de la traduction HTML:', error);
            return htmlContent; // En cas d'erreur, retourner le contenu original
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
                    // Traduire le HTML en préservant les balises
                    const translatedHtml = await translateHtml(originalHtml, langMap[sourceLang], langMap[targetLang]);
                    
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
    
    // Exposer la fonction de traduction globalement pour qu'elle soit accessible depuis main.js
    window.translateNews = translateDynamicContent; // Garder le même nom pour la compatibilité
});
