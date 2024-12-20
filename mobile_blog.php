<?php
session_start();
require_once 'config.php';
require_once 'utils.php';

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);

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

// Récupérer les articles récents
$stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY date_publication DESC LIMIT 3");
$articles_recents = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leadership Féminin - Mobile</title>
    <script src="https://kit.fontawesome.com/c6b8a9f677.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="blog.css" />
    <link rel="stylesheet" href="mobile_styles.css" />
    <style>
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 85%;
            height: 100%;
            background: white;
            transition: 0.3s ease-in-out;
            z-index: 1000;
            overflow-y: auto;
        }

        .mobile-menu.active {
            left: 0;
        }

        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }

        .menu-overlay.active {
            display: block;
        }

        .menu-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-close {
            font-size: 1.5rem;
            background: none;
            border: none;
            color: #333;
        }

        .menu-content {
            padding: 1rem;
        }

        .menu-section {
            margin-bottom: 2rem;
        }

        .menu-section h3 {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #111;
        }

        .menu-link {
            display: block;
            padding: 0.8rem 0;
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
        }

        .menu-link:hover {
            color: #111;
        }

        .menu-button {
            display: block;
            width: 100%;
            padding: 1rem;
            margin: 0.5rem 0;
            background: #111;
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.2s;
        }

        .menu-button.outline {
            background: white;
            color: #111;
            border: 1px solid #111;
        }

        .menu-button:hover {
            background: #333;
        }

        .menu-button.outline:hover {
            background: #f5f5f5;
        }

        .hero-section {
            position: relative;
            height: 60vh;
            background-size: cover;
            background-position: center;
            color: white;
            transition: transform 0.3s ease-in-out;
        }

        .hero-section:hover {
            transform: scale(1.02);
        }

        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards;
        }

        .fade-in-up-delay {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards;
            animation-delay: 0.3s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .title-hover {
            transition: transform 0.3s ease;
        }

        .title-hover:hover {
            transform: translateX(10px);
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0));
        }

        .article-card {
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-button {
            padding: 8px;
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            color: #4B5563;
        }

        .menu-button:hover {
            color: #1D4ED8;
        }

        .menu-button svg {
            width: 24px;
            height: 24px;
        }
    </style>
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

    <!-- Main Content -->
    <main class="mt-16">
        <!-- Hero Section -->
        <section class="hero-section" style="background-image: url('images blog/first.jpg');">
            <div class="hero-overlay"></div>
        </section>

        <div class="bg-white py-8">
            <div class="container mx-auto px-4">
                <div class="fade-in-up">
                    <h2 class="text-4xl font-black mb-3 leading-tight tracking-tight title-hover">
                        LEAD WITH <br>
                        <span class="text-black">COURAGE</span>
                    </h2>
                    <p class="text-gray-800 text-lg font-medium mb-8 tracking-wide">
                        Inspirez. Innovez. Impactez.
                    </p>
                </div>
                <div class="fade-in-up-delay">
                    <h2 class="text-4xl font-black leading-tight tracking-tight title-hover">
                        EMPOWERED <br>
                        <span class="text-black">LEADERSHIP</span>
                    </h2>
                    <p class="text-gray-800 text-lg font-medium tracking-wide">
                        Votre vision. Notre mission.
                    </p>
                </div>
            </div>
        </div>
        <!-- Articles Vus -->
        <h2 class="text-3xl font-black text-center py-8">ARTICLES LES PLUS VUS</h2>
        <div class="py-4 bg-white">
            <div class="container mx-auto px-4">
                <?php
                $stmt = $pdo->query("SELECT * FROM articles WHERE statut = 'publié' ORDER BY vues DESC LIMIT 3");
                while($article = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="mb-4 bg-white rounded-lg shadow-lg">
                    <div class="flex flex-col md:flex-row p-3">
                        <div class="flex-1 pr-4">
                            <h3 class="text-base font-bold">
                                <?php 
                                $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                echo strip_tags($titre);
                                ?>
                            </h3>
                            <p class="text-gray-600 text-xs my-1 line-clamp-2">
                                <?php 
                                $contenu = html_entity_decode($article['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                echo substr(strip_tags($contenu), 0, 80) . '...';
                                ?>
                            </p>
                            <div class="flex justify-between items-center text-xs">
                                <time class="text-gray-500"><?php echo formaterDate($article['date_publication']); ?></time>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="text-black font-bold">Lire →</a>
                            </div>
                        </div>
                        <div class="relative mt-2 md:mt-0 md:w-1/3">
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['titre']); ?>"
                                 class="w-full h-32 object-cover rounded">
                            <span class="absolute bottom-1 right-1 bg-black text-white px-2 py-0.5 rounded text-xs">
                                <?php echo number_format($article['vues']); ?> vues
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Newsletter Section -->
        <section class="bg-black text-white px-4 py-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold mb-2">Restez Informé</h2>
                <p class="text-gray-300 mb-6">Inscrivez-vous à notre newsletter pour recevoir les dernières actualités</p>
                <form action="newsletter.php" method="POST" class="max-w-md mx-auto">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <input type="email" name="email" placeholder="Votre adresse email" 
                               class="flex-1 px-4 py-2 rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <button type="submit" 
                                class="bg-white text-black px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            S'inscrire
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

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

    <script>
        // Mobile menu functionality
        const menuButton = document.getElementById('menuButton');
        const menuClose = document.getElementById('menuClose');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        function openMenu() {
            mobileMenu.classList.add('active');
            menuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            mobileMenu.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        menuButton.addEventListener('click', openMenu);
        menuClose.addEventListener('click', closeMenu);
        menuOverlay.addEventListener('click', closeMenu);

        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html>
