<?php

// Informations de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'projet_php');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonction pour gérer l'upload d'images via TinyMCE
if (isset($_FILES['file'])) {
    $response = array();
    $file = $_FILES['file'];
    
    // Vérifier les erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response = array('error' => 'Erreur lors du téléchargement');
        echo json_encode($response);
        exit;
    }

    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        $response = array('error' => 'Type de fichier non autorisé');
        echo json_encode($response);
        exit;
    }

    // Créer le dossier uploads s'il n'existe pas
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $response = array(
            'location' => $filepath
        );
    } else {
        $response = array('error' => 'Erreur lors de l\'enregistrement du fichier');
    }

    echo json_encode($response);
    exit;
}

// Vérification de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['file'])) {
    // Récupération des données du formulaire
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $premium = isset($_POST['premium']) ? intval($_POST['premium']) : 0;
    $date_publication = date('Y-m-d H:i:s');
    $statut = 'brouillon'; // Par défaut en brouillon

    // Traitement de l'image
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $targetFile = $targetDir . uniqid() . '.' . $imageFileType;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $targetFile;
        }
    }

    try {
        // Nettoyer le titre avant l'insertion
        $titre = htmlspecialchars_decode($titre, ENT_QUOTES);
        
        if (isset($_GET['id'])) {
            // Mise à jour d'un article existant
            $stmt = $pdo->prepare("UPDATE articles SET titre = ?, contenu = ?, premium = ?" . ($image ? ", image = ?" : "") . " WHERE id = ?");
            $params = [$titre, $contenu, $premium];
            if ($image) {
                $params[] = $image;
            }
            $params[] = $_GET['id'];
            $stmt->execute($params);
        } else {
            // Création d'un nouvel article
            $stmt = $pdo->prepare("INSERT INTO articles (titre, contenu, date_publication, statut, premium, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $contenu, $date_publication, $statut, $premium, $image]);
        }
        
        header("Location: articles.php");
        exit();
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un article</title>
    <script src="https://cdn.tiny.cloud/1/ih6f834scrcsq2g32n8gm97fxig2p505hsdjg77tdcl1r7bc/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="css/style1.css">
    <style>
        :root {
            --primary-color: #808080;
            --white: #ffffff;
            --red: #dc3545;
            --blue: #808080;
            --green: #808080;
        }

        .article-form {
            position: relative;
            width: 100%;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 20px;
        }

        .article-form form {
            position: relative;
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
        }

        .article-form .inputBox {
            margin-bottom: 20px;
        }

        .article-form .inputBox label {
            font-size: 1rem;
            color: var(--black1);
            margin-bottom: 5px;
            display: block;
            font-weight: 500;
        }

        .article-form .inputBox input[type="text"],
        .article-form .inputBox input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            outline: none;
            font-size: 1rem;
        }

        .article-form .radio-group {
            display: flex;
            gap: 20px;
            margin: 10px 0;
        }

        .article-form .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .article-form .form-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nike-button-container {
            display: inline-flex;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: var(--primary-color);
        }

        .nike-button {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nike-button ion-icon {
            font-size: 20px;
        }

        .nike-button-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .nike-button-container.success:hover {
            background: #6a6a6a;
        }

        .nike-button-container.danger:hover {
            background: #bb2d3b;
        }

        .nike-button-container:active {
            transform: translateY(0);
        }

        .nike-button-container.success {
            background: var(--primary-color);
        }

        .nike-button-container.danger {
            background: var(--red);
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: flex-start;
        }

        .tox-tinymce {
            border-radius: 5px !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
        }

        .article-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .article-actions .view {
            background: var(--blue);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.3s;
            text-decoration: none;
        }

        .article-actions .view:hover {
            background: #0056b3;
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
                    <img src="images/Profil Admin.png" alt="">
                </div>
            </div>

            <!-- ======================= Article Form ================== -->
            <div class="article-form">
                <form action="add_article.php" method="POST" enctype="multipart/form-data">
                    <h2 class="mb-4">Ajouter un nouvel article</h2>
                    
                    <div class="inputBox">
                        <label for="titre">Titre de l'article</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    
                    <div class="inputBox">
                        <label for="image">Image de couverture</label>
                        <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif">
                    </div>
                    
                    <div class="inputBox">
                        <label for="contenu">Contenu de l'article</label>
                        <textarea id="contenu" name="contenu"></textarea>
                    </div>

                    <div class="inputBox">
                        <label>Type d'article</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="premium" value="0" checked>
                                <span>Standard</span>
                            </label>
                            <label>
                                <input type="radio" name="premium" value="1">
                                <span>Premium</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="nike-button-container success">
                            <span class="nike-button">
                                <span class="button-text"><?php echo isset($_GET['id']) ? 'Modifier l\'article' : 'Ajouter l\'article'; ?></span>
                                <ion-icon name="<?php echo isset($_GET['id']) ? 'create-outline' : 'add-circle-outline'; ?>"></ion-icon>
                            </span>
                        </button>
                        <a href="articles.php" class="nike-button-container danger">
                            <span class="nike-button">
                                <span class="button-text">Annuler</span>
                                <ion-icon name="close-circle-outline"></ion-icon>
                            </span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="js/mainadmin.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <script>
        tinymce.init({
            selector: '#contenu',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            images_upload_url: 'add_article.php',
            automatic_uploads: true,
            images_reuse_filename: true,
            file_picker_types: 'image',
            image_dimensions: true,
            image_class_list: [
                {title: 'Responsive', value: 'img-fluid'},
                {title: 'Petite', value: 'img-small'},
                {title: 'Moyenne', value: 'img-medium'},
                {title: 'Grande', value: 'img-large'}
            ],
            content_style: `
                body { font-family:Helvetica,Arial,sans-serif; font-size:14px }
                .img-fluid { max-width: 100%; height: auto; }
                .img-small { max-width: 300px; height: auto; }
                .img-medium { max-width: 500px; height: auto; }
                .img-large { max-width: 800px; height: auto; }
            `,
            image_advtab: true,
            file_picker_callback: function(callback, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function() {
                    var file = this.files[0];
                    var formData = new FormData();
                    formData.append('file', file);

                    fetch('add_article.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.location) {
                            // Créer une image temporaire pour obtenir les dimensions
                            var img = new Image();
                            img.onload = function() {
                                callback(result.location, {
                                    alt: file.name,
                                    width: this.width,
                                    height: this.height
                                });
                            };
                            img.src = result.location;
                        } else {
                            throw new Error(result.error || 'Upload failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Upload failed');
                    });
                };

                input.click();
            }
        });
    </script>
</body>
</html>
