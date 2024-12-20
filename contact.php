<?php
session_start();
require_once 'config/connexion.php';

// Fonction de détection mobile
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

// Déterminer le lien du blog en fonction du device
$is_mobile = isMobile();
$blog_link = $is_mobile ? 'mobile_blog.php' : 'blog1.php';
$actualites_link = $is_mobile ? 'mobile_actualites.php' : 'actualites.php';

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $sujet = $_POST['sujet'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validation simple
    if (!empty($nom) && !empty($email) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (nom, email, sujet, message, date_envoi) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$nom, $email, $sujet, $message]);
            $success = "Votre message a été envoyé avec succès !";
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de l'envoi du message.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Nsos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('contact.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        /* Animations au scroll */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .slide-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s ease-out;
        }

        .slide-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .slide-right {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s ease-out;
        }

        .slide-right.visible {
            opacity: 1;
            transform: translateX(0);
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

        .contact-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
        }

        .contact-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Animation du formulaire */
        .form-group {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-out;
        }

        .form-group.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Styles du footer responsive */
        footer {
            padding: 4rem 2rem;
        }

        .footer-content {
            display: grid;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Version mobile (par défaut) */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-brand, .footer-social, .footer-nav, .footer-entreprise {
                padding: 1rem;
            }

            .social-icons {
                justify-content: center;
            }

            .footer-links {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }

            .footer-entreprise {
                display: none;
            }
            
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

        /* Version tablette */
        @media (min-width: 769px) and (max-width: 1024px) {
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Version desktop */
        @media (min-width: 1025px) {
            .footer-content {
                grid-template-columns: 2fr 1fr 1fr 1fr;
            }
        }

        .footer-brand h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .footer-brand p {
            color: #666;
            line-height: 1.6;
        }

        .footer-social h3, .footer-nav h3, .footer-entreprise h3 {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background: #333;
            color: white;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #333;
        }

        .footer-bottom {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="<?php echo $blog_link; ?>" class="text-xl font-bold">Nsos</a>
            <nav class="flex space-x-8">
                <a href="<?php echo $blog_link; ?>" class="text-gray-600 hover:text-gray-900">Accueil</a>
                <a href="<?php echo $actualites_link; ?>" class="text-gray-600 hover:text-gray-900">Blog</a>
                <?php if ($is_logged_in): ?>
                    <a href="profil.php" class="text-gray-600 hover:text-gray-900">Profil</a>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-gray-900">Connexion</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="contact-hero text-white py-32 px-4">
        <div class="container mx-auto text-center">
            <h1 class="text-6xl font-bold mb-6 fade-up">CONTACTEZ-NOUS</h1>
            <p class="text-xl mb-8 fade-up">Nous sommes là pour vous aider et répondre à toutes vos questions</p>
            <a href="#contact-form" class="flash-button bg-white text-black px-8 py-4 rounded-full text-lg font-bold inline-block">
                Commencer
            </a>
        </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="py-16 px-4">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="contact-card bg-white p-8 rounded-lg text-center slide-left">
                <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-map-marker-alt text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Notre Adresse</h3>
                <p class="text-gray-600">123 Rue du Commerce<br>75001 Paris, France</p>
            </div>
            <div class="contact-card bg-white p-8 rounded-lg text-center">
                <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-phone text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Téléphone</h3>
                <p class="text-gray-600">+33 1 23 45 67 89<br>Lun-Ven: 9h-18h</p>
            </div>
            <div class="contact-card bg-white p-8 rounded-lg text-center slide-right">
                <div class="w-16 h-16 bg-black text-white rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-envelope text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Email</h3>
                <p class="text-gray-600">contact@nsos.com<br>support@nsos.com</p>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section id="contact-form" class="py-16 px-4 bg-white">
        <div class="container mx-auto max-w-4xl">
            <h2 class="text-4xl font-bold text-center mb-12 fade-up">Envoyez-nous un message</h2>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                        <input type="text" id="nom" name="nom" required
                            class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-black">
                    </div>
                    <div class="form-group">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" id="email" name="email" required
                            class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-black">
                    </div>
                </div>
                <div class="form-group">
                    <label for="sujet" class="block text-sm font-medium text-gray-700 mb-2">Sujet</label>
                    <input type="text" id="sujet" name="sujet"
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-black">
                </div>
                <div class="form-group">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                    <textarea id="message" name="message" rows="6" required
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-black"></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="flash-button bg-black text-white px-12 py-4 rounded-full text-lg font-bold inline-block">
                        Envoyer le message
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white">
        <div class="container">
            <div class="footer-content">
                <!-- Logo et description -->
                <div class="footer-brand">
                    <h3 class="text-xl font-bold mb-4">Nsos</h3>
                    <p class="text-gray-600 mb-6">Votre source d'inspiration et d'information.</p>
                </div>

                <!-- Navigation -->
                <div class="footer-nav">
                    <h4 class="font-semibold mb-4">Navigation</h4>
                    <div class="footer-links">
                        <a href="<?php echo $blog_link; ?>">Accueil</a>
                        <a href="<?php echo $actualites_link; ?>">Blog</a>
                        <a href="contact.php">Contact</a>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <div class="footer-social">
                    <h3>Suivez-nous</h3>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Section entreprise (masquée sur mobile) -->
                <div class="footer-entreprise">
                    <h3>Entreprise</h3>
                    <div class="footer-links">
                        <a href="about.php">À propos</a>
                        <a href="careers.php">Carrières</a>
                        <a href="privacy.php">Confidentialité</a>
                        <a href="terms.php">Conditions</a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="footer-bottom">
                <p> 2023 Nsos. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        // Animation au scroll
        function handleScroll() {
            const elements = document.querySelectorAll('.fade-up, .slide-left, .slide-right, .contact-card, .form-group');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementBottom = element.getBoundingClientRect().bottom;
                
                if (elementTop < window.innerHeight - 100 && elementBottom > 0) {
                    element.classList.add('visible');
                }
            });
        }

        // Animation au chargement
        window.addEventListener('load', () => {
            // Ajouter les classes d'animation aux éléments
            document.querySelector('.contact-hero h1').classList.add('fade-up');
            document.querySelector('.contact-hero p').classList.add('fade-up');
            
            const cards = document.querySelectorAll('.contact-card');
            cards.forEach((card, index) => {
                card.style.transitionDelay = `${index * 0.2}s`;
            });

            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                group.style.transitionDelay = `${index * 0.1}s`;
            });

            // Déclencher les animations
            setTimeout(handleScroll, 100);
        });

        // Animation au scroll
        window.addEventListener('scroll', handleScroll);
    </script>
</body>
</html>
