<?php
include('config.php');

// Ajout des colonnes pour la vérification par email
$sql = "ALTER TABLE users 
        ADD COLUMN verification_token VARCHAR(255) NULL AFTER date_inscription,
        ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER verification_token,
        ADD COLUMN token_expiry DATETIME NULL AFTER is_verified";

try {
    if ($conn->query($sql)) {
        echo "Colonnes de vérification ajoutées avec succès!";
    } else {
        echo "Erreur lors de l'ajout des colonnes: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}

$conn->close();
?>
