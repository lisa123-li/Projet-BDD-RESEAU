<?php
// Fichier : connexionbdd.php

function get_db_connection() {
    $host = "localhost";      // Hôte PostgreSQL
    $port = "5432";           // Port PostgreSQL
    $dbname = "base";       // Nom de votre base de données
    $user = "lisa";           // Nom d'utilisateur PostgreSQL
    $password = "ariolitona"; // Mot de passe PostgreSQL

    // Connexion à la base de données avec pg_connect
    $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

    if (!$conn) {
        // Gérer l'erreur de connexion
        die("Erreur de connexion à la base de données : " . pg_last_error());
    }

    return $conn;
}
