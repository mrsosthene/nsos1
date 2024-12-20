<?php
// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_php');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (!empty($search)) {
    // Recherche dans le titre et le contenu des articles
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE (titre LIKE :search OR contenu LIKE :search) AND statut = 'publié' ORDER BY date_publication DESC");
    $searchTerm = "%{$search}%";
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - <?php echo htmlspecialchars($search); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="blog.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="blog.php" class="text-xl font-bold">Nsos</a>
            <nav class="space-x-4">
                <a href="blog.php" class="text-gray-600 hover:text-blue-500">Accueil</a>
                <a href="actualites.php" class="text-gray-600 hover:text-blue-500">Actualités</a>
                <a href="#" class="text-gray-600 hover:text-blue-500">Contact</a>
                <a href="inscription.php" class="text-gray-600 hover:text-blue-500">S'inscrire</a>
                <a href="login.php" class="text-gray-600 hover:text-blue-500">Se connecter</a>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-4">Résultats de recherche pour "<?php echo htmlspecialchars($search); ?>"</h1>
            <form action="recherche.php" method="GET" class="mb-6">
                <div class="search-bar w-full max-w-2xl">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher un article..." />
                    <button type="submit">
                        <i class="fas fa-magnifying-glass text-xl hover:text-gray-300 transition-colors"></i>
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($search)): ?>
            <p class="text-gray-600">Veuillez saisir un terme de recherche.</p>
        <?php elseif (empty($results)): ?>
            <p class="text-gray-600">Aucun résultat trouvé pour "<?php echo htmlspecialchars($search); ?>".</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($results as $article): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <?php if (!empty($article['image'])): ?>
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-4">
                            <h2 class="text-xl font-bold mb-2">
                                <a href="lire_article.php?id=<?php echo $article['id']; ?>" class="text-gray-900 hover:text-blue-600">
                                    <?php echo htmlspecialchars($article['titre']); ?>
                                </a>
                            </h2>
                            <p class="text-gray-600 mb-4">
                                <?php 
                                $excerpt = strip_tags($article['contenu']);
                                echo strlen($excerpt) > 150 ? substr($excerpt, 0, 150) . '...' : $excerpt;
                                ?>
                            </p>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span><?php echo date('d/m/Y', strtotime($article['date_publication'])); ?></span>
                                <a href="lire_article.php?id=<?php echo $article['id']; ?>" class="text-blue-500 hover:text-blue-700">Lire la suite →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Nsos. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>
