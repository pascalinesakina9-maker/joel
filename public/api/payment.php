<?php
require_once 'config.php';

$pdo = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    sendResponse(['error' => 'Seule la méthode POST est autorisée'], 405);
}

try {
    handlePayment($pdo);
} catch (Exception $e) {
    sendResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}

function handlePayment($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $inscriptionId = $input['inscription_id'] ?? null;
    $montant = $input['montant'] ?? null;
    
    if (!$inscriptionId || !$montant) {
        sendResponse(['error' => 'ID inscription et montant requis'], 400);
    }
    
    if (!is_numeric($montant) || $montant <= 0) {
        sendResponse(['error' => 'Montant invalide'], 400);
    }
    
    try {
        // Vérifier que l'inscription existe
        $stmt = $pdo->prepare("SELECT id FROM inscriptions WHERE id = ?");
        $stmt->execute([$inscriptionId]);
        
        if (!$stmt->fetch()) {
            sendResponse(['error' => 'Inscription non trouvée'], 404);
        }
        
        // Commencer la transaction
        $pdo->beginTransaction();
        
        // Générer un ID de transaction unique
        $transactionId = 'SIM_' . date('YmdHis') . '_' . uniqid();
        
        // Enregistrer le paiement
        $stmt = $pdo->prepare("
            INSERT INTO paiements 
            (inscription_id, montant, mode, transaction_id, date_paiement)
            VALUES (?, ?, 'simulation', ?, NOW())
        ");
        
        $stmt->execute([$inscriptionId, $montant, $transactionId]);
        
        // Mettre à jour le statut de paiement de l'inscription
        $stmt = $pdo->prepare("
            UPDATE inscriptions 
            SET paiement_statut = 'payé' 
            WHERE id = ?
        ");
        
        $stmt->execute([$inscriptionId]);
        
        // Valider la transaction
        $pdo->commit();
        
        sendResponse([
            'success' => true,
            'message' => 'Paiement simulé avec succès',
            'transaction_id' => $transactionId,
            'montant' => $montant,
            'date_paiement' => date('Y-m-d H:i:s')
        ], 201);
        
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollback();
        sendResponse(['error' => 'Erreur lors du paiement: ' . $e->getMessage()], 500);
    }
}
?>