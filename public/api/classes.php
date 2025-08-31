<?php
require_once 'config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetClasses($pdo);
            break;
            
        case 'POST':
            handleAddClasse($pdo);
            break;
            
        case 'PUT':
            handleUpdateClasse($pdo);
            break;
            
        case 'DELETE':
            handleDeleteClasse($pdo);
            break;
            
        default:
            sendResponse(['error' => 'Méthode non autorisée'], 405);
    }
} catch (Exception $e) {
    sendResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}

function handleGetClasses($pdo) {
    $stmt = $pdo->query("
        SELECT c.*, 
               COUNT(i.id) as nombre_eleves,
               SUM(CASE WHEN i.paiement_statut = 'payé' THEN 1 ELSE 0 END) as eleves_payes
        FROM classes c 
        LEFT JOIN inscriptions i ON c.id = i.classe_id 
        GROUP BY c.id 
        ORDER BY c.niveau, c.nom
    ");
    
    $classes = $stmt->fetchAll();
    sendResponse($classes);
}

function handleAddClasse($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $error = validateClasse($input);
    if ($error) {
        sendResponse(['error' => $error], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO classes (nom, niveau, frais_scolarite, capacite_max, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $input['nom'],
            $input['niveau'],
            $input['frais_scolarite'] ?? 50000,
            $input['capacite_max'] ?? 30,
            $input['description'] ?? ''
        ]);
        
        $id = $pdo->lastInsertId();
        
        sendResponse([
            'success' => true,
            'message' => 'Classe ajoutée avec succès',
            'id' => $id
        ], 201);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            sendResponse(['error' => 'Cette classe existe déjà'], 409);
        }
        sendResponse(['error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()], 500);
    }
}

function handleUpdateClasse($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(['error' => 'ID requis'], 400);
    }
    
    $error = validateClasse($input);
    if ($error) {
        sendResponse(['error' => $error], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE classes 
            SET nom = ?, niveau = ?, frais_scolarite = ?, capacite_max = ?, description = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['nom'],
            $input['niveau'],
            $input['frais_scolarite'],
            $input['capacite_max'],
            $input['description'],
            $id
        ]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(['error' => 'Classe non trouvée'], 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Classe mise à jour avec succès'
        ]);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], 500);
    }
}

function handleDeleteClasse($pdo) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(['error' => 'ID requis'], 400);
    }
    
    try {
        // Vérifier s'il y a des élèves dans cette classe
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM inscriptions WHERE classe_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            sendResponse(['error' => 'Impossible de supprimer une classe contenant des élèves'], 400);
        }
        
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(['error' => 'Classe non trouvée'], 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Classe supprimée avec succès'
        ]);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
    }
}

function validateClasse($data) {
    if (empty($data['nom'])) {
        return "Le nom de la classe est requis";
    }
    
    if (empty($data['niveau']) || !in_array($data['niveau'], ['primaire', 'secondaire'])) {
        return "Le niveau doit être 'primaire' ou 'secondaire'";
    }
    
    if (isset($data['frais_scolarite']) && (!is_numeric($data['frais_scolarite']) || $data['frais_scolarite'] < 0)) {
        return "Les frais de scolarité doivent être un nombre positif";
    }
    
    if (isset($data['capacite_max']) && (!is_numeric($data['capacite_max']) || $data['capacite_max'] < 1)) {
        return "La capacité maximale doit être un nombre positif";
    }
    
    return null;
}
?>