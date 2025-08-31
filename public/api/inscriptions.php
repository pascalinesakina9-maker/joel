<?php
require_once 'config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetInscriptions($pdo);
            break;
            
        case 'POST':
            handleAddInscription($pdo);
            break;
            
        case 'PUT':
            handleUpdateStatus($pdo);
            break;
            
        case 'DELETE':
            handleDeleteInscription($pdo);
            break;
            
        default:
            sendResponse(['error' => 'Méthode non autorisée'], 405);
    }
} catch (Exception $e) {
    sendResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}

function handleGetInscriptions($pdo) {
    $stmt = $pdo->query("
        SELECT * FROM inscriptions 
        ORDER BY date_inscription DESC
    ");
    
    $inscriptions = $stmt->fetchAll();
    sendResponse($inscriptions);
}

function handleAddInscription($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    $error = validateInscription($input);
    if ($error) {
        sendResponse(['error' => $error], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO inscriptions 
            (nom, prenom, date_naissance, classe, parent_nom, parent_tel, document, statut, paiement_statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'reçu', 'non payé')
        ");
        
        $stmt->execute([
            $input['nom'],
            $input['prenom'],
            $input['date_naissance'],
            $input['classe'],
            $input['parent_nom'],
            $input['parent_tel'],
            $input['document'] ?? ''
        ]);
        
        $id = $pdo->lastInsertId();
        
        sendResponse([
            'success' => true,
            'message' => 'Inscription ajoutée avec succès',
            'id' => $id
        ], 201);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()], 500);
    }
}

function handleUpdateStatus($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id || !$input['statut']) {
        sendResponse(['error' => 'ID et statut requis'], 400);
    }
    
    $validStatuts = ['reçu', 'incomplet', 'validé', 'rejeté'];
    if (!in_array($input['statut'], $validStatuts)) {
        sendResponse(['error' => 'Statut invalide'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE inscriptions 
            SET statut = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$input['statut'], $id]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(['error' => 'Inscription non trouvée'], 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Statut mis à jour avec succès'
        ]);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], 500);
    }
}

function handleDeleteInscription($pdo) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(['error' => 'ID requis'], 400);
    }
    
    try {
        // Supprimer d'abord les paiements associés
        $stmt = $pdo->prepare("DELETE FROM paiements WHERE inscription_id = ?");
        $stmt->execute([$id]);
        
        // Puis supprimer l'inscription
        $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(['error' => 'Inscription non trouvée'], 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Inscription supprimée avec succès'
        ]);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
    }
}
?>