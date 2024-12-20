<?php
header('Content-Type: application/json');

// Vérifier si un fichier a été uploadé
if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    // Vérifier les erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement']);
        exit;
    }

    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']);
        exit;
    }

    // Créer le dossier uploads s'il n'existe pas
    $upload_dir = 'uploads';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;

    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode([
            'success' => true,
            'file' => [
                'url' => $filepath,
                'name' => $filename
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du fichier']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
}
