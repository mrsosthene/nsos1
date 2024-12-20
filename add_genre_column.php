<?php
require 'config.php';

try {
    $sql = "ALTER TABLE users ADD COLUMN genre VARCHAR(10) NOT NULL AFTER password";
    if ($conn->query($sql)) {
        echo "La colonne 'genre' a été ajoutée avec succès!";
    }
} catch (Exception $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "La colonne 'genre' existe déjà.";
    } else {
        echo "Erreur : " . $e->getMessage();
    }
}

$conn->close();
?>
