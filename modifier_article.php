<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Fonction pour gérer l'upload d'images
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
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    if (!in_array($file['type'], $allowed_types)) {
        $response = array('error' => 'Type de fichier non autorisé');
        echo json_encode($response);
        exit;
    }

    // Créer le dossier uploads s'il n'existe pas
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Générer un nom de fichier unique
    $filename = uniqid() . '_' . basename($file['name']);
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

// Récupérer l'ID de l'article à modifier
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['file'])) {
    $titre = htmlspecialchars_decode($_POST['titre'], ENT_QUOTES);
    $contenu = $_POST['contenu'];
    $premium = isset($_POST['premium']) ? 1 : 0;
    $image = null;

    // Gestion de l'upload d'image local
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $image = $upload_path;
            }
        }
    }

    try {
        if ($image) {
            $stmt = $pdo->prepare("UPDATE articles SET titre = ?, contenu = ?, premium = ?, image = ? WHERE id = ?");
            $stmt->execute([$titre, $contenu, $premium, $image, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE articles SET titre = ?, contenu = ?, premium = ? WHERE id = ?");
            $stmt->execute([$titre, $contenu, $premium, $id]);
        }
        
        header('Location: actualites.php');
        exit();
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

// Récupérer les données de l'article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: actualites.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
    <script src="https://cdn.tiny.cloud/1/ih6f834scrcsq2g32n8gm97fxig2p505hsdjg77tdcl1r7bc/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="css/style1.css">
    <style>
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

        .article-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
        }

        .nike-button-container.success {
            background: var(--blue);
            color: var(--white);
        }

        .nike-button-container.info {
            background: #17a2b8;
            color: white;
        }

        .nike-button-container.danger {
            background: var(--red);
            color: white;
        }

        .nike-button {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .button-text {
            font-size: 1rem;
        }

        .tox-tinymce {
            border-radius: 5px !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
        }

        .article-actions a {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }

        .article-actions .cancel {
            background: #6c757d;
            color: white;
        }

        .article-actions .cancel:hover {
            background: #5a6268;
        }

        .article-actions .view {
            background: #28a745;
            color: white;
        }

        .article-actions .view:hover {
            background: #218838;
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
                    <a href="add_article.php">
                        <span class="icon">
                            <ion-icon name="add-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Nouvel Article</span>
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
                <form action="modifier_article.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <h2 class="mb-4">Modifier l'article</h2>
                    
                    <div class="inputBox">
                        <label for="titre">Titre de l'article</label>
                        <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($article['titre']); ?>" required>
                    </div>
                    
                    <div class="inputBox">
                        <label for="contenu">Contenu de l'article</label>
                        <textarea id="contenu" name="contenu"><?php echo htmlspecialchars($article['contenu']); ?></textarea>
                    </div>

                    <div class="inputBox">
                        <label>Type d'article</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="premium" value="0" <?php echo $article['premium'] == 0 ? 'checked' : ''; ?>>
                                <span>Standard</span>
                            </label>
                            <label>
                                <input type="radio" name="premium" value="1" <?php echo $article['premium'] == 1 ? 'checked' : ''; ?>>
                                <span>Premium</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="inputBox">
                        <label for="image">Image</label>
                        <input type="file" name="image" id="image" accept="image/jpeg, image/png, image/gif">
                        <?php if (!empty($article['image'])): ?>
                            <p>Image actuelle : <?php echo htmlspecialchars($article['image']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="article-actions">
                        <button type="submit" class="nike-button-container success">
                            <span class="nike-button">
                                <span class="button-text">Mettre à jour l'article</span>
                                <ion-icon name="create-outline"></ion-icon>
                            </span>
                        </button>
                        <a href="lire_article.php?id=<?php echo $id; ?>" class="nike-button-container info">
                            <span class="nike-button">
                                <span class="button-text">Voir l'article</span>
                                <ion-icon name="eye-outline"></ion-icon>
                            </span>
                        </a>
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
            images_upload_url: 'modifier_article.php?id=<?php echo $id; ?>',
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
            file_picker_callback: function(callback, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function() {
                    var file = this.files[0];
                    var formData = new FormData();
                    formData.append('file', file);

                    fetch('modifier_article.php?id=<?php echo $id; ?>', {
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
