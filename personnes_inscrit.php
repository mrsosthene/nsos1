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

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");

// Gestion de la suppression d'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = (int)$_POST['id'];

        try {
            // Commencer une transaction
            $pdo->beginTransaction();

            // 1. D'abord supprimer les vues d'articles de l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM article_views WHERE user_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Ensuite supprimer l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Valider la transaction
            $pdo->commit();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            // En cas d'erreur, annuler la transaction
            $pdo->rollBack();
            echo "<script>alert('Erreur lors de la suppression : " . $e->getMessage() . "');</script>";
        }
    }
}

// Vérifier si le bouton "Nommer Admin" a été cliqué
if (isset($_POST['make_admin'])) {
    $user_id = $_POST['user_id'];
    $stmt_admin = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = :id");
    $stmt_admin->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt_admin->execute()) {
        echo "<script>alert('Utilisateur nommé administrateur avec succès');</script>";
        echo "<script>window.location.href = window.location.href;</script>";
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
    <title>Personnes Inscrites</title>
    <link rel="stylesheet" href="css/style1.css">
    <style>
        .details {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        
        .recentOrders {
            width: 90%;
            background: #fff;
            padding: 20px;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
            border-radius: 20px;
        }
        
        .cardHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table thead td {
            font-weight: 600;
            background: #f0f0f0;
            padding: 12px;
            text-align: center;
        }
        
        table tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        table tbody td {
            padding: 12px;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        
        table tbody td:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .admin-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 120px;
        }
        
        .admin-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .delete-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100px;
        }
        
        .delete-btn:hover {
            background: #ff0000;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .admin-badge {
            background: #2196F3;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            display: inline-block;
            min-width: 80px;
        }

        .action-cell {
            padding: 8px;
        }

        .action-cell:hover {
            background-color: transparent;
        }
    </style>
</head>
<body>
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
                        <span class="title">Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="add_article.php">
                        <span class="icon">
                            <ion-icon name="add-outline"></ion-icon>
                        </span>
                        <span class="title">Ajouter un article</span>
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

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="user">
                    <img src="images/Profil Admin.png" alt="">
                </div>
            </div>

            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Personnes Inscrites</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <td>Nom</td>
                                <td>Prénom</td>
                                <td>Sexe</td>
                                <td>Rôle</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['prenom']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['sexe']) . "</td>";
                                echo "<td class='action-cell'>";
                                if ($row['role'] !== 'admin') {
                                    echo "<form method='POST' onsubmit=\"return confirm('Êtes-vous sûr de vouloir nommer administrateur cet utilisateur ?');\" style='display:inline;'>
                                            <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                                            <button type='submit' name='make_admin' class='admin-btn'>
                                                Nommer Admin
                                            </button>
                                        </form>";
                                } else {
                                    echo "<span class='admin-badge'>Admin</span>";
                                }
                                echo "</td><td class='action-cell'>
                                <form method='POST' onsubmit=\"return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');\" style='display:inline;'>
                                    <input type='hidden' name='action' value='supprimer'>
                                    <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                                    <button type='submit' class='delete-btn'>Supprimer</button>
                                </form>
                                </td>";
                                echo "</tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/mainadmin.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
