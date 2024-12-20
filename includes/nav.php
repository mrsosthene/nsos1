<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
?>

<header class="bg-white shadow-sm">
    <nav class="mx-auto flex max-w-7xl items-center justify-between p-4">
        <div class="flex items-center">
            <a href="index.php" class="text-xl font-bold text-gray-800">Nsos</a>
        </div>
        
        <div class="flex items-center space-x-8">
            <a href="index.php" class="text-gray-600 hover:text-gray-900">Accueil</a>
            <a href="actualites.php" class="text-gray-600 hover:text-gray-900">Actualités</a>
            <a href="categories.php" class="text-gray-600 hover:text-gray-900">Catégories</a>
            <a href="contact.php" class="text-gray-600 hover:text-gray-900">Contact</a>
            <?php if ($is_logged_in): ?>
                <a href="profil.php" class="text-gray-600 hover:text-gray-900">Mon profil</a>
                <a href="logout.php" class="text-gray-600 hover:text-gray-900">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="text-gray-600 hover:text-gray-900">Connexion</a>
                <a href="register.php" class="text-gray-600 hover:text-gray-900">S'inscrire</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<style>
    nav {
        border-bottom: 1px solid #e5e7eb;
    }
    nav a {
        font-size: 0.95rem;
        font-weight: 500;
        transition: color 0.2s ease;
    }
    .flex.items-center.space-x-8 {
        margin-left: auto;
    }
</style>
