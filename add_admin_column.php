<?php
require 'config.php';

$sql = file_get_contents('add_admin_column.sql');

if ($conn->query($sql) === TRUE) {
    echo "Colonne is_admin ajoutée avec succès!";
} else {
    echo "Erreur lors de l'ajout de la colonne: " . $conn->error;
}

$conn->close();
?>
