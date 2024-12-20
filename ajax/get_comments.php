<?php
session_start();
require_once '../config/connexion.php';

$article_id = filter_input(INPUT_GET, 'article_id', FILTER_VALIDATE_INT);

if (!$article_id) {
    exit('Article non trouvé');
}

// Récupérer les commentaires
$stmt = $pdo->prepare("
    SELECT c.*, u.prenom, u.nom 
    FROM commentaires c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.article_id = ? 
    ORDER BY c.date_creation DESC
");
$stmt->execute([$article_id]);
$commentaires = $stmt->fetchAll();
?>

<div class="comments-list space-y-4">
    <?php if ($commentaires): ?>
        <?php foreach ($commentaires as $commentaire): ?>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-start mb-2">
                    <div class="font-medium text-gray-900">
                        <?php echo htmlspecialchars($commentaire['prenom'] . ' ' . $commentaire['nom']); ?>
                    </div>
                    <div class="text-sm text-gray-500">
                        <?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?>
                    </div>
                </div>
                <div class="text-gray-700">
                    <?php echo nl2br(htmlspecialchars($commentaire['contenu'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-gray-500 text-center py-4">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
    <?php endif; ?>
</div>
