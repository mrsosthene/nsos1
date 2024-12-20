<?php
include('config.php');

// Vérifier si les colonnes existent déjà
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'verification_token'");
$verification_token_exists = $result->num_rows > 0;

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
$is_verified_exists = $result->num_rows > 0;

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'token_expiry'");
$token_expiry_exists = $result->num_rows > 0;

// Construire la requête ALTER TABLE en fonction des colonnes manquantes
$alter_queries = array();

if (!$verification_token_exists) {
    $alter_queries[] = "ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL";
}

if (!$is_verified_exists) {
    $alter_queries[] = "ADD COLUMN is_verified TINYINT(1) DEFAULT 0";
}

if (!$token_expiry_exists) {
    $alter_queries[] = "ADD COLUMN token_expiry DATETIME DEFAULT NULL";
}

// S'il y a des colonnes à ajouter
if (!empty($alter_queries)) {
    $sql = "ALTER TABLE users " . implode(", ", $alter_queries);
    
    try {
        if ($conn->query($sql)) {
            echo "Table users mise à jour avec succès.<br>";
        } else {
            echo "Erreur lors de la mise à jour de la table: " . $conn->error . "<br>";
        }
    } catch (Exception $e) {
        echo "Une erreur est survenue : " . $e->getMessage() . "<br>";
    }
} else {
    echo "La table est déjà à jour.<br>";
}

// Afficher la structure actuelle de la table
echo "<h3>Structure actuelle de la table users :</h3>";
$result = $conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo "Colonne: " . $row['Field'] . " - Type: " . $row['Type'] . "<br>";
}

$conn->close();
?>
