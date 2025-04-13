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
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Centre horizontalement */
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


<h1>Promotions</h1>



<div class="products-container">

<?php
    // Connexion à la base de données
    include_once 'connexionbdd.php';
    $conn = get_db_connection();

    $query = "
        SELECT 
            e.image_path,
            e.description,
            e.nom,
            p.pourcentage,
            e.prix,
            p.description AS promo_description
        FROM 
            article e
        LEFT JOIN 
            appareil_coiffure a ON e.reference_produit = a.id_mat_coif
        LEFT JOIN 
            promotion p ON e.id_promotion = p.id_promotion
        WHERE 
            p.date_fin >= CURRENT_DATE
        ORDER BY 
            e.prix ASC;
    ";

    $result = pg_query($conn, $query);

    if (!$result) {
        echo "<p>Erreur lors de l'exécution de la requête : " . pg_last_error($conn) . "</p>";
    } else {
        while ($row = pg_fetch_assoc($result)) {
            // Calcul du prix après réduction
            $prix_apres_promo = $row['prix'];
            if (!empty($row['pourcentage'])) {
                $prix_apres_promo = $row['prix'] * (1 - $row['pourcentage'] / 100);
            }
            ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['nom']); ?>">
                    <div class="description-overlay">
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                </div>
                <h3><?php echo htmlspecialchars($row['nom']); ?></h3>
                
                <!-- Prix avant et après réduction -->
                <p>
                    <span style="text-decoration: line-through; color: #999;"><?php echo htmlspecialchars($row['prix']); ?> €</span>
                    <span style="color: red; font-weight: bold; margin-left: 10px;">
                        <?php echo number_format($prix_apres_promo, 2, ',', ''); ?> €
                    </span>
                </p>
                
                <p><strong>Promotion :</strong> <?php echo htmlspecialchars($row['promo_description']); ?></p>
            </div>
            <?php
        }
    }

    pg_close($conn);
?>

</div>
    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Fidélité Shine & Silk. Tous droits réservés.</p>
    </footer>
</body>
</html>
