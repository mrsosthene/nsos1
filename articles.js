document.addEventListener("DOMContentLoaded", function() {
    // Fonction qui ajuste la taille des cartes en fonction de leur position
    function adjustCardSize() {
        const container = document.querySelector('.articles-container');
        const cards = document.querySelectorAll('.article-card');
        const containerRect = container.getBoundingClientRect();
        const containerCenterX = containerRect.left + containerRect.width / 2;

        cards.forEach(card => {
            const cardRect = card.getBoundingClientRect();
            const cardCenterX = cardRect.left + cardRect.width / 2;
            const distance = Math.abs(containerCenterX - cardCenterX);

            // Calculer un facteur d'échelle en fonction de la distance au centre
            const scale = Math.max(0.7, 1 - (distance / containerRect.width)); // Ajuste le facteur de réduction
            card.style.transform = `scale(${scale})`;
            card.style.opacity = scale; // Les cartes qui s'éloignent deviennent plus transparentes
        });
    }

    // Applique l'ajustement lors du défilement
    document.querySelector('.articles-container').addEventListener('scroll', adjustCardSize);

    // Appliquer l'ajustement au chargement initial
    adjustCardSize();
});
