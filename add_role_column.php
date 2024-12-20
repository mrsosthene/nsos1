<?php
require 'config.php';

try {
    // Ajouter la colonne role si elle n'existe pas
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'user'";
    $conn->query($sql);
    
    // Mettre à jour l'utilisateur admin existant ou en créer un
    $email_admin = 'admin@admin.com';
    $sql = "UPDATE users SET role = 'admin' WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email_admin);
    $stmt->execute();
    
    if ($stmt->affected_rows == 0) {
        echo "Aucun utilisateur admin trouvé avec l'email admin@admin.com<br>";
    } else {
        echo "Role admin attribué avec succès!<br>";
    }
    
    echo "Colonne 'role' ajoutée avec succès à la table users!";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
