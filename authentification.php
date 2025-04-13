<?php
// Inclure les fichiers de PHPMailer manuellement
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$notification = ""; // Variable pour stocker le message de notification
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Connexion à la base de données
    include_once 'connexionbdd.php';
    $conn = get_db_connection();

    // Vérifier si l'utilisateur a déjà un mot de passe
    $query = "SELECT mot_de_passe FROM client WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if ($result) {
        $user = pg_fetch_assoc($result);

        if ($user['mot_de_passe'] == NULL) {
            // Générer un mot de passe aléatoire
            $new_password = bin2hex(random_bytes(4)); // 8 octets -> 16 caractères

            // Hachage du mot de passe avant de l'enregistrer dans la BDD
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Mise à jour du mot de passe dans la base
            $update_query = "UPDATE client SET mot_de_passe = $1 WHERE email = $2";
            $update_result = pg_query_params($conn, $update_query, array($hashed_password, $email));

            if ($update_result) {
                // Préparer l'envoi du mail
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                try {
                    // Configuration du serveur SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Utiliser Gmail
                    $mail->SMTPAuth = true;
                    $mail->Username = 'reply5385@gmail.com'; // Remplacez par votre e-mail Gmail
                    $mail->Password = 'wwob wfqk xozt ywpu'; // Mot de passe ou clé d'application
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Définir l'expéditeur et le destinataire
                    $mail->setFrom('no-reply@shineandsilk.com', 'Shine&Silk Support'); // Adresse no-reply
                    $mail->addAddress($email);

                    // Contenu de l'e-mail
                    $mail->isHTML(true);
                    $mail->Subject = 'Votre nouveau mot de passe';
                    $mail->Body = "
                        <p>Bonjour,</p>
                        <p>Voici votre nouveau mot de passe :</p>
                        <p><strong>$new_password</strong></p>
                        <p>Veuillez le changer dès que possible après connexion.</p>
                        <p>Cordialement,<br>L'équipe Shine&Silk</p>
                    ";
                    $mail->AltBody = "Bonjour,\n\nVoici votre nouveau mot de passe : $new_password\n\nVeuillez le changer dès que possible après connexion.\n\nCordialement,\nL'équipe Shine&Silk";

                    // Définir l'encodage des caractères pour éviter les problèmes avec les caractères spéciaux
                    $mail->CharSet = 'UTF-8';

                    // Envoyer l'e-mail
                    if ($mail->send()) {
                        $notification = "Un e-mail a été envoyé à : $email. Vérifiez votre boîte de réception pour récupérer votre mot de passe.";
                    } else {
                        $notification = "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
                    }
                } catch (Exception $e) {
                    $notification = "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
                }
            } else {
                $notification = "Erreur lors de la mise à jour du mot de passe : " . pg_last_error($conn);
            }
        } else {
            // Vérification de la correspondance du mot de passe
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $password = $_POST['password'];

                // Vérifier si le mot de passe correspond à celui stocké dans la base de données
                if (password_verify($password, $user['mot_de_passe'])) {
                    // Mot de passe correct, rediriger l'utilisateur
                    $_SESSION['email'] = $email;
                    header('Location: profil.php');
                    exit();  // Assurez-vous de stopper l'exécution du script après la redirection
                } else {
                    $notification = "Le mot de passe ne correspond pas.";
                }
            }
        }
    } else {
        $notification = "Erreur de récupération du mot de passe : " . pg_last_error($conn);
    }

    // Fermer la connexion
    pg_close($conn);
} else {
    $notification = "Méthode non autorisée.";
}

echo $notification; // Afficher le message de notification à l'utilisateur
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
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
            width: 400px;
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
        .notification {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #FF0000;
            color: white;
            padding: 10px;
         
            display: none;
            font-size: 1em;
        }
        .notification.error { background-color: #f44336; }
        .notification.warning { background-color: #ff9800; }
    </style>
</head>
<body>
    <form action="" method="POST">
        <label id="lab" for="conn">Se connecter</label>
        <img src="img4.png" alt="logo">
        <p>Saisissez le mot de passe </p>
        <input type="password" name="password" placeholder="mot de passe" required>
        <button type="submit">Connexion</button>
    </form>
    <div id="notification" class="notification"></div>

    <script>
        function showNotification(message) {
            const notificationElement = document.getElementById('notification');
            notificationElement.textContent = message;
            notificationElement.style.display = 'block';
            setTimeout(() => notificationElement.style.display = 'none', 3000); // Masquer après 5 secondes
        }
    </script>
</body>
</html>
