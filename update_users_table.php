<?php
include('config.php');

$sql = "ALTER TABLE users 
        ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL,
        ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
        ADD COLUMN token_expiry DATETIME DEFAULT NULL";

try {
    if ($conn->query($sql) === TRUE) {
        echo "Table users modifiée avec succès";
    } else {
        echo "Erreur lors de la modification de la table: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}

$conn->close();
?>
