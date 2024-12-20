document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner les éléments
    const header = document.querySelector('header');
    const contentBlocks = document.querySelectorAll('.article-content');

    // Position initiale pour les blocs de contenu
    contentBlocks.forEach(block => {
        block.style.transform = 'translateX(100%)';
        block.style.opacity = '0';
        block.style.transition = 'all 0.8s ease-out';
    });

    // Fonction pour gérer l'apparition de la barre de navigation
    function handleNavigation() {
        if (window.scrollY > 100) {
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            header.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        } else {
            header.style.backgroundColor = 'transparent';
            header.style.boxShadow = 'none';
        }
    }

    // Fonction pour gérer l'animation des blocs de contenu
    function handleContentAnimation() {
        contentBlocks.forEach(block => {
            const blockTop = block.getBoundingClientRect().top;
            const blockBottom = block.getBoundingClientRect().bottom;
            const triggerPoint = window.innerHeight * 0.8;

            if (blockTop < triggerPoint && blockBottom > 0) {
                block.style.transform = 'translateX(0)';
                block.style.opacity = '1';
            }
        });
    }

    // Écouter l'événement de défilement
    window.addEventListener('scroll', () => {
        handleNavigation();
        handleContentAnimation();
    });

    // Déclencher une première fois pour l'état initial
    handleNavigation();
    handleContentAnimation();
});
