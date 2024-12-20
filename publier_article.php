<?php
session_start();
require_once 'config/connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Récupérer le titre de l'article
        $stmt = $pdo->prepare("SELECT titre FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        // Mise à jour du statut de l'article en "publié"
        $stmt = $pdo->prepare("UPDATE articles SET statut = 'publie' WHERE id = ?");
        $stmt->execute([$id]);
        
        // Message de succès
        $_SESSION['message'] = "L'article \"" . htmlspecialchars($article['titre']) . "\" a été publié avec succès !";
        $_SESSION['message_type'] = "success";
        
        header("Location: articles.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['message'] = "Erreur lors de la publication : " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: articles.php");
        exit();
    }
} else {
    header("Location: articles.php");
    exit();
}
?>
