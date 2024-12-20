<?php
session_start();
require_once 'config/connexion.php';

// Fonction de détection mobile
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

// Déterminer le lien du blog en fonction du device
$blog_link = isMobile() ? 'mobile_actualites.php' : 'actualites.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: mobile_actualites.php');
    exit();
}

$id = $_GET['id'];

// Récupérer l'article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: mobile_actualites.php');
    exit();
}

// Formater la date
$date_formatee = date('d/m/Y', strtotime($article['date_publication']));

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);

// Gérer la vue pour tous les visiteurs
try {
    if ($is_logged_in) {
        // Pour les utilisateurs connectés, on vérifie s'ils ont déjà vu l'article
        $stmt = $pdo->prepare("SELECT id FROM article_views WHERE article_id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            // Ajouter une vue personnalisée
            $stmt = $pdo->prepare("INSERT INTO article_views (article_id, user_id) VALUES (?, ?)");
            $stmt->execute([$id, $_SESSION['user_id']]);
            
            // Incrémenter le compteur de vues
            $stmt = $pdo->prepare("UPDATE articles SET vues = vues + 1 WHERE id = ?");
            $stmt->execute([$id]);
        }
    } else {
        // Pour les visiteurs non connectés, on incrémente simplement le compteur
        $stmt = $pdo->prepare("UPDATE articles SET vues = vues + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
} catch(PDOException $e) {
    error_log("Erreur lors de l'enregistrement de la vue : " . $e->getMessage());
}

// Récupérer le nombre total de vues uniques
$stmt = $pdo->prepare("SELECT COUNT(*) as total_views FROM article_views WHERE article_id = ?");
$stmt->execute([$id]);
$views_data = $stmt->fetch();
$total_views = $views_data['total_views'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
    ?></title>
    <script src="https://kit.fontawesome.com/c6b8a9f677.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/mobile_lire_article.css">
    <style>
        :root {
            --primary-color: #808080;
            --white: #ffffff;
            --red: #dc3545;
            --blue: #808080;
            --green: #808080;
        }

        /* Style pour l'image de fond */
        .article-header-image {
            position: relative;
            width: 100%;
            height: auto;
            margin-bottom: 2rem;
            z-index: 1;
        }

        .article-header-image img {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
        }

        /* Container principal de l'article */
        .article-main-content {
            position: relative;
            background: white;
            margin-top: -2rem;
            padding: 2rem 1.5rem;
            border-radius: 20px 20px 0 0;
            z-index: 2;
        }

        /* Styles pour le contenu de l'article */
        .article-content {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Adaptation des images dans le contenu */
        .article-content img {
            width: 100% !important;
            height: auto !important;
            max-width: 100% !important;
            margin: 1.5rem 0 2.5rem 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Adaptation du texte */
        .article-content p,
        .article-content div,
        .article-content span {
            width: 100% !important;
            max-width: 100% !important;
            font-size: 16px;
            line-height: 1.6;
            margin: 1.5rem 0 2rem 0;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        @media screen and (min-width: 480px) and (max-width: 720px) {
            .article-header-image img {
                max-height: 400px;
            }

            .article-main-content {
                margin-top: -3rem;
                padding: 2.5rem 2rem;
            }

            .article-content img {
                margin: 2rem 0 3rem 0;
            }

            .article-content p,
            .article-content div,
            .article-content span {
                font-size: 17px;
                margin: 1.5rem 0 2.5rem 0;
            }
        }

        /* Styles du footer */
        footer {
            background-color: #1a1a1a;
            color: #fff;
            padding: 2rem 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            display: grid;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-brand h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #fff;
        }

        .footer-brand p {
            color: #999;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #fff;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .footer-links a {
            color: #999;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }

        .footer-links a:hover {
            color: #fff;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-icons a {
            color: #999;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #fff;
        }

        .footer-bottom {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #333;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        @media screen and (min-width: 480px) and (max-width: 720px) {
            footer {
                padding: 3rem 2rem;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 3rem;
            }

            .footer-brand {
                grid-column: 1 / -1;
            }
        }

        /* Style du bouton de commentaire */
        .flash-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            width: 100%;
        }

        .flash-button:hover {
            background-color: #333;
            transform: translateY(-1px);
        }

        .flash-button:active {
            transform: translateY(0);
        }

        .flash-button span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .flash-button ion-icon {
            font-size: 1.25rem;
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
            <a href="mobile_blog.php" class="text-xl font-bold">Nsos</a>
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
            <a href="mobile_blog.php" class="text-xl font-bold">Nsos</a>
            <button class="menu-close" id="menuClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="menu-content">
            <div class="menu-section">
                <h3>Menu Principal</h3>
                <a href="mobile_blog.php" class="menu-link">Accueil</a>
                <a href="<?php echo $blog_link; ?>" class="menu-link">Blog</a>
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

    <!-- Article Content -->
    <div class="pt-16">
        <?php if ($article['image']): ?>
            <div class="article-header-image">
                <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>">
            </div>
        <?php endif; ?>

        <div class="article-main-content">
            <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($article['titre']); ?></h1>
            
            <div class="flex items-center text-gray-600 text-sm mb-4">
                <span class="mr-4">
                    <i class="far fa-calendar-alt mr-1"></i>
                    <?php echo $date_formatee; ?>
                </span>
                <span>
                    <i class="far fa-eye mr-1"></i>
                    <?php echo $article['vues']; ?> vues
                </span>
            </div>

            <div class="article-content prose max-w-none">
                <?php echo $article['contenu']; ?>
            </div>
        </div>
    </div>

    <!-- Section commentaires -->
    <div id="commentaires" class="mt-8 bg-white p-6">
        <h2 class="text-2xl font-bold mb-4">Commentaires</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if ($is_logged_in): ?>
            <form action="ajouter_commentaire.php" method="POST" class="mb-8">
                <input type="hidden" name="article_id" value="<?php echo $id; ?>">
                <input type="hidden" name="is_mobile" value="1">
                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Votre commentaire</label>
                    <textarea id="comment" name="comment" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                        placeholder="Partagez votre avis..."
                    ></textarea>
                </div>
                <button type="submit" name="submit_comment" class="flash-button">
                    <span>
                        Publier le commentaire
                        <ion-icon name="send-outline"></ion-icon>
                    </span>
                </button>
            </form>
        <?php else: ?>
            <div class="bg-gray-50 p-4 rounded-md mb-8">
                <p class="text-gray-700 text-center">
                    <a href="login.php" class="text-blue-600 hover:underline">Connectez-vous</a> 
                    ou 
                    <a href="inscription.php" class="text-blue-600 hover:underline">inscrivez-vous</a> 
                    pour laisser un commentaire.
                </p>
            </div>
        <?php endif; ?>

        <!-- Liste des commentaires -->
        <div class="space-y-4">
            <?php
            // Récupérer les commentaires avec les informations des utilisateurs
            $stmt = $pdo->prepare("
                SELECT c.*, u.prenom, u.nom 
                FROM commentaires c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.article_id = ? 
                ORDER BY c.date_creation DESC
            ");
            $stmt->execute([$id]);
            $commentaires = $stmt->fetchAll();

            if ($commentaires): 
                foreach ($commentaires as $commentaire): 
            ?>
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
            <?php 
                endforeach;
            else: 
            ?>
                <p class="text-gray-500 text-center py-4">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <h2>Nsos</h2>
                <p>Votre source d'information sur l'actualité du sport et plus encore. Restez connecté avec nous pour ne rien manquer.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Navigation</h3>
                <div class="footer-links">
                    <a href="blog1.php">Accueil</a>
                    <a href="<?php echo $blog_link; ?>" class="text-gray-300 hover:text-white transition-colors">Blog</a>
                    <a href="contact.php">Contact</a>
                    <?php if ($is_logged_in): ?>
                        <a href="profil.php">Profil</a>
                        <a href="logout.php">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php">Connexion</a>
                        <a href="register.php">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-section">
                <h3>Catégories</h3>
                <div class="footer-links">
                    <a href="#">Football</a>
                    <a href="#">Basketball</a>
                    <a href="#">Tennis</a>
                    <a href="#">Autres sports</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Nsos. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Animation au défilement
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });

            document.querySelectorAll('.fade-in-on-scroll, .zoom-on-scroll').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>

    <script>
    // Si l'URL contient #commentaires, faire défiler jusqu'aux commentaires
    if (window.location.hash === '#commentaires') {
        const commentaires = document.getElementById('commentaires');
        if (commentaires) {
            setTimeout(() => {
                window.scrollTo({
                    top: commentaires.offsetTop - 60, // Soustraire la hauteur du header fixe
                    behavior: 'smooth'
                });
            }, 100);
        }
    }
    </script>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
