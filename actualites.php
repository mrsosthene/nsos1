<?php
// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_php');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Variable pour stocker l'ID de l'article publié
$article_publie_id = null;

// Vérification de la publication d'un article
if (isset($_GET['publier']) && is_numeric($_GET['publier'])) {
    $id_article = $_GET['publier'];

    // Mettre à jour le statut de l'article en 'publie'
    $stmt = $pdo->prepare("UPDATE articles SET statut = 'publie' WHERE id = ?");
    $stmt->execute([$id_article]);

    // Enregistrer l'ID de l'article publié pour afficher le dialogue
    $article_publie_id = $id_article;
}

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);

// Récupération des articles publiés, triés par nombre de vues
$stmt = $pdo->prepare("SELECT * FROM articles WHERE statut = 'publie' ORDER BY vues DESC");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifiez si la variable contient des données
if (empty($articles)) {
    // Optionnel : journalisez ou affichez un message pour le débogage
    error_log("Aucun article trouvé dans la base de données.");
}

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

function formaterDate($date) {
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
    return strftime("%d %B %Y", strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualités - Nsos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="blog.css">
    <style>
        .article-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.6s ease-out;
            opacity: 0;
            transform: translateX(100px);
        }
        .article-card.visible {
            opacity: 1;
            transform: translateX(0);
        }
        .article-card:hover {
            transform: translateY(-5px);
        }
        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        .nav-arrow {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .nav-arrow:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        .nav-arrow.left {
            left: 20px;
        }
        .nav-arrow.right {
            right: 20px;
        }
        .article-meta {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }
        .article-meta .fa-eye {
            color: #4a5568;
            margin-right: 0.5rem;
        }
        .views-count {
            background: #f3f4f6;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .read-more {
            display: inline-block;
            padding: 8px 20px;
            background: #f3f4f6;
            border-radius: 20px;
            transition: background 0.3s ease;
        }
        .read-more:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header avec la nouvelle navigation -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="blog1.php" class="text-xl font-bold">Nsos</a>
            <nav class="flex space-x-8">
                <a href="blog1.php" class="text-gray-600 hover:text-gray-900">Accueil</a>
                <a href="actualites.php" class="text-gray-600 hover:text-gray-900">Blog</a>
                <a href="#" class="text-gray-600 hover:text-gray-900">Catégories</a>
                <a href="contact.php" class="text-gray-600 hover:text-gray-900">Contact</a>
                <?php if ($is_logged_in): ?>
                    <a href="profil.php" class="text-gray-600 hover:text-gray-900">Mon profil</a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">Déconnexion</a>
                <?php else: ?>
                    <a href="inscription.php" class="text-gray-600 hover:text-gray-900">Rejoignez-nous</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Navigation arrows -->
        <div class="nav-arrow left">❮</div>
        <div class="nav-arrow right">❯</div>

        <!-- Articles -->
        <?php foreach($articles as $article): ?>
            <article class="article-card mb-8">
                <div class="relative">
                    <a href="lire_article.php?id=<?php echo $article['id']; ?>">
                        <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                             alt="<?php echo stripslashes($article['titre']); ?>"
                             class="article-image">
                        <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black to-transparent">
                            <h2 class="text-3xl font-bold text-white mb-2">
                                <?php 
                                $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                echo strip_tags($titre);
                                ?>
                            </h2>
                            <p class="text-white mb-4">
                                Publié le <?php echo formaterDate($article['date_publication']); ?>
                            </p>
                        </div>
                    </a>
                </div>
                <div class="p-6">
                    <div class="article-content mb-4">
                        <?php 
                        $contenu = html_entity_decode($article['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $excerpt = substr(strip_tags($contenu), 0, 200) . '...';
                        echo $excerpt;
                        ?>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="article-meta">
                            <span class="views-count">
                                <i class="fas fa-eye"></i> <?php echo number_format($article['vues']); ?> vues
                            </span>
                        </div>
                        <a href="lire_article.php?id=<?php echo $article['id']; ?>" class="read-more">
                            Lire la suite →
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </main>

    <footer class="footer">
      <div class="footer-container">
        <!-- Colonne Ressources -->
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

        <!-- Colonne Aide -->
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

        <!-- Colonne Entreprise -->
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

      <!-- Bas de page -->
      <div class="footer-bottom">
        <p> 2024 Nike, Inc. Tous droits réservés</p>
        <ul class="footer-links">
          <li><a href="#">Guides</a></li>
          <li><a href="#">Conditions d'utilisation</a></li>
          <li><a href="#">Conditions générales de vente</a></li>
          <li><a href="#">Informations sur l'entreprise</a></li>
          <li>
            <a href="#">Politique de confidentialité et de gestion des cookies</a>
          </li>
        </ul>
      </div>
    </footer>

    <script>
        // Animation au scroll
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observer tous les articles
        document.querySelectorAll('.article-card').forEach(card => {
            observer.observe(card);
        });

        // Fonction pour faire défiler d'une hauteur d'écran
        function scrollPage(direction) {
            const windowHeight = window.innerHeight;
            const currentScroll = window.scrollY;
            const targetScroll = direction === 'up' ? currentScroll - windowHeight : currentScroll + windowHeight;
            
            window.scrollTo({
                top: targetScroll,
                behavior: 'smooth'
            });
        }

        // Navigation par flèches du clavier
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                scrollPage('up');
            }
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                scrollPage('down');
            }
        });

        // Click handlers pour les flèches
        document.querySelector('.nav-arrow.left').addEventListener('click', () => {
            scrollPage('up');
        });

        document.querySelector('.nav-arrow.right').addEventListener('click', () => {
            scrollPage('down');
        });

        // Masquer/afficher les flèches en fonction de la position de défilement
        window.addEventListener('scroll', () => {
            const leftArrow = document.querySelector('.nav-arrow.left');
            const rightArrow = document.querySelector('.nav-arrow.right');
            
            // Masquer la flèche gauche au sommet de la page
            if (window.scrollY <= 0) {
                leftArrow.style.opacity = '0';
                leftArrow.style.pointerEvents = 'none';
            } else {
                leftArrow.style.opacity = '1';
                leftArrow.style.pointerEvents = 'auto';
            }
            
            // Masquer la flèche droite en bas de la page
            if ((window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight) {
                rightArrow.style.opacity = '0';
                rightArrow.style.pointerEvents = 'none';
            } else {
                rightArrow.style.opacity = '1';
                rightArrow.style.pointerEvents = 'auto';
            }
        });
    </script>
</body>
</html>