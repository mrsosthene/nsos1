<?php
// Récupère les données GET envoyées depuis register.php
$prenom = isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : "Utilisateur";
$nom = isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : "";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inscription Réussie</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div
      class="bg-white w-full max-w-md rounded-xl shadow-lg text-center p-8"
    >
      <h1 class="text-3xl font-bold text-green-500 mb-4">Inscription réussie !</h1>
      <p class="text-gray-700 mb-4">
        Félicitations <strong><?php echo "$prenom $nom"; ?></strong> 🎉, votre
        inscription a été réalisée avec succès !
      </p>
      <a
        href="blog.html"
        class="inline-block mt-4 px-6 py-2 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600"
      >
        Retour à l'accueil
      </a>
    </div>
  </body>
</html>
