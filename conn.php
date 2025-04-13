<?php
session_start(); // Démarre la session


// Initialiser le message d'erreur
$error_message = "";

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['mail']); // Nettoyer l'entrée utilisateur

    // Connexion à la base de données
    include_once 'connexionbdd.php';
    $conn = get_db_connection();

    // Requête pour vérifier si l'email existe dans la base de données
    $query = "SELECT id_client FROM client WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if (pg_num_rows($result) == 0) {
        // Si l'email n'existe pas, définir le message d'erreur
        $error_message = "Email non trouvé. Veuillez vérifier votre e-mail.";
        $_SESSION['error_message'] = $error_message; // Stocker dans la session
    } else {
        // Rediriger vers la page d'authentification si l'email existe
        $_SESSION['email'] = $email;
        header('Location: authentification.php');
        exit();
    }

    
    // Fermer la connexion
    pg_close($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        /* Styles CSS */
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #d1bbbb;
            font-family: Arial, sans-serif;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            height: 60%;
            text-align: center;
        }
        img {
            width: 150px;
            padding-bottom: 15%;
        }
        #lab {
            font-weight: bold;
            color: #555;
            font-size: 0.8em;
            margin-bottom: 10px;
            text-align: left;
            width: 100%;
        }
        p {
            margin: 10px 0;
            font-size: 0.9em;
            color: #555;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #000;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #4500a8;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
      <script>
        // Script pour faire disparaître le message après un délai
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = document.querySelector('.error-message');
            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.opacity = '0'; // Commencer la disparition
                    setTimeout(() => errorMessage.remove(), 500); // Retirer après l'animation
                }, 2000); // Délai de 5 secondes
            }
        });
    </script>
</head>
<body>
    <form action="" method="POST">
        <label id="lab" for="conn">Se connecter</label>
        <img src="img4.png" alt="logo">
        <p>Saisissez votre adresse e-mail et nous vous enverrons votre mot de passe</p>
        <input type="email" name="mail" placeholder="E-mail" required>
        <?php
        // Afficher le message d'erreur, s'il existe
        if (isset($_SESSION['error_message'])) {
            echo "<div class='error-message'>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
            unset($_SESSION['error_message']); // Réinitialiser le message
        }
        ?>
        <button type="submit">Continuer</button>
    </form>
</body>
</html>
