<?php
include('config.php');

$queries = [
    "ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) NULL AFTER date_inscription",
    "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER verification_token",
    "ALTER TABLE users ADD COLUMN token_expiry DATETIME NULL AFTER is_verified"
];

foreach ($queries as $sql) {
    try {
        if ($conn->query($sql)) {
            echo "Succès: " . $sql . "<br>";
        } else {
            echo "Erreur: " . $conn->error . "<br>";
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "<br>";
    }
}

$conn->close();
echo "Terminé!";
?>
