<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'ep_matendo_inscriptions');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Gestion de la requête OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Connexion à la base de données
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur de connexion à la base de données']);
        exit;
    }
}

// Fonction pour envoyer une réponse JSON
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fonction pour valider les données d'inscription
function validateInscription($data) {
    $required = ['nom', 'prenom', 'date_naissance', 'classe', 'parent_nom', 'parent_tel'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return "Le champ '$field' est requis";
        }
    }
    
    // Validation téléphone
    if (!preg_match('/^[+]?[\d\s\-()]+$/', $data['parent_tel'])) {
        return "Format de téléphone invalide";
    }
    
    // Validation date
    if (!DateTime::createFromFormat('Y-m-d', $data['date_naissance'])) {
        return "Format de date invalide";
    }
    
    return null;
}
?>