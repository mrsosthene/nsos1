<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['query'])) {
        $search = '%' . $_GET['query'] . '%';
        
        $stmt = $pdo->prepare("
            SELECT id, titre, image, vues 
            FROM articles 
            WHERE titre LIKE :search 
            OR contenu LIKE :search 
            ORDER BY vues DESC 
            LIMIT 5
        ");
        
        $stmt->execute(['search' => $search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'results' => $results]);
    } else {
        // Si pas de requête, retourner les articles populaires
        $stmt = $pdo->query("
            SELECT id, titre, image, vues 
            FROM articles 
            ORDER BY vues DESC 
            LIMIT 5
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'results' => $results]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Une erreur est survenue']);
}
