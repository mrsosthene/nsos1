<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}
// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_php');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupérer le nombre total d'utilisateurs
$stmt_count_inscrit = $pdo->query("SELECT COUNT(id) as total FROM users ");
$total_inscrit = $stmt_count_inscrit->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer le nombre total de femmes
$stmt_count_inscrit = $pdo->query("SELECT COUNT(id) as total FROM users WHERE sexe = 'femme'");
$total_femmes = $stmt_count_inscrit->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer le nombre total d'hommes
$stmt_count_inscrit = $pdo->query("SELECT COUNT(id) as total FROM users WHERE sexe = 'homme'");
$total_hommes = $stmt_count_inscrit->fetch(PDO::FETCH_ASSOC)['total'];

// Compter les utilisateurs connectés (basé sur la dernière activité)
try {
    $stmt_count_connected = $pdo->query("SELECT COUNT(id) as total FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $total_connected = $stmt_count_connected->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_connected = 0; // Valeur par défaut si erreur
}

// Compter les commentaires
try {
    $stmt_count_comments = $pdo->query("SELECT COUNT(id) as total FROM commentaires");
    $total_comments = $stmt_count_comments->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_comments = 0; // Valeur par défaut si la table n'existe pas
}

// Récupérer le nombre total de messages de contact
try {
    $stmt_count_messages = $pdo->query("SELECT COUNT(id) as total FROM contacts");
    $total_messages = $stmt_count_messages->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $total_messages = 0;
}

// Récupérer les messages de contact récents
try {
    $stmt_messages = $pdo->query("SELECT * FROM contacts ORDER BY date_envoi DESC LIMIT 5");
    $recent_messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_messages = [];
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");

// Gestion de la suppression d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = (int)$_POST['id'];

        try {
            // Début de la transaction
            $pdo->beginTransaction();

            // Supprimer d'abord les vues d'articles
            $stmt = $pdo->prepare("DELETE FROM article_views WHERE user_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer les commentaires de l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM commentaires WHERE user_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer les likes de l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Enfin, supprimer l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Valider la transaction
            $pdo->commit();

            // Rediriger pour éviter la re-soumission du formulaire
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            // En cas d'erreur, annuler toutes les modifications
            $pdo->rollBack();
            echo "<script>alert('Erreur lors de la suppression : " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('ID utilisateur invalide.');</script>";
    }
}

// Vérifier si le bouton "Nommer Admin" a été cliqué
if (isset($_POST['make_admin'])) {
    $user_id = $_POST['user_id'];

    // Mettre à jour le rôle de l'utilisateur dans la base de données
    $stmt_admin = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = :id");
    $stmt_admin->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt_admin->execute()) {
        echo "<script>alert('Utilisateur nommé administrateur avec succès');</script>";
        echo "<script>window.location.href = window.location.href;</script>"; // Rafraîchir la page
        exit;
    } else {
        echo "<script>alert('Erreur lors de la nomination');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="css/style1.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" placeholder="Recherchez ici">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>

                <div class="user">
                    <img src="images\Profil Admin.png" alt="">
                </div>
            </div>

            <!-- ======================= Cards ================== -->
            <div class="cardBox">
                <a href="personnes_inscrit.php" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <div>
                            <div class="numbers">
                                <?php echo "$total_inscrit"; ?>
                            </div>
                            <div class="cardName">Inscrits</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                    </div>
                </a>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $total_connected; ?></div>
                        <div class="cardName">Connectés</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="eye-outline"></ion-icon>
                    </div>
                </div>

                <a href="commentaires.php" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <div>
                            <div class="numbers"><?php echo $total_comments; ?></div>
                            <div class="cardName">Commentaires</div>
                        </div>

                        <div class="iconBx">
                            <ion-icon name="chatbubbles-outline"></ion-icon>
                        </div>
                    </div>
                </a>

                <a href="voir_messages.php" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <div>
                            <div class="numbers"><?php echo $total_messages; ?></div>
                            <div class="cardName">Messages</div>
                        </div>

                        <div class="iconBx">
                            <ion-icon name="mail-outline"></ion-icon>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ================ Messages Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Messages Récents</h2>
                        <a href="voir_messages.php" class="btn">Voir Tout</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <td>Nom</td>
                                <td>Email</td>
                                <td>Sujet</td>
                                <td>Date</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_messages as $message): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($message['nom']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td><?php echo htmlspecialchars($message['sujet']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($message['date_envoi'])); ?></td>
                                <td>
                                    <button class="status delivered" onclick="showMessage('<?php echo htmlspecialchars(addslashes($message['message'])); ?>')">
                                        Voir
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal pour afficher le message complet -->
            <div id="messageModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
                <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 8px;">
                    <span class="close" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                    <h2 style="margin-bottom: 15px;">Message</h2>
                    <p id="messageContent" style="line-height: 1.6;"></p>
                </div>
            </div>

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

            <!-- ================ Add Charts JS ================= -->
            <div class="chartsBx">
                <script>
                const totalInscrits = <?php echo $total_inscrit; ?>;
                const totalConnectes = <?php echo $total_connected; ?>;
                </script>
                <div class="chart"> <canvas id="chart-1"></canvas> </div>
                <div class="chart"> <canvas id="chart-2"></canvas> </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="js/mainadmin.js"></script>
    <script src="js/chartsJS.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>