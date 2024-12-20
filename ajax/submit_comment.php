<?php
session_start();
require_once '../config/connexion.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour commenter']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
    $commentaire = trim($_POST['comment']);

    // Validation
    if (empty($article_id) || empty($commentaire)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
        exit();
    }

    try {
        // Vérifier si l'article existe
        $stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
            exit();
        }

        // Insérer le commentaire
        $stmt = $pdo->prepare("INSERT INTO commentaires (article_id, user_id, contenu, date_creation) VALUES (?, ?, ?, NOW())");
        
        if ($stmt->execute([$article_id, $_SESSION['user_id'], $commentaire])) {
            echo json_encode(['success' => true, 'message' => 'Commentaire ajouté avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire']);
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du commentaire']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
