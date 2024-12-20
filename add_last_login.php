<?php
require 'config.php';

try {
    // Lire le contenu du fichier SQL
    $sql = file_get_contents('add_last_login.sql');
    
    // Exécuter la requête
    if ($conn->query($sql) === TRUE) {
        echo "Colonne last_login ajoutée avec succès!";
    } else {
        echo "Erreur lors de l'ajout de la colonne: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
