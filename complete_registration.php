<?php
session_start();
require 'config.php';

$error_message = "";
$success_message = "";
$email = "";

if (!isset($_GET['token'])) {
    header("Location: inscription.php");
    exit();
}

$token = $_GET['token'];

try {
    // Vérifier si le token est valide et non expiré
    $stmt = $pdo->prepare("SELECT id, email, token_expiry FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: inscription.php");
        exit();
    }

    $email = $user['email'];

    if (strtotime($user['token_expiry']) < time()) {
        $error_message = "Ce lien a expiré. Veuillez vous réinscrire.";
    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
        $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''));
        $nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
        $password = htmlspecialchars(trim($_POST['password'] ?? ''));
        $confirm_password = htmlspecialchars(trim($_POST['confirm_password'] ?? ''));

        // Validation
        if (empty($prenom) || empty($nom) || empty($password) || empty($confirm_password)) {
            $error_message = "Tous les champs sont obligatoires.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($password) < 8) {
            $error_message = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Mise à jour du profil
            $update_stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ?, password = ?, is_verified = 1, verification_token = NULL WHERE id = ?");
            
            if ($update_stmt->execute([$prenom, $nom, $hashed_password, $user['id']])) {
                $_SESSION['verification_message'] = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Une erreur est survenue. Veuillez réessayer.";
            }
        }
    }
} catch (PDOException $e) {
    $error_message = "Une erreur est survenue. Veuillez réessayer plus tard.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser l'inscription - Nsos Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: white;
            color: #111;
        }

        .container {
            max-width: 460px;
            margin: 0 auto;
            padding: 48px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 48px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .header p {
            color: #707072;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .form-control {
            width: 100%;
            padding: 18px 16px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            background-color: #f5f5f5;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            background-color: #e8e8e8;
        }

        .btn {
            width: 100%;
            padding: 20px;
            font-size: 16px;
            font-weight: 500;
            color: white;
            background-color: black;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .alert {
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #dc2626;
        }

        @media (max-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FINALISEZ VOTRE INSCRIPTION</h1>
            <p><?php echo htmlspecialchars($email); ?></p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="?token=<?php echo htmlspecialchars($token); ?>" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <input type="text" name="prenom" class="form-control" 
                           placeholder="Prénom" required>
                </div>
                <div class="form-group">
                    <input type="text" name="nom" class="form-control" 
                           placeholder="Nom" required>
                </div>
            </div>

            <div class="form-group">
                <input type="password" name="password" class="form-control" 
                       placeholder="Mot de passe" required>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_password" class="form-control" 
                       placeholder="Confirmer le mot de passe" required>
            </div>

            <button type="submit" class="btn">
                Créer mon compte
            </button>
        </form>
    </div>
</body>
</html>
