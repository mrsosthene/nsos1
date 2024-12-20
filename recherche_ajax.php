<?php
header('Content-Type: application/json');

define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_php');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => $e->getMessage()]));
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (!empty($search)) {
    // Recherche prioritaire dans les titres
    $stmt = $pdo->prepare("
        (SELECT id, titre, image, date_publication, 1 as priority 
         FROM articles 
         WHERE titre LIKE :startsWith 
         AND statut = 'publié')
        UNION
        (SELECT id, titre, image, date_publication, 2 as priority 
         FROM articles 
         WHERE titre LIKE :contains 
         AND titre NOT LIKE :startsWith 
         AND statut = 'publié')
        ORDER BY priority ASC, date_publication DESC 
        LIMIT 5
    ");
    
    $startsWithTerm = $search . "%";
    $containsTerm = "%" . $search . "%";
    
    $stmt->bindParam(':startsWith', $startsWithTerm, PDO::PARAM_STR);
    $stmt->bindParam(':contains', $containsTerm, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($results);
