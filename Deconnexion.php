<?php
session_start();  // Démarre la session

// Supprimer toutes les variables de session
session_unset();

// Détruire la session
session_destroy();

// Rediriger vers une page de déconnexion réussie ou une autre page appropriée
header("Location: index.php");
exit;
?>
