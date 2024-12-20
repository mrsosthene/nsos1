<?php
include('config.php');
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Vérifier le token dans la base de données
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Mettre à jour le statut de vérification
        $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $updateStmt->bind_param("i", $user['id']);
        
        if ($updateStmt->execute()) {
            $_SESSION['verification_message'] = "Votre email a été vérifié avec succès. Vous pouvez maintenant vous connecter.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['verification_message'] = "Lien de vérification invalide ou déjà utilisé.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
