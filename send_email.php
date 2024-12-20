<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendVerificationEmail($userEmail, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'soskouch@gmail.com'; // Votre email
        $mail->Password = 'zqvy ftyu yrwx spij'; // Votre mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Destinataires
        $mail->setFrom('soskouch@gmail.com', 'Nsos Blog');
        $mail->addAddress($userEmail);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Vérification de votre compte Nsos Blog';
        
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/blog1/verify_email.php?token=" . $token;
        
        $mail->Body = "
            <h2>Bienvenue sur Nsos Blog!</h2>
            <p>Merci de votre inscription. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href='$verificationLink'>Vérifier mon compte</a></p>
            <p>Si le lien ne fonctionne pas, copiez et collez l'URL suivante dans votre navigateur :</p>
            <p>$verificationLink</p>
            <p>Ce lien expirera dans 24 heures.</p>
            <br>
            <p>Cordialement,<br>L'équipe Nsos Blog</p>
        ";

        $mail->AltBody = "
            Bienvenue sur Nsos Blog!
            
            Merci de votre inscription. Pour activer votre compte, veuillez copier et coller le lien suivant dans votre navigateur :
            
            $verificationLink
            
            Ce lien expirera dans 24 heures.
            
            Cordialement,
            L'équipe Nsos Blog
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email : " . $mail->ErrorInfo);
        return false;
    }
}
?>
