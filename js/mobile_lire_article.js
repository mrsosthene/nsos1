document.addEventListener('DOMContentLoaded', function() {
    // Gestion du menu mobile
    const menuButton = document.getElementById('menuButton');
    const menuClose = document.getElementById('menuClose');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.getElementById('menuOverlay');

    function openMenu() {
        mobileMenu.classList.add('active');
        menuOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        mobileMenu.classList.remove('active');
        menuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (menuButton) menuButton.addEventListener('click', openMenu);
    if (menuClose) menuClose.addEventListener('click', closeMenu);
    if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);

    // Swipe detection pour le menu mobile
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        const swipeDistance = touchEndX - touchStartX;
        
        if (Math.abs(swipeDistance) > swipeThreshold) {
            if (swipeDistance > 0 && touchStartX < 50) {
                // Swipe right from left edge
                openMenu();
            } else if (swipeDistance < 0 && mobileMenu.classList.contains('active')) {
                // Swipe left when menu is open
                closeMenu();
            }
        }
    }

    // Gestion du bouton retour
    const backButton = document.querySelector('.back-button');
    if (backButton) {
        backButton.addEventListener('click', function() {
            window.history.back();
        });
    }

    // Gestion du formulaire de commentaire
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const commentText = this.querySelector('textarea').value;
            const articleId = this.querySelector('input[name="article_id"]').value;

            if (!commentText.trim()) {
                alert('Veuillez entrer un commentaire');
                return;
            }

            // Envoi du commentaire via AJAX
            fetch('ajouter_commentaire.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `commentaire=${encodeURIComponent(commentText)}&article_id=${articleId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ajouter le nouveau commentaire à la liste
                    const commentsContainer = document.querySelector('.comments-list');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <div class="comment-header">
                            <span class="comment-author">${data.author}</span>
                            <span class="comment-date">À l'instant</span>
                        </div>
                        <div class="comment-content">
                            ${commentText}
                        </div>
                    `;
                    commentsContainer.insertBefore(newComment, commentsContainer.firstChild);
                    
                    // Réinitialiser le formulaire
                    this.reset();
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'envoi du commentaire');
            });
        });
    }

    // Animation au scroll
    let lastScrollTop = 0;
    const header = document.querySelector('.fixed-header');
    
    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop) {
            // Scroll vers le bas
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scroll vers le haut
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
});
