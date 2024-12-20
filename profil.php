<?php
session_start();
require_once 'config.php';

// Fonction de détection mobile
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

// Déterminer le lien du blog en fonction du device
$blog_link = isMobile() ? 'mobile_actualites.php' : 'actualites.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $nouveau_mot_de_passe = trim($_POST['nouveau_mot_de_passe']);
    
    $errors = [];
    
    // Validation des champs
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé";
        }
    }
    
    // Si pas d'erreurs, mettre à jour le profil
    if (empty($errors)) {
        try {
            if (!empty($nouveau_mot_de_passe)) {
                // Mise à jour avec nouveau mot de passe
                $hashed_password = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$nom, $email, $hashed_password, $user_id]);
            } else {
                // Mise à jour sans mot de passe
                $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ? WHERE id = ?");
                $stmt->execute([$nom, $email, $user_id]);
            }
            
            // Mettre à jour les informations de session
            $_SESSION['nom'] = $nom;
            
            $success = "Profil mis à jour avec succès";
            
            // Recharger les informations de l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch(PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour du profil";
        }
    }
}

// Initialiser les compteurs à 0 pour le moment
$comments_count = 0;
$likes_count = 0;
$recent_articles = [];

// Formater la date d'inscription
$date_inscription = isset($user['date_inscription']) ? date('d/m/Y', strtotime($user['date_inscription'])) : date('d/m/Y');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - <?php echo htmlspecialchars($user['nom']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #000000;
            color: #ffffff;
        }
        .profile-header {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)), url('imagesBlog/bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .form-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff4d4d 0%, #ff0000 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 0, 0, 0.2);
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            transition: transform 0.5s ease-out;
        }
        .btn-primary:hover::after {
            transform: translate(-50%, -50%) scale(2);
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.15);
        }
        .avatar {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ff4d4d 0%, #ff0000 100%);
        }
        .avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }
        label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header class="fixed w-full z-50 bg-black/90 backdrop-blur-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="blog1.php" class="text-2xl font-bold">Nsos</a>
            <nav class="flex space-x-8">
                <a href="blog1.php" class="text-gray-300 hover:text-white transition-colors">Accueil</a>
                <a href="<?php echo $blog_link; ?>" class="text-gray-300 hover:text-white transition-colors">Blog</a>
                <a href="contact.php" class="text-gray-300 hover:text-white transition-colors">Contact</a>
                <a href="logout.php" class="text-gray-300 hover:text-white transition-colors">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="min-h-screen pt-24 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête du profil -->
            <div class="profile-header rounded-3xl p-12 mb-8">
                <div class="flex items-center space-x-6">
                    <div class="avatar w-24 h-24 rounded-full flex items-center justify-center text-3xl font-bold">
                        <?php echo strtoupper(substr($user['nom'], 0, 1)); ?>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2"><?php echo htmlspecialchars($user['nom']); ?></h1>
                        <p class="text-gray-300">Membre depuis le <?php echo $date_inscription; ?></p>
                    </div>
                </div>
            </div>

            <!-- Messages d'erreur/succès -->
            <?php if (!empty($errors)): ?>
                <div class="mb-8 p-4 rounded-2xl bg-red-900/50 border border-red-500/50">
                    <?php foreach ($errors as $error): ?>
                        <p class="text-red-300"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="mb-8 p-4 rounded-2xl bg-green-900/50 border border-green-500/50">
                    <p class="text-green-300"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="bg-white/5 backdrop-blur-lg rounded-3xl p-8 border border-white/10 shadow-2xl">
                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label for="nom" class="block text-sm mb-2">Nom</label>
                        <input type="text" id="nom" name="nom" 
                               value="<?php echo htmlspecialchars($user['nom']); ?>"
                               class="form-input w-full h-12 px-4 rounded-xl">
                    </div>

                    <div>
                        <label for="email" class="block text-sm mb-2">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               class="form-input w-full h-12 px-4 rounded-xl">
                    </div>

                    <div>
                        <label for="nouveau_mot_de_passe" class="block text-sm mb-2">
                            Nouveau mot de passe
                        </label>
                        <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe"
                               placeholder="Laisser vide pour ne pas changer"
                               class="form-input w-full h-12 px-4 rounded-xl">
                    </div>

                    <div class="flex justify-end space-x-4 pt-6">
                        <a href="blog1.php" 
                           class="px-8 py-3 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-all duration-300">
                            Retour
                        </a>
                        <button type="submit" 
                                class="btn-primary px-8 py-3 text-white rounded-xl relative overflow-hidden">
                            <span class="relative z-10">Mettre à jour</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="stat-card p-6 rounded-2xl">
                    <div class="text-gray-400 text-sm font-medium">Commentaires</div>
                    <div class="mt-2 text-3xl font-bold"><?php echo $comments_count; ?></div>
                </div>
                <div class="stat-card p-6 rounded-2xl">
                    <div class="text-gray-400 text-sm font-medium">J'aime</div>
                    <div class="mt-2 text-3xl font-bold"><?php echo $likes_count; ?></div>
                </div>
                <div class="stat-card p-6 rounded-2xl">
                    <div class="text-gray-400 text-sm font-medium">Statut</div>
                    <div class="mt-2 text-2xl font-bold"><?php echo ucfirst($user['role'] ?? 'Membre'); ?></div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
