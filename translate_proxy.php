<?php
/**
 * Proxy PHP pour les requêtes de traduction
 * Contourne les problèmes CORS en relayant les requêtes depuis le serveur
 */

// Autoriser les requêtes depuis le même domaine
header('Content-Type: application/json');

// Fonction pour traduire via MyMemory API
function translateWithMyMemory($text, $sourceLang, $targetLang) {
    // Construire l'URL avec les paramètres
    $url = 'https://api.mymemory.translated.net/get?'
         . 'q=' . urlencode($text)
         . '&langpair=' . $sourceLang . '|' . $targetLang
         . '&de=admin@tif-tournament.com'; // Email fictif pour augmenter la limite quotidienne
    
    // Initialiser cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Vérifier la réponse
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if (isset($data['responseData']) && isset($data['responseData']['translatedText'])) {
            return [
                'success' => true,
                'translatedText' => $data['responseData']['translatedText']
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'Échec de la traduction avec MyMemory'
    ];
}

// Fonction pour traduire via Google Translate (méthode non officielle)
function translateWithGoogle($text, $sourceLang, $targetLang) {
    // Construire l'URL avec les paramètres
    $url = 'https://translate.googleapis.com/translate_a/single?'
         . 'client=gtx'
         . '&sl=' . $sourceLang
         . '&tl=' . $targetLang
         . '&dt=t'
         . '&q=' . urlencode($text);
    
    // Initialiser cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36');
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Vérifier la réponse
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if (isset($data[0]) && is_array($data[0])) {
            $translatedText = '';
            foreach ($data[0] as $part) {
                if (isset($part[0])) {
                    $translatedText .= $part[0];
                }
            }
            
            if (!empty($translatedText)) {
                return [
                    'success' => true,
                    'translatedText' => $translatedText
                ];
            }
        }
    }
    
    return [
        'success' => false,
        'error' => 'Échec de la traduction avec Google'
    ];
}

// Vérifier si les paramètres requis sont présents
if (!isset($_POST['text']) || !isset($_POST['source']) || !isset($_POST['target'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Paramètres manquants'
    ]);
    exit;
}

// Récupérer les paramètres
$text = $_POST['text'];
$sourceLang = $_POST['source'];
$targetLang = $_POST['target'];

// Si le texte est vide ou les langues sont identiques, renvoyer le texte original
if (empty($text) || $sourceLang === $targetLang) {
    echo json_encode([
        'success' => true,
        'translatedText' => $text
    ]);
    exit;
}

// Essayer d'abord avec MyMemory
$result = translateWithMyMemory($text, $sourceLang, $targetLang);

// Si MyMemory échoue, essayer avec Google
if (!$result['success']) {
    $result = translateWithGoogle($text, $sourceLang, $targetLang);
}

// Renvoyer le résultat
echo json_encode($result);
