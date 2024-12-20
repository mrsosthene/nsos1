class CommentManager {
    constructor() {
        this.form = document.getElementById('commentForm');
        this.commentSection = document.getElementById('commentaires');
        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        const submitButton = this.form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        
        try {
            const formData = new FormData(this.form);
            const response = await this.submitComment(formData);
            
            if (response.success) {
                await this.refreshComments();
                this.form.reset();
                this.scrollToComments();
            } else {
                alert(response.message || 'Erreur lors de l\'ajout du commentaire');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
        } finally {
            submitButton.disabled = false;
        }
    }

    async submitComment(formData) {
        const response = await fetch('ajax/submit_comment.php', {
            method: 'POST',
            body: formData
        });
        return await response.json();
    }

    async refreshComments() {
        const articleId = this.form.querySelector('[name="article_id"]').value;
        const response = await fetch(`ajax/get_comments.php?article_id=${articleId}`);
        const html = await response.text();
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        const newComments = tempDiv.querySelector('.comments-list');
        if (newComments) {
            const currentComments = this.commentSection.querySelector('.comments-list');
            if (currentComments) {
                currentComments.innerHTML = newComments.innerHTML;
            }
        }
    }

    scrollToComments() {
        const offset = 80; // Hauteur du header fixe
        const top = this.commentSection.offsetTop - offset;
        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });
    }
}

// Initialiser le gestionnaire de commentaires
document.addEventListener('DOMContentLoaded', () => {
    new CommentManager();
});
