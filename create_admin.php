<?php
require 'config.php';

// Informations de l'administrateur
$email = 'admin@admin.com';
$password = 'admin123'; // Vous pourrez changer ce mot de passe plus tard
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$prenom = 'Admin';
$nom = 'Admin';
$is_verified = 1;
$is_admin = 1;

// Vérifier si l'admin existe déjà
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    // L'admin n'existe pas, on le crée
    $stmt = $conn->prepare("INSERT INTO users (email, password, prenom, nom, is_verified, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $email, $hashed_password, $prenom, $nom, $is_verified, $is_admin);
    
    if ($stmt->execute()) {
        echo "Compte administrateur créé avec succès!<br>";
        echo "Email: " . $email . "<br>";
        echo "Mot de passe: " . $password . "<br>";
    } else {
        echo "Erreur lors de la création du compte administrateur: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Un compte administrateur existe déjà avec cet email.";
}

$check_stmt->close();
$conn->close();
?>
