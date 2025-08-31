<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Seule la méthode POST est autorisée'], 405);
}

try {
    handleImageUpload();
} catch (Exception $e) {
    sendResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}

function handleImageUpload() {
    if (!isset($_FILES['profile_image'])) {
        sendResponse(['error' => 'Aucun fichier fourni'], 400);
    }
    
    $file = $_FILES['profile_image'];
    $inscriptionId = $_POST['inscription_id'] ?? null;
    
    if (!$inscriptionId) {
        sendResponse(['error' => 'ID inscription requis'], 400);
    }
    
    // Validation du fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        sendResponse(['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP'], 400);
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        sendResponse(['error' => 'Fichier trop volumineux. Maximum 5MB'], 400);
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        sendResponse(['error' => 'Erreur lors du téléversement'], 400);
    }
    
    // Créer le dossier s'il n'existe pas
    $uploadDir = '../assets/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Générer un nom unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $inscriptionId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Redimensionner l'image
    if (resizeImage($file['tmp_name'], $filepath, 300, 300)) {
        // Mettre à jour la base de données
        $pdo = getDBConnection();
        
        try {
            // Supprimer l'ancienne image si elle existe
            $stmt = $pdo->prepare("SELECT profile_image FROM inscriptions WHERE id = ?");
            $stmt->execute([$inscriptionId]);
            $oldImage = $stmt->fetchColumn();
            
            if ($oldImage && $oldImage !== 'default-avatar.png' && file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }
            
            // Mettre à jour avec la nouvelle image
            $stmt = $pdo->prepare("UPDATE inscriptions SET profile_image = ? WHERE id = ?");
            $stmt->execute([$filename, $inscriptionId]);
            
            if ($stmt->rowCount() === 0) {
                unlink($filepath); // Supprimer le fichier si l'inscription n'existe pas
                sendResponse(['error' => 'Inscription non trouvée'], 404);
            }
            
            sendResponse([
                'success' => true,
                'message' => 'Image téléversée avec succès',
                'filename' => $filename,
                'url' => '/assets/profiles/' . $filename
            ]);
            
        } catch (PDOException $e) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            sendResponse(['error' => 'Erreur base de données: ' . $e->getMessage()], 500);
        }
    } else {
        sendResponse(['error' => 'Erreur lors du redimensionnement de l\'image'], 500);
    }
}

function resizeImage($source, $destination, $maxWidth, $maxHeight) {
    $imageInfo = getimagesize($source);
    if (!$imageInfo) {
        return false;
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Calculer les nouvelles dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Créer l'image source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // Créer la nouvelle image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Préserver la transparence pour PNG et GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Redimensionner
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Sauvegarder
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($newImage, $destination, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($newImage, $destination, 6);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($newImage, $destination);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($newImage, $destination, 85);
            break;
    }
    
    // Nettoyer la mémoire
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}
?>