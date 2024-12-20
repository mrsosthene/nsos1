<?php
session_start();
require 'config.php';

$error_message = "";
$success_message = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Vérifier si le token existe et n'est pas expiré
    $stmt = $conn->prepare("SELECT id, email, token_expiry FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Vérifier si le token n'est pas expiré
        if (strtotime($user['token_expiry']) > time()) {
            // Mettre à jour le statut de vérification
            $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            
            if ($update_stmt->execute()) {
                $success_message = "Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.";
                $_SESSION['verification_message'] = "Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.";
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Une erreur est survenue lors de la vérification de votre compte.";
            }
            $update_stmt->close();
        } else {
            $error_message = "Le lien de vérification a expiré. Veuillez vous réinscrire.";
        }
    } else {
        $error_message = "Lien de vérification invalide ou compte déjà vérifié.";
    }
    $stmt->close();
} else {
    $error_message = "Token de vérification manquant.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du compte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-center mb-4">Vérification du compte</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="login.php" class="text-blue-500 hover:underline">Retour à la page de connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
