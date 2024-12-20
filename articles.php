<?php
// Informations de connexion à la base de données

define('DB_HOST', 'localhost');

define('DB_NAME', 'projet_php');

define('DB_USER', 'root');

define('DB_PASS', '');



try {

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Erreur de connexion à la base de données: " . $e->getMessage());

}



// Variable pour stocker l'ID de l'article publié

$article_publie_id = null;

// Fonction pour nettoyer le HTML et limiter le texte
function cleanAndLimitText($html, $limit = 150) {
    // Supprime les balises HTML
    $text = strip_tags($html);
    // Limite la longueur du texte
    if (strlen($text) > $limit) {
        $text = substr($text, 0, $limit) . '...';
    }
    return $text;
}

// Vérification de la suppression d'un article

if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $id_article = $_GET['supprimer'];
    
    try {
        // Démarrer une transaction
        $pdo->beginTransaction();
        
        // D'abord supprimer les vues associées
        $stmt = $pdo->prepare("DELETE FROM article_views WHERE article_id = ?");
        $stmt->execute([$id_article]);
        
        // Ensuite supprimer l'article
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id_article]);
        
        // Valider la transaction
        $pdo->commit();
        
        // Rediriger vers la même page
        header('Location: articles.php');
        exit();
    } catch(PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $pdo->rollBack();
        die("Erreur lors de la suppression de l'article : " . $e->getMessage());
    }
}



// Vérification de la publication d'un article

if (isset($_GET['publier']) && is_numeric($_GET['publier'])) {
    $id_article = $_GET['publier'];

    try {
        // Mettre à jour le statut de l'article en 'publie'
        $stmt = $pdo->prepare("UPDATE articles SET statut = 'publie', date_publication = NOW() WHERE id = ?");
        $stmt->execute([$id_article]);

        // Rediriger vers la même page avec un message de succès
        header('Location: articles.php?success=1');
        exit();
    } catch(PDOException $e) {
        die("Erreur lors de la publication de l'article : " . $e->getMessage());
    }
}



// Récupération des articles

$stmt = $pdo->prepare("SELECT * FROM articles ORDER BY id DESC");

$stmt->execute();

$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Pas besoin d'inverser l'ordre car on trie déjà par ID DESC



?>

<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des articles</title>
    <link rel="stylesheet" href="articles.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="header">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                L'article a été publié avec succès !
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        <a href="add_article.php" class="nike-button-container primary">
            <span class="nike-button">
                <span class="button-text">Ajouter un article</span>
                <ion-icon name="add-outline"></ion-icon>
            </span>
        </a>
    </div>

    <div class="articles-container">
        <?php foreach ($articles as $article): ?>
            <div class="article-card">
                <?php
                    // Vérifier l'image de couverture
                    $hasImage = false;
                    if (!empty($article['image'])) {
                        if (file_exists($article['image'])) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" alt="' . htmlspecialchars($article['titre']) . '" class="article-image">';
                            $hasImage = true;
                        }
                    }

                    // Si pas d'image de couverture, chercher dans le contenu
                    if (!$hasImage) {
                        if (preg_match('/<img[^>]+src="([^">]+)"/', $article['contenu'], $matches)) {
                            echo '<img src="' . htmlspecialchars($matches[1]) . '" alt="' . htmlspecialchars($article['titre']) . '" class="article-image">';
                            $hasImage = true;
                        }
                    }

                    // Si aucune image n'est trouvée, afficher le placeholder
                    if (!$hasImage) {
                        echo '<div class="no-image"><ion-icon name="image-outline"></ion-icon></div>';
                    }
                ?>
                
                <div class="article-content">
                    <div class="article-text">
                        <h2><?php 
                            $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            echo strip_tags($titre);
                        ?></h2>
                        <p class="article-date"><?php echo date('d/m/Y', strtotime($article['date_publication'])); ?></p>
                        <p class="article-description"><?php 
                            $contenu = html_entity_decode($article['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            echo cleanAndLimitText($contenu, 100);
                        ?></p>
                    </div>
                    <div class="article-buttons">
                        <a href="modifier_article.php?id=<?php echo $article['id']; ?>" class="nike-button-container info">
                            <span class="nike-button">
                                <span class="button-text">Modifier</span>
                                <ion-icon name="create-outline"></ion-icon>
                            </span>
                        </a>
                        <?php if ($article['statut'] === 'brouillon'): ?>
                        <a href="publier_article.php?id=<?php echo $article['id']; ?>" class="nike-button-container success">
                            <span class="nike-button">
                                <span class="button-text">Publier</span>
                                <ion-icon name="cloud-upload-outline"></ion-icon>
                            </span>
                        </a>
                        <?php endif; ?>
                        <a href="?supprimer=<?php echo $article['id']; ?>" 
                           class="nike-button-container danger"
                           onclick="return confirm('Voulez-vous vraiment supprimer cet article ?')">
                            <span class="nike-button">
                                <span class="button-text">Supprimer</span>
                                <ion-icon name="trash-outline"></ion-icon>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="view-articles-container">
        <a href="actualites.php" class="nike-button-container">
            <span class="nike-button">
                <span class="button-text">Voir tous les articles</span>
                <ion-icon name="arrow-forward-outline"></ion-icon>
            </span>
        </a>
    </div>

    <div class="footer-buttons">
        <div class="button-group">
            <a href="actualites.php" class="nike-button-container primary">
                <span class="nike-button">
                    <span class="button-text">Blogs</span>
                    <ion-icon name="newspaper-outline"></ion-icon>
                </span>
            </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.articles-container');
        const cards = document.querySelectorAll('.article-card');
        
        function updateCardsScale() {
            const containerRect = container.getBoundingClientRect();
            const containerCenter = containerRect.left + containerRect.width / 2;
            
            cards.forEach((card, index) => {
                const cardRect = card.getBoundingClientRect();
                const cardCenter = cardRect.left + cardRect.width / 2;
                const distance = Math.abs(containerCenter - cardCenter);
                const maxDistance = containerRect.width / 2;
                
                // Augmentation de l'effet d'échelle
                const scale = Math.max(0.7, 1 - (distance / maxDistance) * 0.5);
                const opacity = Math.max(0.5, 1 - (distance / maxDistance) * 0.5);
                const translateY = Math.abs(distance / maxDistance) * 80;
                
                card.style.transform = `scale(${scale}) translateY(${translateY}px)`;
                card.style.opacity = opacity;
                
                if (distance < 150) { // Zone active plus large
                    card.classList.add('active');
                    if (index > 0) cards[index-1].classList.add('adjacent');
                    if (index < cards.length - 1) cards[index+1].classList.add('adjacent');
                } else {
                    card.classList.remove('active');
                    if (index > 0) cards[index-1].classList.remove('adjacent');
                    if (index < cards.length - 1) cards[index+1].classList.remove('adjacent');
                }
            });
        }

        container.addEventListener('scroll', updateCardsScale);
        updateCardsScale();
        
        // Center first card on load
        if (cards.length > 0) {
            cards[0].scrollIntoView({
                behavior: 'auto',
                block: 'nearest',
                inline: 'center'
            });
        }
    });
    </script>
</body>
</html>
