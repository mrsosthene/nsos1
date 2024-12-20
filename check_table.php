<?php
include('config.php');

$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<h2>Structure de la table users :</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "Colonne: " . $row['Field'] . " - Type: " . $row['Type'] . "<br>";
    }
} else {
    echo "Erreur lors de la vérification de la structure de la table";
}

$conn->close();
?>
