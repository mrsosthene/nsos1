<?php
require_once 'config.php';
require_once 'utils.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=projet_php", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupérer l'article le plus récent
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC LIMIT 1");
$article_principal = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les autres articles
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' AND id != " . $article_principal['id'] . " ORDER BY date_publication DESC LIMIT 3");
$articles_secondaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles populaires
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY vues DESC LIMIT 3");
$articles_populaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les articles publiés
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC");
$tous_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualités - Nsos</title>
    <script src="https://kit.fontawesome.com/c6b8a9f677.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/mobile_actualites.css">
</head>
<body class="bg-gray-100">
    <!-- Mobile Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
        <div class="flex justify-between items-center px-4 py-3">
            <div class="w-10">
                <button class="menu-button" id="menuButton" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
            <a href="#" class="text-xl font-bold">Nsos</a>
            <div class="w-10">
                <button class="menu-button" aria-label="Blog">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="mobile-menu" id="mobileMenu">
        <div class="menu-header">
            <a href="#" class="text-xl font-bold">Nsos</a>
            <button class="menu-close" id="menuClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="menu-content">
            <div class="menu-section">
                <h3>Menu Principal</h3>
                <a href="mobile_blog.php" class="menu-link">Accueil</a>
                <a href="mobile_actualites.php" class="menu-link">Blog</a>
                <a href="contact.php" class="menu-link">Contact</a>
            </div>
            <div class="menu-section">
                <h3>Votre Compte</h3>
                <?php if ($is_logged_in): ?>
                    <a href="profil.php" class="menu-button">Profil</a>
                    <a href="logout.php" class="menu-button outline">Sortir</a>
                <?php else: ?>
                    <a href="inscription.php" class="menu-button">Rejoignez-nous</a>
                    <a href="login.php" class="menu-button outline">S'identifier</a>
                <?php endif; ?>
            </div>
            <div class="menu-section">
                <h3>Ressources</h3>
                <a href="#" class="menu-link">Articles Premium</a>
                <a href="#" class="menu-link">Guides</a>
                <a href="#" class="menu-link">Conseils</a>
            </div>
        </div>
    </div>

    <main class="mt-16">
        <!-- Article Principal -->
        <section class="relative">
            <a href="mobile_lire_article.php?id=<?php echo $article_principal['id']; ?>" class="block">
                <div class="relative h-[60vh]">
                    <img src="<?php echo htmlspecialchars($article_principal['image']); ?>" 
                         alt="<?php echo htmlspecialchars($article_principal['titre']); ?>"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black opacity-70"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h1 class="text-2xl font-bold text-white mb-2">
                            <?php 
                            $titre = html_entity_decode($article_principal['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            echo strip_tags($titre);
                            ?>
                        </h1>
                        <p class="text-white text-sm mb-4">
                            <?php 
                            $contenu = html_entity_decode($article_principal['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            echo substr(strip_tags($contenu), 0, 150) . '...';
                            ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-white text-sm">
                                <?php echo formaterDate($article_principal['date_publication']); ?>
                            </span>
                            <span class="bg-white text-black px-3 py-1 rounded-full text-sm">
                                <?php echo number_format($article_principal['vues']); ?> vues
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </section>

        <!-- Articles Populaires -->
        <section class="py-8 bg-white">
            <h2 class="text-2xl font-black text-center mb-6">ARTICLES LES PLUS VUS</h2>
            <div class="px-4">
                <?php foreach($articles_populaires as $article): ?>
                    <div class="mb-6 bg-gray-50 rounded-lg shadow-sm overflow-hidden">
                        <a href="mobile_lire_article.php?id=<?php echo $article['id']; ?>" class="block">
                            <div class="relative h-48">
                                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['titre']); ?>"
                                     class="w-full h-full object-cover">
                                <span class="absolute bottom-2 right-2 bg-black text-white px-2 py-1 rounded text-xs">
                                    <?php echo number_format($article['vues']); ?> vues
                                </span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-2">
                                    <?php 
                                    $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    echo strip_tags($titre);
                                    ?>
                                </h3>
                                <p class="text-gray-600 text-sm mb-2">
                                    <?php 
                                    $contenu = html_entity_decode($article['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    echo substr(strip_tags($contenu), 0, 100) . '...';
                                    ?>
                                </p>
                                <div class="flex justify-between items-center text-sm">
                                    <time class="text-gray-500">
                                        <?php echo formaterDate($article['date_publication']); ?>
                                    </time>
                                    <span class="text-black font-bold">Lire →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Tous les Articles -->
        <section class="py-8 bg-gray-100">
            <h2 class="text-2xl font-black text-center mb-6">TOUS LES ARTICLES</h2>
            <div class="px-4">
                <?php 
                $total_articles = count($tous_articles);
                foreach($tous_articles as $index => $article): 
                    $hidden = $index >= 5 ? 'hidden' : '';
                ?>
                    <div class="article-item mb-6 bg-white rounded-lg shadow-sm overflow-hidden <?php echo $hidden; ?>" 
                         data-index="<?php echo $index; ?>">
                        <a href="mobile_lire_article.php?id=<?php echo $article['id']; ?>" class="block">
                            <div class="relative h-48">
                                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['titre']); ?>"
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-4">
                                    <h3 class="text-white font-bold text-lg mb-2">
                                        <?php 
                                        $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                        echo strip_tags($titre);
                                        ?>
                                    </h3>
                                    <div class="flex items-center justify-between text-white/80 text-sm">
                                        <time><?php echo formaterDate($article['date_publication']); ?></time>
                                        <span class="bg-white/20 px-2 py-1 rounded">
                                            <?php echo number_format($article['vues']); ?> vues
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4">
                                <p class="text-gray-600 text-sm mb-4">
                                    <?php 
                                    $contenu = html_entity_decode($article['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    echo substr(strip_tags($contenu), 0, 150) . '...';
                                    ?>
                                </p>
                                <div class="flex justify-end">
                                    <span class="text-black font-bold text-sm">Lire l'article →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($total_articles > 5): ?>
                    <div class="text-center mt-8 flex flex-col gap-4">
                        <button id="loadMoreBtn" class="bg-black text-white px-6 py-3 rounded-full font-bold hover:bg-gray-800 transition-all duration-300 flex items-center mx-auto">
                            <span>Voir plus d'articles</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <button id="showLessBtn" class="hidden bg-gray-200 text-gray-800 px-6 py-3 rounded-full font-bold hover:bg-gray-300 transition-all duration-300 flex items-center mx-auto">
                            <span>Voir moins</span>
                            <svg class="w-5 h-5 ml-2 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const loadMoreBtn = document.getElementById('loadMoreBtn');
                const showLessBtn = document.getElementById('showLessBtn');
                const articles = document.querySelectorAll('.article-item');
                let currentlyShown = 5;
                const increment = 5;
                const initialDisplay = 5;

                function updateButtonsVisibility() {
                    if (currentlyShown > initialDisplay) {
                        showLessBtn.classList.remove('hidden');
                    } else {
                        showLessBtn.classList.add('hidden');
                    }

                    if (currentlyShown >= articles.length) {
                        loadMoreBtn.classList.add('hidden');
                    } else {
                        loadMoreBtn.classList.remove('hidden');
                    }
                }

                if (loadMoreBtn) {
                    loadMoreBtn.addEventListener('click', function() {
                        let shown = 0;
                        for (let i = currentlyShown; i < articles.length && shown < increment; i++) {
                            articles[i].classList.remove('hidden');
                            shown++;
                        }
                        currentlyShown += shown;
                        updateButtonsVisibility();

                        // Animation de défilement en douceur vers le dernier article affiché
                        const lastShownArticle = articles[currentlyShown - 1];
                        if (lastShownArticle) {
                            lastShownArticle.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }

                if (showLessBtn) {
                    showLessBtn.addEventListener('click', function() {
                        // Cacher tous les articles après les 5 premiers
                        for (let i = initialDisplay; i < articles.length; i++) {
                            articles[i].classList.add('hidden');
                        }
                        currentlyShown = initialDisplay;
                        updateButtonsVisibility();

                        // Remonter vers le haut de la section
                        articles[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                }

                // Initialisation de l'état des boutons
                updateButtonsVisibility();
            });
        </script>

        <!-- Mobile Footer -->
        <footer class="bg-gray-100 mt-8 py-6 px-4">
            <div class="text-center mb-6">
                <h3 class="font-bold text-gray-800 mb-2">Suivez-nous</h3>
                <div class="flex justify-center space-x-4">
                    <a href="#" class="text-gray-600"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-600"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-600"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-600"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="text-center text-sm text-gray-600">
                <p> 2024 Nsos, Inc. Tous droits réservés</p>
                <div class="mt-2 space-y-1">
                    <a href="#" class="block">Conditions d'utilisation</a>
                    <a href="#" class="block">Politique de confidentialité</a>
                    <a href="#" class="block">Nous contacter</a>
                </div>
            </div>
        </footer>

        <script src="js/mobile_actualites.js"></script>
    </body>
</html>
