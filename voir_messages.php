<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=projet_php", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Pagination
$messagesParPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $messagesParPage;

// Récupérer le nombre total de messages
$stmt = $pdo->query("SELECT COUNT(*) FROM contacts");
$totalMessages = $stmt->fetchColumn();
$totalPages = ceil($totalMessages / $messagesParPage);

// Récupérer les messages pour la page actuelle
$stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY date_envoi DESC LIMIT :debut, :messagesParPage");
$stmt->bindValue(':debut', $debut, PDO::PARAM_INT);
$stmt->bindValue(':messagesParPage', $messagesParPage, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Supprimer un message
if (isset($_POST['delete_message'])) {
    $message_id = (int)$_POST['message_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$message_id]);
        header("Location: voir_messages.php");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression du message.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Administration</title>
    <link rel="stylesheet" href="css/style1.css">
    <style>
        .message-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .pagination a {
            padding: 8px 16px;
            background: #287bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .pagination a:hover {
            background: #1a5dc7;
        }

        .pagination .active {
            background: #1a5dc7;
        }

        .status.delete {
            background: #ff4444;
        }

        .status.delete:hover {
            background: #cc0000;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .message-content {
            margin-top: 20px;
            line-height: 1.6;
        }

        .delete-form {
            display: inline;
        }

        .delete-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
                    <a href="add_article.php">
                        <span class="icon">
                            <ion-icon name="add-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Ajouter un article</span>
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

        <!-- Main -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="user">
                    <img src="images/Profil Admin.png" alt="">
                </div>
            </div>

            <!-- Messages List -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Messages</h2>
                        <span>Total: <?php echo $totalMessages; ?> messages</span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>Nom</td>
                                <td>Email</td>
                                <td>Sujet</td>
                                <td>Date</td>
                                <td>Actions</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($message['nom']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td><?php echo htmlspecialchars($message['sujet']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?></td>
                                <td>
                                    <button class="status delivered" onclick="showMessage('<?php echo htmlspecialchars(addslashes($message['message'])); ?>')">
                                        Voir
                                    </button>
                                    <form class="delete-form" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" name="delete_message" class="status delete">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" <?php echo $page == $i ? 'class="active"' : ''; ?>>
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Modal pour afficher le message complet -->
            <div id="messageModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Message</h2>
                    <div id="messageContent" class="message-content"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="js/mainadmin.js"></script>

    <script>
        // Fonction pour afficher le modal avec le message
        function showMessage(message) {
            document.getElementById('messageContent').textContent = message;
            document.getElementById('messageModal').style.display = 'block';
        }

        // Fermer le modal quand on clique sur le X
        document.querySelector('.close').onclick = function() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Fermer le modal quand on clique en dehors
        window.onclick = function(event) {
            var modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
