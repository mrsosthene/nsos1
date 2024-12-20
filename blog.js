// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', () => {
    // Afficher le body
    document.body.style.opacity = '1';

    // Gestion de la recherche
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    // Fonction pour mettre en surbrillance les termes recherchés
    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    // Fonction pour formater la date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    }

    // Fonction pour afficher les résultats
    function displayResults(results, query) {
        searchResults.innerHTML = '';
        
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-no-results">Aucun résultat trouvé</div>';
            searchResults.style.display = 'block';
            return;
        }

        results.forEach(result => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            
            // Mise en surbrillance du titre
            const highlightedTitle = highlightText(result.titre, query);
            
            resultItem.innerHTML = `
                <img src="${result.image || 'images blog/default.jpg'}" alt="${result.titre}" class="search-result-image">
                <div class="search-result-info">
                    <div class="search-result-title">${highlightedTitle}</div>
                    <div class="search-result-date">${formatDate(result.date_publication)}</div>
                </div>
            `;
            
            resultItem.addEventListener('click', () => {
                window.location.href = `lire_article.php?id=${result.id}`;
            });
            
            searchResults.appendChild(resultItem);
        });
        
        searchResults.style.display = 'block';
    }

    // Gestionnaire d'événement pour la saisie dans la barre de recherche
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Recherche dès la première lettre
        searchTimeout = setTimeout(() => {
            fetch(`recherche_ajax.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(results => displayResults(results, query))
                .catch(error => console.error('Erreur:', error));
        }, 150); // Réduit le délai pour une réponse plus rapide
    });

    // Navigation au clavier dans les résultats
    let selectedIndex = -1;
    
    searchInput.addEventListener('keydown', (e) => {
        const results = document.querySelectorAll('.search-result-item');
        
        if (results.length === 0) return;
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % results.length;
            updateSelection(results);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = selectedIndex <= 0 ? results.length - 1 : selectedIndex - 1;
            updateSelection(results);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            results[selectedIndex].click();
        }
    });

    function updateSelection(results) {
        results.forEach((result, index) => {
            if (index === selectedIndex) {
                result.classList.add('selected');
                result.scrollIntoView({ block: 'nearest' });
            } else {
                result.classList.remove('selected');
            }
        });
    }

    // Fermer les résultats quand on clique en dehors
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
            selectedIndex = -1;
        }
    });

    // Gérer la barre de navigation
    const header = document.querySelector('header');
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        
        // Afficher la barre de navigation quand on scrolle vers le bas
        if (currentScrollY > 50) {
            header.classList.add('visible');
        } else {
            header.classList.remove('visible');
        }
        
        lastScrollY = currentScrollY;
    });

    // Éléments à animer
    const elementsToAnimate = [
        '.article',
        '.sidebar',
        '.popular-article',
        '.premium-section',
        '.card'
    ];

    // Initialiser les éléments
    elementsToAnimate.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            element.classList.add('fade-in');
            if (['.article', '.card', '.popular-article'].includes(selector)) {
                element.classList.add('slide-from-right');
            }
        });
    });

    // Observer les éléments pour l'animation au scroll
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    if (entry.target.classList.contains('slide-from-right')) {
                        entry.target.classList.add('in-position');
                    }
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: '50px'
        }
    );

    // Observer tous les éléments avec la classe fade-in
    document.querySelectorAll('.fade-in').forEach(element => {
        observer.observe(element);
    });

    // Gestion du menu déroulant
    const dropdownButton = document.querySelector('.dropdown-button');
    const dropdownContent = document.querySelector('.dropdown-content');

    if (dropdownButton && dropdownContent) {
        dropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownContent.classList.toggle('show');
            
            // Animation de l'icône
            const icon = this.querySelector('.menu-icon');
            if (icon) {
                icon.style.transform = dropdownContent.classList.contains('show') 
                    ? 'rotate(180deg)' 
                    : 'rotate(0)';
            }
        });

        // Fermer le menu au clic en dehors
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdownContent.contains(e.target)) {
                dropdownContent.classList.remove('show');
                const icon = dropdownButton.querySelector('.menu-icon');
                if (icon) {
                    icon.style.transform = 'rotate(0)';
                }
            }
        });
    }
});
