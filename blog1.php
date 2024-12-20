<?php
require_once 'config.php';
require_once 'utils.php';

// Fonction de détection mobile
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

// Déterminer le lien du blog en fonction du device
$blog_link = isMobile() ? 'mobile_actualites.php' : 'actualites.php';

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);

// Vérifier si c'est un appareil mobile
$isMobile = isMobile();

if ($isMobile) {
    include 'mobile_blog.php';
    exit();
} else {
    include 'pc_blog.php';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Leadership Féminin</title>
    <script src="https://kit.fontawesome.com/c6b8a9f677.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="blog.css" />
    <link rel="stylesheet" href="blog_menu.css" />
    <link rel="stylesheet" href="mobile.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="blog.js" defer></script>
  </head>
  <body class="bg-gray-100 font-sans">
    <!-- Header -->
    <header>
      <div class="container">
        <a href="#" class="text-2xl">Nsos</a>
        
        <!-- Menu déroulant pour mobile et tablette -->
        <div class="dropdown">
          <button class="dropdown-button" aria-label="Menu" aria-expanded="false">
            Menu
            <svg class="menu-icon" viewBox="0 0 24 24">
              <path d="M7 10l5 5 5-5z"/>
            </svg>
          </button>
          <div class="dropdown-content">
            <a href="<?php echo $isMobile ? 'mobile_blog.php' : 'index.php'; ?>">Accueil</a>
            <a href="<?php echo $blog_link; ?>">Blog</a>
            <a href="<?php echo $isMobile ? 'mobile_contact.php' : 'contact.php'; ?>">Contact</a>
            <a href="<?php echo $isMobile ? 'mobile_inscription.php' : 'inscription.php'; ?>">Rejoignez-nous</a>
            <a href="<?php echo $isMobile ? 'mobile_login.php' : 'login.php'; ?>">S'identifier</a>
          </div>
        </div>

        <!-- Navigation normale -->
        <nav class="space-x-4">
          <a href="<?php echo $isMobile ? 'mobile_blog.php' : 'index.php'; ?>">Accueil</a>
          <a href="<?php echo $blog_link; ?>">Blog</a>
          <a href="<?php echo $isMobile ? 'mobile_contact.php' : 'contact.php'; ?>">Contact</a>
          <a href="<?php echo $isMobile ? 'mobile_inscription.php' : 'inscription.php'; ?>">Rejoignez-nous</a>
          <a href="<?php echo $isMobile ? 'mobile_login.php' : 'login.php'; ?>">S'identifier</a>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <!-- Main Content -->

    <main class="container mx-auto p-4 flex justify-items-center">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Main Blog Card -->
        <div
          class="shadow-lg rounded-lg overflow-hidden hover-scale transition-all duration-300"
        >
          <div class="relative">
            <img
              src="images blog/first.jpg"
              alt="Leadership féminin"
              class="w-full h-100 object-cover transition-transform duration-300"
            />
            <div
              class="absolute  top-0 bg-gray-400 text-white px-4 py-2 text-sm font-semibold"
            >
              Guides
            </div>
          </div>
          <div class="p-6">
            <h2
              class="text-lg font-bold mb-2 title-text transition-transform duration-300"
            >
              Les femmes qui changent le monde : Portraits de leaders
              inspirantes
            </h2>
            <p class="text-gray-600 text-sm">June 16, 2022</p>
          </div>
        </div>


        <!-- Side Cards -->
        <div class="featured-articles">
          <div class="article-card" data-aos="fade-up" data-aos-delay="100">
            <span class="article-category">Conseils</span>
            <h3>Le mentorat féminin : Une clé pour un leadership fort et durable</h3>
            <div class="article-overlay"></div>
          </div>

          <div class="article-card" data-aos="fade-up" data-aos-delay="200">
            <span class="article-category">Conseils</span>
            <h3>L'impact des femmes leaders dans les entreprises africaines</h3>
            <div class="article-overlay"></div>
          </div>

          <div class="article-card" data-aos="fade-up" data-aos-delay="300">
            <span class="article-category">Ressources</span>
            <h3>Les mythes autour du leadership féminin : Décryptage et vérité</h3>
            <div class="article-overlay"></div>
          </div>
        </div>
      </div>
    </main>
    <section class="first">
      <div class="articles-container">
        <div class="articles">
          <?php foreach ($articles_recents as $article): ?>
            <article class="article-content">
              <div class="article-image-container">
                <img src="<?php echo $article['image']; ?>" alt="<?php echo $article['titre']; ?>" class="article-image">
              </div>
              <div class="article-text">
                <h2><?php echo $article['titre']; ?></h2>
                <p><?php echo isset($article['contenu']) ? substr(strip_tags($article['contenu']), 0, 200) . '...' : ''; ?></p>
                <div class="article-meta">
                  <span class="author">Par <?php echo isset($article['auteur']) ? $article['auteur'] : 'Anonyme'; ?></span>
                  <span class="date"><?php echo formaterDate($article['date_publication']); ?></span>
                </div>
                <div class="btn-container">
                  <a href="#" class="btn" data-aos="fade-up" data-aos-duration="800">
                    <span class="btn-text">Lire la suite</span>
                    <span class="btn-flash"></span>
                  </a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
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
          <!-- Première Carte -->
          <div class="card">
            <div
              class="card-image"
              style="background-image: url('Bg.jpg')"
            ></div>
            <div class="card-content">
              <p class="category">CONSEILS</p>
              <h3>A comprehensive guide on Agile development</h3>
            </div>
          </div>

          <!-- Deuxième Carte -->
          <div class="card central-card">
            <div
              class="card-image"
              style="background-image: url('imagesBlog/leader.jpeg')"
            ></div>
            <button class="unlock-button">Débloquer le contenu</button>
            <div class="card-content">
              <p class="category">CONSEILS</p>
              <h3>10 Productivity tools that are worth checking out</h3>
            </div>
          </div>

          <!-- Troisième Carte -->
          <div class="card">
            <div
              class="card-image"
              style="background-image: url('imagesBlog/lead.jpeg')"
            ></div>
            <div class="card-content">
              <p class="category">RESOURCES</p>
              <h3>Top 7 Must have management tools for productivity</h3>
            </div>
          </div>
        </div>
      </section>
    </div>
    <footer>
        <div class="container">
            <div class="footer-content">
                <!-- Logo et description -->
                <div class="footer-brand">
                    <h2>Nsos</h2>
                    <p>
                        Votre plateforme dédiée au leadership féminin et à l'entrepreneuriat en Afrique.
                    </p>
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

                <!-- Navigation du footer -->
                <nav class="footer-nav">
                    <div class="footer-links">
                        <a href="<?php echo $isMobile ? 'mobile_blog.php' : 'index.php'; ?>">Accueil</a>
                        <a href="<?php echo $blog_link; ?>">Blog</a>
                        <a href="<?php echo $isMobile ? 'mobile_contact.php' : 'contact.php'; ?>">Contact</a>
                        <a href="<?php echo $isMobile ? 'mobile_inscription.php' : 'inscription.php'; ?>">Rejoignez-nous</a>
                        <a href="<?php echo $isMobile ? 'mobile_login.php' : 'login.php'; ?>">S'identifier</a>
                    </div>
                </nav>

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
                <p> &copy; <?php echo date('Y'); ?> Nsos. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
  </body>
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    // Initialisation AOS
    AOS.init({
      duration: 800,
      offset: 100,
      once: true,
      easing: 'ease-out-cubic'
    });

    // Menu mobile
    document.addEventListener('DOMContentLoaded', function() {
      const dropdownButton = document.querySelector('.dropdown-button');
      const dropdown = document.querySelector('.dropdown');

      dropdownButton.addEventListener('click', function(e) {
        e.preventDefault();
        dropdown.classList.toggle('active');
        
        // Mise à jour de l'attribut aria-expanded
        const isExpanded = dropdown.classList.contains('active');
        dropdownButton.setAttribute('aria-expanded', isExpanded);
      });

      // Fermer le menu si on clique en dehors
      document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
          dropdown.classList.remove('active');
          dropdownButton.setAttribute('aria-expanded', 'false');
        }
      });

      // Fermer le menu après avoir cliqué sur un lien
      const dropdownLinks = document.querySelectorAll('.dropdown-content a');
      dropdownLinks.forEach(link => {
        link.addEventListener('click', function() {
          dropdown.classList.remove('active');
          dropdownButton.setAttribute('aria-expanded', 'false');
        });
      });
    });
  </script>
</html>
