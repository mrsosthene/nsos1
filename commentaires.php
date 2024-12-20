<?php
session_start();
require_once 'config/connexion.php';

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Traitement de la suppression
if (isset($_POST['supprimer']) && isset($_POST['commentaire_id'])) {
    $commentaire_id = $_POST['commentaire_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
        $stmt->execute([$commentaire_id]);
        
        // Message de succès
        $success = "Le commentaire a été supprimé avec succès.";
    } catch(PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupération de tous les commentaires avec les informations de l'article associé
$stmt = $pdo->query("
    SELECT c.*, a.titre as article_titre 
    FROM commentaires c 
    LEFT JOIN articles a ON c.article_id = a.id 
    ORDER BY c.date_creation DESC
");
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des commentaires - Admin</title>
    <link rel="stylesheet" href="css/style1.css">
    <style>
        .comment-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .comment-meta {
            color: #666;
            font-size: 0.9em;
        }

        .comment-content {
            margin: 15px 0;
            line-height: 1.6;
        }

        .delete-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-button:hover {
            background: #c82333;
        }

        .success-message {
            background: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-message {
            background: #dc3545;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .article-link {
            color: #007bff;
            text-decoration: none;
        }

        .article-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="index1.php">
                        <span class="icon">
                            <ion-icon name="book-outline"></ion-icon>
                        </span>
                        <span class="title">Nsos</span>
                    </a>
                </li>
                <li>
                    <a href="admin.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="articles.php">
                        <span class="icon">
                            <ion-icon name="newspaper-outline"></ion-icon>
                        </span>
                        <span class="title">Articles</span>
                    </a>
                </li>
                <li>
                    <a href="commentaires.php">
                        <span class="icon">
                            <ion-icon name="chatbubbles-outline"></ion-icon>
                        </span>
                        <span class="title">Commentaires</span>
                    </a>
                </li>
                <li>
                    <a href="personnes_inscrit.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Utilisateurs</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" placeholder="Rechercher ici">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>

                <div class="user">
                    <img src="images/Profil Admin.png" alt="">
                </div>
            </div>

            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Gestion des Commentaires</h2>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="success-message"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php foreach ($commentaires as $commentaire): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <div class="comment-meta">
                                    <strong><?php echo htmlspecialchars($commentaire['nom']); ?></strong>
                                    <span class="date">
                                        - <?php echo date('d/m/Y H:i', strtotime($commentaire['date_creation'])); ?>
                                    </span>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="commentaire_id" value="<?php echo $commentaire['id']; ?>">
                                    <button type="submit" name="supprimer" class="delete-button" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">
                                        <ion-icon name="trash-outline"></ion-icon>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                            <div>
                                Article : 
                                <a href="lire_article.php?id=<?php echo $commentaire['article_id']; ?>" class="article-link">
                                    <?php echo htmlspecialchars($commentaire['article_titre']); ?>
                                </a>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($commentaire['contenu'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($commentaires)): ?>
                        <p>Aucun commentaire n'a été trouvé.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="js/mainadmin.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
