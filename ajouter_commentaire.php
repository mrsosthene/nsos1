<?php
session_start();
require_once 'config/connexion.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
    $commentaire = trim($_POST['comment']);
    $is_mobile = isset($_POST['is_mobile']) && $_POST['is_mobile'] === '1';
    
    // Déterminer la page de redirection
    $redirect_page = $is_mobile ? 'mobile_lire_article.php' : 'lire_article.php';

    // Validation simple
    if (empty($article_id) || empty($commentaire)) {
        header("Location: $redirect_page?id=$article_id&error=Tous les champs sont obligatoires#commentaires");
        exit();
    }

    try {
        // Insérer le commentaire
        $stmt = $pdo->prepare("INSERT INTO commentaires (article_id, user_id, contenu, date_creation) VALUES (?, ?, ?, NOW())");
        
        if ($stmt->execute([$article_id, $_SESSION['user_id'], $commentaire])) {
            header("Location: $redirect_page?id=$article_id#commentaires");
            exit();
        } else {
            header("Location: $redirect_page?id=$article_id&error=Erreur lors de l'ajout du commentaire#commentaires");
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: $redirect_page?id=$article_id&error=Erreur lors de l'ajout du commentaire#commentaires");
        exit();
    }
} else {
    header('Location: blog1.php');
    exit();
}
