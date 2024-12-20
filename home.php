<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fonction de détection mobile
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

// Déterminer les liens en fonction du device
$is_mobile = isMobile();
$blog_link = $is_mobile ? 'mobile_blog.php' : 'blog1.php';
$actualites_link = $is_mobile ? 'mobile_actualites.php' : 'actualites.php';
$contact_link = $is_mobile ? 'mobile_contact.php' : 'contact.php';

// Afficher les informations de l'utilisateur
$prenom = $_SESSION['prenom'];
$nom = $_SESSION['nom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nsos - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('imagesBlog/bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
        }
        .flash-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .flash-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            transition: transform 0.5s ease-out;
        }
        .flash-button:hover::before {
            transform: translate(-50%, -50%) scale(2);
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .slide-up {
            animation: slideUp 1s ease-out forwards;
        }
        .slide-up-delay-1 {
            animation-delay: 0.2s;
        }
        .slide-up-delay-2 {
            animation-delay: 0.4s;
        }

        /* Styles du footer responsive */
        @media (max-width: 768px) {
            .footer-content > div {
                text-align: center;
            }
            
            .footer-links {
                align-items: center;
            }
            
            .social-icons {
                justify-content: center;
            }
            
            .footer-nav, .footer-social {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body class="bg-black text-white">
    <!-- Navigation -->
    <header class="fixed w-full z-50 bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="<?php echo $blog_link; ?>" class="text-2xl font-bold text-gray-900">Nsos</a>
            <nav class="flex space-x-8">
                <a href="<?php echo $blog_link; ?>" class="text-gray-600 hover:text-gray-900 transition-colors">Accueil</a>
                <a href="<?php echo $actualites_link; ?>" class="text-gray-600 hover:text-gray-900 transition-colors">Blog</a>
                <a href="logout.php" class="text-gray-600 hover:text-gray-900 transition-colors">Déconnexion</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-7xl font-bold mb-6 opacity-0 slide-up">
                BIENVENUE, <?php echo strtoupper($prenom); ?>
            </h1>
            <p class="text-xl mb-12 opacity-0 slide-up slide-up-delay-1">
                Découvrez notre collection d'articles inspirants et enrichissants
            </p>
            <div class="space-x-6 opacity-0 slide-up slide-up-delay-2">
                <a href="<?php echo $actualites_link; ?>" class="flash-button bg-white text-black px-8 py-4 rounded-full text-lg font-bold inline-block">
                    Explorer les Articles
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white text-gray-800 py-16">
        <div class="container mx-auto px-4">
            <div class="footer-content grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <!-- Logo et description -->
                <div class="footer-brand">
                    <h3 class="text-xl font-bold mb-4">Nsos</h3>
                    <p class="text-gray-600 mb-6">Votre source d'inspiration et d'information.</p>
                </div>

                <!-- Navigation -->
                <div class="footer-nav">
                    <h4 class="font-semibold mb-4">Navigation</h4>
                    <div class="footer-links flex flex-col space-y-2">
                        <a href="<?php echo $blog_link; ?>" class="text-gray-600 hover:text-gray-900">Accueil</a>
                        <a href="<?php echo $actualites_link; ?>" class="text-gray-600 hover:text-gray-900">Blog</a>
                        <a href="contact.php" class="text-gray-600 hover:text-gray-900">Contact</a>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <div class="footer-social">
                    <h4 class="font-semibold mb-4">Suivez-nous</h4>
                    <div class="social-icons flex space-x-4">
                        <a href="#" class="text-gray-600 hover:text-gray-900 text-2xl">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-gray-900 text-2xl">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-gray-900 text-2xl">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-gray-900 text-2xl">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>

                <!-- Section entreprise (masquée sur mobile) -->
                <div class="footer-entreprise hidden lg:block">
                    <h4 class="font-semibold mb-4">Entreprise</h4>
                    <div class="footer-links flex flex-col space-y-2">
                        <a href="#" class="text-gray-600 hover:text-gray-900">À propos</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Carrières</a>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Mentions légales</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 mt-12 pt-8 border-t border-gray-200 text-center text-gray-600">
            <p> 2024 Nsos. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
