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
            handleUpdateInscription($pdo);
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
    $classeId = $_GET['classe_id'] ?? null;
    $statut = $_GET['statut'] ?? null;
    $paiement = $_GET['paiement'] ?? null;
    
    $sql = "
        SELECT i.*, 
               c.nom as classe_nom, 
               c.frais_scolarite,
               u.nom as parent_nom_complet,
               u.email as parent_email
        FROM inscriptions i 
        LEFT JOIN classes c ON i.classe_id = c.id
        LEFT JOIN utilisateurs u ON i.parent_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($classeId) {
        $sql .= " AND i.classe_id = ?";
        $params[] = $classeId;
    }
    
    if ($statut) {
        $sql .= " AND i.statut = ?";
        $params[] = $statut;
    }
    
    if ($paiement) {
        $sql .= " AND i.paiement_statut = ?";
        $params[] = $paiement;
    }
    
    $sql .= " ORDER BY i.date_inscription DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $inscriptions = $stmt->fetchAll();
    
    // Ajouter l'URL complète de l'image de profil
    foreach ($inscriptions as &$inscription) {
        $inscription['profile_image_url'] = '/assets/profiles/' . ($inscription['profile_image'] ?: 'default-avatar.png');
    }
    
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
        // Récupérer l'ID de la classe
        $stmt = $pdo->prepare("SELECT id FROM classes WHERE nom = ?");
        $stmt->execute([$input['classe']]);
        $classe = $stmt->fetch();
        
        if (!$classe) {
            sendResponse(['error' => 'Classe non trouvée'], 400);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO inscriptions 
            (nom, prenom, date_naissance, classe, classe_id, parent_nom, parent_tel, 
             adresse, document, statut, paiement_statut, profile_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'reçu', 'non payé', 'default-avatar.png')
        ");
        
        $stmt->execute([
            $input['nom'],
            $input['prenom'],
            $input['date_naissance'],
            $input['classe'],
            $classe['id'],
            $input['parent_nom'],
            $input['parent_tel'],
            $input['adresse'] ?? '',
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

function handleUpdateInscription($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        sendResponse(['error' => 'ID requis'], 400);
    }
    
    // Si c'est juste une mise à jour de statut
    if (isset($input['statut']) && count($input) === 1) {
        handleUpdateStatus($pdo, $id, $input['statut']);
        return;
    }
    
    // Validation complète pour mise à jour complète
    $error = validateInscription($input);
    if ($error) {
        sendResponse(['error' => $error], 400);
    }
    
    try {
        // Récupérer l'ID de la classe si nécessaire
        $classeId = null;
        if (isset($input['classe'])) {
            $stmt = $pdo->prepare("SELECT id FROM classes WHERE nom = ?");
            $stmt->execute([$input['classe']]);
            $classe = $stmt->fetch();
            $classeId = $classe ? $classe['id'] : null;
        }
        
        $stmt = $pdo->prepare("
            UPDATE inscriptions 
            SET nom = ?, prenom = ?, date_naissance = ?, classe = ?, classe_id = ?,
                parent_nom = ?, parent_tel = ?, adresse = ?, notes = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['nom'],
            $input['prenom'],
            $input['date_naissance'],
            $input['classe'],
            $classeId,
            $input['parent_nom'],
            $input['parent_tel'],
            $input['adresse'] ?? '',
            $input['notes'] ?? '',
            $id
        ]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(['error' => 'Inscription non trouvée'], 404);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Inscription mise à jour avec succès'
        ]);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], 500);
    }
}

function handleUpdateStatus($pdo, $id, $statut) {
    $validStatuts = ['reçu', 'incomplet', 'validé', 'rejeté'];
    if (!in_array($statut, $validStatuts)) {
        sendResponse(['error' => 'Statut invalide'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE inscriptions SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);
        
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
        // Récupérer l'image de profil pour la supprimer
        $stmt = $pdo->prepare("SELECT profile_image FROM inscriptions WHERE id = ?");
        $stmt->execute([$id]);
        $inscription = $stmt->fetch();
        
        if (!$inscription) {
            sendResponse(['error' => 'Inscription non trouvée'], 404);
        }
        
        // Supprimer d'abord les paiements associés
        $stmt = $pdo->prepare("DELETE FROM paiements WHERE inscription_id = ?");
        $stmt->execute([$id]);
        
        // Puis supprimer l'inscription
        $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id = ?");
        $stmt->execute([$id]);
        
        // Supprimer l'image de profil si ce n'est pas l'image par défaut
        if ($inscription['profile_image'] && 
            $inscription['profile_image'] !== 'default-avatar.png' && 
            file_exists('../assets/profiles/' . $inscription['profile_image'])) {
            unlink('../assets/profiles/' . $inscription['profile_image']);
        }
        
        sendResponse([
            'success' => true,
            'message' => 'Inscription supprimée avec succès'
        ]);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
    }
}

// Fonction de validation mise à jour
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
    
    // Validation âge (entre 5 et 25 ans)
    $birthDate = new DateTime($data['date_naissance']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    if ($age < 5 || $age > 25) {
        return "L'âge doit être entre 5 et 25 ans";
    }
    
    return null;
}
?>