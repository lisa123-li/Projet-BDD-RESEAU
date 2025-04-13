<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Nos Produits</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            
        }
        h1 {
            text-align: center;
            color: #333;
            margin-top: 20px;
            font-size: 2em;
            font-weight: 600;
        }

        /* Style du formulaire */
        form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }
        form label {
            font-size: 1em;
            color: #555;
            font-weight: 600;
        }
        form select {
            padding: 8px 12px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
            transition: border-color 0.3s ease;
        }
        form select:focus {
            outline: none;
            border-color: #007bff;
        }
        form button {
            padding: 8px 16px;
            font-size: 1em;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form button:hover {
            background-color: #4500a8;
        }

        /* Conteneur des produits */
        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 50px;
        }

        /* Carte de produit */
        .product-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            height: 360px;
            overflow: hidden;
        }

        /* Image du produit */
        .product-image {
            width: 100%;
            height: 350px;
            overflow: hidden;
            border-radius: 6px;
            margin-bottom: 15px;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        /* Titre du produit */
        .product-card h3 {
            font-size: 1.1em;
            color: #333;
            margin: 10px 0;
            font-weight: 600;
        }

        /* Descriptions et détails du produit */
        .product-card p {
            font-size: 0.8em;
            color: rgba(0, 0, 0, 0.7);
            margin: 2px 0;
            opacity: 0.8;
        }

        .product-card p strong {
            color: #333;
        }

        /* Overlay avec la description */
        .description-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(10px);
            border-radius: 6px;
        }

        .product-card:hover .description-overlay {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Top bar -->
    <div class="top-bar">
        <div class="social-icons">
            <img src="photo/instagram.png" alt="Instagram">
            <img src="photo/facebook.png" alt="Facebook">
            <img src="photo/twitter.png" alt="Twitter">
        </div>
    </div>

    <?php
  if (isset($_SESSION['email'])) {
    include 'headerConnect.php';
  } else {
    include 'headerNoConnect.php';
  }
  ?>


<h1>Produits de coiffure</h1>

<!-- Formulaire pour trier les produits -->
<form method="GET" action="">
    <label for="order-by">Trier par :</label>
    <select name="order-by" id="order-by">
        <option value="prix_asc" <?php echo (isset($_GET['order-by']) && $_GET['order-by'] === 'prix_asc') ? 'selected' : ''; ?>>Prix croissant</option>
        <option value="prix_desc" <?php echo (isset($_GET['order-by']) && $_GET['order-by'] === 'prix_desc') ? 'selected' : ''; ?>>Prix décroissant</option>
        <option value="type" <?php echo (isset($_GET['order-by']) && $_GET['order-by'] === 'type') ? 'selected' : ''; ?>>Type d'appareil</option>
    </select>
    <button type="submit">Appliquer</button>
</form>

<div class="products-container">
<?php
    // Connexion à la base de données
    include_once 'connexionbdd.php';
    $conn = get_db_connection();

    // Critère d'ordre par défaut
    $orderBy = "prix_asc";
    if (isset($_GET['order-by'])) {
        $orderBy = $_GET['order-by'];
    }

    // Ajuster la clause ORDER BY en fonction du choix
    $orderClause = "";
    switch ($orderBy) {
        case "prix_asc":
            $orderClause = "ORDER BY e.prix ASC";
            break;
        case "prix_desc":
            $orderClause = "ORDER BY e.prix DESC";
            break;
        case "type":
            $orderClause = "ORDER BY a.type_appareil ASC";
            break;
        default:
            $orderClause = "ORDER BY e.prix ASC";
    }

    $query = "SELECT contenance, composition, usage, image_path, e.prix, type_produit, nom, description
              FROM produit_coiffure a
              JOIN article e ON a.id_prod_coif = e.reference_produit
              $orderClause";

    $result = pg_query($conn, $query);

    if (!$result) {
        echo "<p>Erreur lors de l'exécution de la requête : " . pg_last_error($conn) . "</p>";
    } else {
        while ($row = pg_fetch_assoc($result)) {
            ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['type_appareil']); ?>">
                    <div class="description-overlay">
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                </div>
                <h3><?php echo htmlspecialchars($row['type_appareil']); ?></h3>
                <p><strong>Contenance :</strong> <?php echo htmlspecialchars($row['contenance']); ?> W</p>
                <p><strong>Composition :</strong> <?php echo htmlspecialchars($row['composition']); ?> °C</p>
                <p><strong>Usage :</strong> <?php echo htmlspecialchars($row['usage']); ?></p>
                <p><strong>Prix :</strong> <?php echo htmlspecialchars($row['prix']); ?> €</p>
            </div>
            <?php
        }
    }

    // Fermer la connexion
    pg_close($conn);
?>
</div>
    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Fidélité Shine & Silk. Tous droits réservés.</p>
    </footer>
</body>
</html>
