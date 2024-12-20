// Animation de la section hero
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que la section est cachée au départ
    const heroSection = document.querySelector('.hero-section');
    
    // Déclencher l'animation après un court délai
    setTimeout(() => {
        heroSection.classList.add('visible');
    }, 500);

    // Animation des articles
    const articles = document.querySelectorAll('.article-card');
    articles.forEach((article, index) => {
        setTimeout(() => {
            if (isElementInView(article)) {
                article.classList.add('visible');
            }
        }, 1000 + (index * 100));
    });
});

// Fonction pour détecter si un élément est visible
function isElementInView(el) {
    const rect = el.getBoundingClientRect();
    return rect.top <= window.innerHeight && rect.bottom >= 0;
}

// Gérer l'apparition des articles au scroll
function checkArticlesInView() {
    const articles = document.querySelectorAll('.article-card:not(.visible)');
    articles.forEach((article, index) => {
        if (isElementInView(article)) {
            setTimeout(() => {
                article.classList.add('visible');
            }, index * 100);
        }
    });
}

// Optimisation du scroll
let ticking = false;
window.addEventListener('scroll', () => {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            checkArticlesInView();
            ticking = false;
        });
        ticking = true;
    }
});
