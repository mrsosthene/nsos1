<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));

    // Validation
    if (empty($email)) {
        $error_message = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format d'email invalide.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        
        if ($result) {
            $error_message = "Cet email est déjà utilisé. Veuillez vous connecter ou utiliser une autre adresse email.";
        } else {
            // Générer le token de vérification
            $verification_token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Insertion dans la base de données avec des valeurs temporaires
            $temp_password = bin2hex(random_bytes(8)); // Mot de passe temporaire
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, verification_token, token_expiry, is_verified) VALUES (?, ?, ?, ?, 0)");
            
            if ($stmt->execute([$email, $hashed_password, $verification_token, $token_expiry])) {
                // Envoi de l'email de vérification
                $mail = new PHPMailer(true);
                
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'soskouch@gmail.com';
                    $mail->Password = 'zqvy ftyu yrwx spij';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    
                    $mail->setFrom('soskouch@gmail.com', 'Nsos Blog');
                    $mail->addAddress($email);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Finalisez votre inscription sur Nsos Blog';
                    
                    $verification_link = "http://localhost/blog2/complete_registration.php?token=" . $verification_token;
                    
                    $mail->Body = "
                        <h2>Bienvenue sur Nsos Blog !</h2>
                        <p>Cliquez sur le lien ci-dessous pour finaliser votre inscription et configurer votre compte :</p>
                        <p><a href='{$verification_link}'>Finaliser mon inscription</a></p>
                        <p>Ce lien expirera dans 24 heures.</p>
                        <p>Si vous n'avez pas créé de compte, vous pouvez ignorer cet email.</p>
                    ";
                    
                    $mail->send();
                    $success_message = "Un email a été envoyé à votre adresse. Veuillez cliquer sur le lien dans l'email pour finaliser votre inscription.";
                    
                } catch (Exception $e) {
                    $error_message = "L'email n'a pas pu être envoyé. Veuillez réessayer plus tard.";
                }
            } else {
                $error_message = "Une erreur est survenue. Veuillez réessayer.";
            }
        }
    }
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoignez Nsos Blog</title>
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

        .login-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
        }

        .login-link a {
            color: #111;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>REJOIGNEZ-NOUS</h1>
            <p>Entrez votre email pour commencer</p>
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

        <form action="inscription.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" class="form-control" 
                       placeholder="Adresse email" required 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>

            <button type="submit" class="btn">
                Continuer
            </button>
        </form>

        <div class="divider">
            <span>ou</span>
        </div>

        <div class="login-link">
            <a href="login.php">Déjà membre ? Connectez-vous</a>
        </div>
    </div>
</body>
</html>
