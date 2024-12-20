<?php
require 'config.php';

try {
    // Ajouter la colonne last_login
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL";
    $conn->query($sql);
    
    echo "Colonne 'last_login' ajoutée avec succès à la table users!";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
