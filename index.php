<?php
session_start();
// Inclure le fichier de connexion à la base de données
include 'connexionbdd.php';

function get_stores() {
    // Connexion à la base de données
    $conn = get_db_connection();

    // Requête pour récupérer le nom, l'email, le téléphone et l'adresse des magasins
    $query = "SELECT nom, adresse, telephone, email FROM magasin";

    // Exécution de la requête
    $result = pg_query($conn, $query);

    if (!$result) {
        // Gestion d'erreur en cas d'échec de la requête
        die("Erreur lors de la récupération des magasins : " . pg_last_error());
    }

    // Récupération des résultats sous forme de tableau associatif
    $stores = pg_fetch_all($result);

    // Fermeture de la connexion
    pg_close($conn);

    return $stores;
}
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shine & Silk</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
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

    <!-- Main Section -->
   
<section class="main-section">
    <div class="image-container">
        <img id="slideshow" src="photo/image1.jpg" alt="Image principale">
    </div>
    <div class="main-content">
        <h1 class="titre">Shine & Silk</h1>
        <p>Découvrez tous nos produits et cumulez des points à chaque achat pour bénéficier d'une réduction.</p>
        <button onclick="window.location.href='index.php#concept-section';">en savoir plus</button>
    </div>
</section>
<section class="concept-section" id="concept-section">
    <div class="concept-container">
        <h2>Notre Concept</h2>
        <p>
            Chez <strong>Shine & Silk</strong>, nous croyons en l'importance de prendre soin de soi tout en respectant l'environnement. 
            Nos produits, soigneusement sélectionnés, allient performance et durabilité. Nous vous proposons une gamme d'appareils et de produits de coiffure et  haut de gamme, 
            tout en vous récompensant à chaque achat grâce à notre programme de fidélité. 
        </p>
        <p>
            Rejoignez-nous en faisant votre premier achat en boutique  pour transformer vos routines quotidiennes en expériences luxueuses et respectueuses. 
        </p>
        <button class="learn-more" onclick="window.location.href='index.php#magasin';"> nos magasins</button>
    </div>
</section>
<section class="commitments-section">
    <div class="commitments-container">
        <h2 class="commitments-title">Nos Engagements</h2>
        <div class="commitments-row">
            <div class="commitment-block">
                <h3>Qualité Premium</h3>
                <p>Nous nous engageons à offrir des produits de haute qualité, conçus avec des matériaux résistants et des technologies innovantes. Chaque produit allie performance, fiabilité et confort pour répondre à vos besoins les plus exigeants.</p>
            </div>
            <div class="commitment-block">
                <h3>Durabilité</h3>
                <p>Nous privilégions des produits respectueux de l'environnement en utilisant des matériaux recyclables et des conceptions durables. Nos appareils sont optimisés pour réduire leur empreinte écologique tout en restant performants.</p>
            </div>
            <div class="commitment-block">
                <h3>Proximité</h3>
                <p>Votre satisfaction est notre priorité. Notre service client est à votre écoute pour répondre à vos questions. De plus, notre programme de fidélité vous offre des avantages exclusifs adaptés à vos besoins.</p>
            </div>
        </div>
    </div>
</section>

<section class="testimonials">
    <h2>Ce que disent nos clients</h2>
    <div class="testimonial">
        <p>"Les produits Shine & Silk ont transformé ma routine. J'adore leur programme de fidélité !"</p>
        <span>- Sarah L.</span>
    </div>
    <div class="testimonial">
        <p>"Des appareils d'une qualité exceptionnelle, parfaits pour mes cheveux ."</p>
        <span>- Julien M.</span>
    </div>
</section>


 <!-- Nos Magasins Section -->
 <section class="stores-section" id="magasin">
    <div class="stores-container">
        <h2 class="commitments-title">Nos Magasins</h2>
        <div class="stores-row">
            <?php
            // Inclure le fichier contenant la fonction pour récupérer les magasins
            

            // Récupérer les magasins
            $stores = get_stores();

            // Afficher chaque magasin dans un bloc
            if ($stores) {
                foreach ($stores as $store) {
                    echo '<div class="store-block">';
                    echo '<h3>' . htmlspecialchars($store['nom']) . '</h3>';
                    echo '<p>Adresse : ' . htmlspecialchars($store['adresse']) . '</p>';
                    echo '<p>Contact : ' . htmlspecialchars($store['telephone']) . '</p>';
                    echo '<p>Email : ' . htmlspecialchars($store['email']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun magasin trouvé.</p>';
            }
            ?>
        </div>
    </div>
</section>


    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Fidélité Shine & Silk. Tous droits réservés.</p>
    </footer>

  
</body>
</html>
