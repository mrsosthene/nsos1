<?php
require_once 'config/connexion.php';

// Récupérer l'article le plus récent
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC LIMIT 1");
$article_principal = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les autres articles
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' AND id != " . $article_principal['id'] . " ORDER BY date_publication DESC LIMIT 3");
$articles_secondaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles populaires
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY vues DESC LIMIT 3");
$articles_populaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formater la date
function formaterDate($date) {
    setlocale(LC_TIME, 'fr_FR.UTF8');
    return strftime('%B %d, %Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leadership Féminin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="blog.css">
    <script src="blog.js" defer></script>
</head>
<body class="bg-gray-100 font-sans">
    <?php include 'includes/nav.php'; ?>

    <!-- Main Content -->
    <main class="container mx-40 p-27 flex justify-items-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Main Blog Card -->
            <div class="shadow-lg rounded-lg overflow-hidden hover-scale transition-all duration-300">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($article_principal['image']); ?>" 
                         alt="<?php echo htmlspecialchars($article_principal['titre']); ?>"
                         class="w-full h-100 object-cover transition-transform duration-300" />
                    <div class="absolute top-0 bg-gray-400 text-white px-4 py-2 text-sm font-semibold">
                        Guides
                    </div>
                </div>
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-2 title-text transition-transform duration-300">
                        <?php echo htmlspecialchars($article_principal['titre']); ?>
                    </h2>
                    <p class="text-gray-600 text-sm">
                        <?php echo formaterDate($article_principal['date_publication']); ?>
                    </p>
                </div>
            </div>

            <!-- Side Cards -->
            <div class="space-y-4 mt-32">
                <?php foreach ($articles_secondaires as $article): ?>
                <div class="grid p-4 drop-shadow-lg hover-scale transition-all duration-300">
                    <h3 class="font-semibold text-gray-800 text-md mb-1 line-hover-up transition-transform duration-300">
                        <?php echo htmlspecialchars($article['titre']); ?>
                    </h3>
                    <p class="text-gray-600 text-sm">Conseils</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <section class="first">
        <div class="container">
            <div class="articles-list">
                <?php 
                $stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC LIMIT 4");
                while($article = $stmt->fetch(PDO::FETCH_ASSOC)): 
                ?>
                <div class="article">
                    <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                         alt="<?php echo htmlspecialchars($article['titre']); ?>">
                    <div class="article-content">
                        <div class="article-category">Article</div>
                        <div class="article-title">
                            <?php echo htmlspecialchars($article['titre']); ?>
                        </div>
                        <div class="article-excerpt">
                            <?php echo substr(strip_tags($article['contenu']), 0, 150) . '...'; ?>
                        </div>
                        <div class="article-meta">
                            <img src="images blog/Ndickou.jpeg" alt="Author">
                            <span>Ndickou</span>
                            <span>•</span>
                            <span><?php echo formaterDate($article['date_publication']); ?></span>
                            <a href="lire_article.php?id=<?php echo $article['id']; ?>">
                                <span>Lire l'article</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="sidebar">
                <h2>Nsos</h2>
                <p>
                    Tellus id nisl blandit vitae quam magna nisl aliquet aliquam arcu
                    ultricies commodo felisoler massa ipsum erat non sit amet.
                </p>
                <div class="search-bar">
                    <input type="text" placeholder="Search articles">
                    <button>🔍</button>
                </div>
                <div class="tags">
                    <div class="tag">Conseils</div>
                    <div class="tag">Ressources</div>
                    <div class="tag">Guides</div>
                </div>
                <div class="popular-articles mt-8">
                    <h3>Articles Populaires</h3>
                    <?php foreach ($articles_populaires as $article): ?>
                    <a href="lire_article.php?id=<?php echo $article['id']; ?>">
                        <div class="popular-article">
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['titre']); ?>">
                            <div class="mt-20">
                                <a href="lire_article.php?id=<?php echo $article['id']; ?>">
                                    <?php echo htmlspecialchars($article['titre']); ?>
                                </a>
                                <a href="lire_article.php?id=<?php echo $article['id']; ?>">
                                    <p class="text-gray-600 text-sm">Lire l'article</p>
                                </a>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="contents-payant">
        <section class="premium-section">
            <h1>S'abonner pour débloquer le contenu premium</h1>
            <p>
                Sed at tellus, pharetra lacus, aenean risus non nisl ultricies commodo
                diam aliquet arcu enim eu leo porttitor habitasse adipiscing porttitor
                varius ultricies facilisis viverra lacus neque.
            </p>
            <div class="card-container">
                <?php
                // Récupérer 3 articles pour la section premium
                $stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC LIMIT 3");
                $articles_premium = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($articles_premium as $index => $article):
                    $is_central = $index === 1;
                ?>
                <div class="card <?php echo $is_central ? 'central-card' : ''; ?>">
                    <a href="lire_article.php?id=<?php echo $article['id']; ?>">
                        <div class="card-image" style="background-image: url('<?php echo htmlspecialchars($article['image']); ?>')"></div>
                        <?php if ($is_central): ?>
                            <button class="unlock-button">Débloquer le contenu</button>
                        <?php endif; ?>
                        <div class="card-content">
                            <p class="category">ARTICLE</p>
                            <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="articles">
        <?php 
        $stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC LIMIT 4");
        while($article = $stmt->fetch(PDO::FETCH_ASSOC)): 
        ?>
        <div class="article">
            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                 alt="<?php echo htmlspecialchars($article['titre']); ?>" />
            <div class="article-content">
                <div class="article-category">Article</div>
                <div class="article-title">
                    <?php echo htmlspecialchars($article['titre']); ?>
                </div>
                <div class="article-excerpt">
                    <?php echo substr(strip_tags($article['contenu']), 0, 150) . '...'; ?>
                </div>
                <div class="article-meta">
                    <img src="images blog/Ndickou.jpeg" alt="Author" />
                    <span>Ndickou</span>
                    <span>•</span>
                    <span><?php echo formaterDate($article['date_publication']); ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="popular-articles">
        <h3>Articles Populaires</h3>
        <?php foreach ($articles_populaires as $article): ?>
        <div class="popular-article">
            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                 alt="<?php echo htmlspecialchars($article['titre']); ?>" />
            <div>
                <?php echo htmlspecialchars($article['titre']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h4>Ressources</h4>
                <ul>
                    <li><a href="#">Cartes cadeaux</a></li>
                    <li><a href="#">Trouver un magasin</a></li>
                    <li><a href="#">Journal Nsos</a></li>
                    <li><a href="#">Devenir membre</a></li>
                    <li><a href="#">Réduction pour étudiants</a></li>
                    <li><a href="#">Articles</a></li>
                    <li><a href="#">Conseils</a></li>
                    <li><a href="#">Commentaires</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Aide</h4>
                <ul>
                    <li><a href="#">Aide</a></li>
                    <li><a href="#">Retours</a></li>
                    <li><a href="#">Nous contacter</a></li>
                    <li><a href="#">Avis</a></li>
                    <li><a href="#">Accompagnement</a></li>
                    <li><a href="#">S'abonner</a></li>
                    <li><a href="#">Groupe d'entraide</a></li>
                    <li><a href="#">Réseautage</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Entreprise</h4>
                <ul>
                    <li><a href="#">À propos de Nsos</a></li>
                    <li><a href="#">Actualités</a></li>
                    <li><a href="#">Carrières</a></li>
                    <li><a href="#">Investisseurs</a></li>
                    <li><a href="#">Développement durable</a></li>
                    <li><a href="#">Accessibilité: partiellement conforme</a></li>
                    <li><a href="#">Mission</a></li>
                    <li><a href="#">Signaler un problème</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p> Nsikou</p>
            <ul class="footer-links">
                <li><a href="#">Guides</a></li>
                <li><a href="#">Conditions d'utilisation</a></li>
                <li><a href="#">Conditions générales de vente</a></li>
                <li><a href="#">Informations sur l'entreprise</a></li>
                <li><a href="#">Politique de confidentialité et de gestion des cookies</a></li>
            </ul>
            <p>Paramètres de confidentialité et de cookies</p>
        </div>
    </footer>
</body>
</html>
