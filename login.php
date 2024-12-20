<?php
session_start();
require 'config.php';

$error_message = "";
$success_message = "";

if (isset($_SESSION['verification_message'])) {
    $success_message = $_SESSION['verification_message'];
    unset($_SESSION['verification_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Requête pour récupérer l'utilisateur avec son rôle
    $stmt = $pdo->prepare("SELECT id, email, password, prenom, nom, is_verified, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        if (password_verify($password, $result['password'])) {
            if ($result['is_verified'] == 1) {
                // Stockage des informations de session
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['email'] = $result['email'];
                $_SESSION['prenom'] = $result['prenom'];
                $_SESSION['nom'] = $result['nom'];
                $_SESSION['role'] = $result['role'];
                
                // Mise à jour de last_login
                try {
                    $update_stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $update_stmt->execute([$result['id']]);
                } catch (Exception $e) {
                    // Continue même si la mise à jour échoue
                }
                
                // Redirection basée sur le rôle
                if ($result['role'] === "admin") {
                    header("Location: admin.php");
                    exit();
                } else {
                    header("Location: home.php");
                    exit();
                }
            } else {
                $error_message = "Veuillez vérifier votre email avant de vous connecter.";
            }
        } else {
            $error_message = "Email ou mot de passe incorrect.";
        }
    } else {
        $error_message = "Email ou mot de passe incorrect.";
    }
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Nsos Blog</title>
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

        .divider {
            display: flex;
            align-items: center;
            margin: 32px 0;
            color: #707072;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e5e5e5;
        }

        .divider span {
            padding: 0 16px;
            font-size: 14px;
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

        .alert-success {
            background-color: #f0fdf4;
            color: #16a34a;
        }

        .signup-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
        }

        .signup-link a {
            color: #111;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CONNEXION</h1>
            <p>Bienvenue sur Nsos Blog</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" class="form-control" 
                       placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" class="form-control" 
                       placeholder="Mot de passe" required>
            </div>

            <button type="submit" class="btn">
                Se connecter
            </button>
        </form>

        <div class="divider">
            <span>ou</span>
        </div>

        <div class="signup-link">
            <a href="inscription.php">Nouveau sur Nsos Blog ? Rejoignez-nous</a>
        </div>
    </div>
</body>
</html>
