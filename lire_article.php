<?php
session_start();
require_once 'config/connexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: actualites.php');
    exit();
}

$id = $_GET['id'];

// Récupérer l'article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: actualites.php');
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="lire_article.css">
    <script src="https://kit.fontawesome.com/c6b8a9f677.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@next/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/comments.js" defer></script>
    <style>
        :root {
            --primary-color: #808080;
            --white: #ffffff;
            --red: #dc3545;
            --blue: #808080;
            --green: #808080;
        }

        /* Styles pour l'image de fond */
        .background-image {
            background-image: url('<?php echo htmlspecialchars($article['image'] ?: "default-image.jpg"); ?>');
            background-size: cover;
            background-position: center;
            height: 100vh;
            position: relative;
            opacity: 1;
            transition: opacity 1s ease-out;
        }

        /* Superposition de l'overlay */
        .gradient-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.8));
        }

        /* Animation du titre */
        .title-container {
            position: absolute;
            bottom: 10%;
            left: 10%;
            right: 10%;
            text-align: left;
            color: white;
            opacity: 0;
            animation: fadeInTitle 2s forwards 0.5s;
        }

        @keyframes fadeInTitle {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animation du contenu de l'article */
        .article-content {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            animation: fadeInContent 1s forwards 1s;
        }

        /* Classes pour les images */
        .img-fluid { max-width: 100%; height: auto; }
        .img-small { max-width: 300px; height: auto; }
        .img-medium { max-width: 500px; height: auto; }
        .img-large { max-width: 800px; height: auto; }

        /* Préserver les dimensions des images dans le contenu */
        .article-content img {
            max-width: 100%;
            height: auto;
        }

        /* Ne pas forcer la largeur des images qui ont des dimensions spécifiques */
        .article-content img[width] {
            width: auto !important;
            max-width: 100% !important;
        }

        .article-content > div {
            max-width: none !important;
        }

        .article-content p, 
        .article-content div, 
        .article-content span {
            max-width: 100% !important;
        }

        @keyframes fadeInContent {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Effet de défilement fluide */
        .fade-in-on-scroll {
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 1s ease, transform 1s ease;
        }

        .fade-in-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Effet de zoom sur les images */
        .zoom-on-scroll {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 1s ease, transform 1s ease;
        }

        .zoom-on-scroll.visible {
            opacity: 1;
            transform: scale(1);
        }

        .article-text {
            color: black !important;
        }
        .article-text p {
            color: black !important;
        }
        .article-text strong,
        .article-text b,
        .article-text span,
        .article-text div,
        .article-text * {
            color: black !important;
        }
        /* Force tous les éléments de texte en noir */
        .article-content * {
            color: black !important;
        }

        .nike-button-container {
            display: inline-flex;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            background: var(--blue);
        }

        .nike-button-container:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .nike-button {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .button-text {
            font-size: 1rem;
        }

        .modern-button {
            position: relative;
            padding: 12px 24px;
            font-size: 16px;
            color: white;
            background: #333;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .modern-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            transition: transform 0.5s ease;
        }

        .modern-button:hover::before {
            transform: translate(-50%, -50%) scale(3);
        }

        .modern-button span {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .flash-button {
            position: relative;
            padding: 12px 24px;
            font-size: 16px;
            color: white;
            background: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .flash-button.premium {
            padding: 16px 32px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 250px;
        }

        .flash-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: 0.5s;
            z-index: -1;
        }

        .flash-button:hover::before {
            left: 100%;
        }

        .flash-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .flash-button span {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <!-- Barre de Navigation -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="blog1.php" class="text-xl font-bold">Nsos</a>
            <nav class="flex space-x-8">
                <a href="blog1.php" class="text-gray-600 hover:text-gray-900">Accueil</a>
                <a href="actualites.php" class="text-gray-600 hover:text-gray-900">Actualités</a>
                <a href="#" class="text-gray-600 hover:text-gray-900">Catégories</a>
                <a href="" class="text-gray-600 hover:text-gray-900">Contact</a>
                <?php if ($is_logged_in): ?>
                    <a href="profil.php" class="text-gray-600 hover:text-gray-900">Mon profil</a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">Déconnexion</a>
                <?php else: ?>
                    <a href="contact.php" class="text-gray-600 hover:text-gray-900">Contact</a>
                    <a href="inscription.php" class="text-gray-600 hover:text-gray-900">S'inscrire</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Image en arrière-plan avec titre -->
    <div class="background-image">
        <div class="gradient-overlay"></div>
        <div class="title-container">
            <h1 class="text-4xl md:text-6xl font-bold drop-shadow-lg">
                <?php echo htmlspecialchars($article['titre']); ?>
            </h1>
            <p class="text-xl mt-4 text-gray-200">
                Publié le <?php echo $date_formatee; ?>
            </p>
            <p class="text-gray-200">
                Vue<?php echo $total_views > 1 ? 's' : ''; ?> : <?php echo $total_views; ?>
            </p>
        </div>
    </div>

    <!-- Contenu de l'article -->
    <main class="py-8">
        <div class="container mx-auto px-6 md:px-12 lg:px-20">
            <div class="article-content bg-white p-8 w-full">
                <div class="w-full">
                    <h1 class="text-4xl font-bold mb-6">
                        <?php echo htmlspecialchars($article['titre']); ?>
                    </h1>
                    <div class="meta text-gray-600 mb-8">
                        <span>Publié le <?php echo $date_formatee; ?></span>
                        <span class="mx-2">•</span>
                        <span><?php echo $total_views; ?> vues</span>
                    </div>
                    <div class="prose max-w-none">
                        <?php 
                        // Afficher le contenu HTML sans l'échapper
                        echo $article['contenu']; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Section commentaires -->
            <div id="commentaires" class="mt-8 bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-2xl font-bold mb-4">Commentaires</h2>

                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Erreur !</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($is_logged_in): ?>
                    <form id="commentForm" class="mb-8">
                        <input type="hidden" name="article_id" value="<?php echo $id; ?>">
                        <div class="mb-4">
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Votre commentaire</label>
                            <textarea id="comment" name="comment" rows="4" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500"
                                placeholder="Partagez votre avis..."
                            ></textarea>
                        </div>
                        <button type="submit" class="flash-button">
                            <span>
                                Publier le commentaire
                                <ion-icon name="send-outline"></ion-icon>
                            </span>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="bg-gray-50 p-4 rounded-md mb-8">
                        <p class="text-gray-700">
                            <a href="login.php" class="text-blue-600 hover:underline">Connectez-vous</a> 
                            ou 
                            <a href="inscription.php" class="text-blue-600 hover:underline">inscrivez-vous</a> 
                            pour laisser un commentaire.
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Liste des commentaires -->
                <div class="comments-list space-y-4">
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

            <?php if (isset($_GET['error']) || isset($_GET['success']) || strpos($_SERVER['REQUEST_URI'], '#commentaires') !== false): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const commentaires = document.getElementById('commentaires');
                    if (commentaires) {
                        setTimeout(() => {
                            window.scrollTo({
                                top: commentaires.offsetTop - 80,
                                behavior: 'smooth'
                            });
                        }, 100);
                    }
                });
            </script>
            <?php endif; ?>

            <script>
            // Si l'URL contient #commentaires, faire défiler jusqu'aux commentaires
            if (window.location.hash === '#commentaires') {
                const commentaires = document.getElementById('commentaires');
                if (commentaires) {
                    setTimeout(() => {
                        window.scrollTo({
                            top: commentaires.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }, 100);
                }
            }
            </script>

            <script>
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
        </div>
    </main>

    <!-- Section Premium -->
    <div class="contents-payant py-12 bg-gray-50">
        <section class="premium-section container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-8">
                <?php 
                $titre = html_entity_decode($article['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                echo htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
                ?>
            </h2>
            <div class="text-center mb-8">
                <button class="flash-button premium">
                    <span>
                        Débloquer le contenu Premium
                        <ion-icon name="lock-open-outline"></ion-icon>
                    </span>
                </button>
            </div>
            <div class="card-container grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                // Récupérer 3 articles aléatoires
                $stmt = $pdo->prepare("SELECT * FROM articles WHERE id != ? AND statut = 'publié' ORDER BY RAND() LIMIT 3");
                $stmt->execute([$id]);
                $articles_recommandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($articles_recommandes as $article_recommande):
                ?>
                <div class="card bg-white rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="card-image h-48 relative">
                        <img src="<?php echo htmlspecialchars($article_recommande['image']); ?>" 
                             alt="<?php echo htmlspecialchars($article_recommande['titre']); ?>"
                             class="w-full h-full object-cover">
                        <?php if ($article_recommande === reset($articles_recommandes)): ?>
                        
                        <?php endif; ?>
                    </div>
                    <div class="card-content p-6">
                        <p class="category text-gray-600 text-sm font-semibold mb-2">ARTICLE PREMIUM</p>
                        <h3 class="text-xl font-bold mb-2"><?php 
                        $titre = html_entity_decode($article_recommande['titre'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        echo htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'); 
                        ?></h3>
                        <p class="text-gray-600 text-sm">
                            <?php echo substr(strip_tags($article_recommande['contenu']), 0, 100) . '...'; ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

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
</body>
</html>
            document.querySelectorAll('.fade-in-on-scroll, .zoom-on-scroll').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
