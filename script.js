// Liste des images pour le diaporama
const images = [
    'photo/image1.jpg',
    'photo/image2.jpg',
    'photo/image3.jpg',
    
    'photo/image5.jpg',
    'photo/image6.jpg',
    'photo/image7.jpg',
];

let currentIndex = 0; // Index de l'image actuelle

// Fonction pour changer l'image
function changeImage() {
    // Retirer la classe active de l'image actuelle
    const currentImage = document.querySelector('.image-container img.active');
    if (currentImage) {
        currentImage.classList.remove('active');
    }

    // Passer à la suivante
    currentIndex = (currentIndex + 1) % images.length;

    // Trouver l'image correspondante
    const newImage = document.querySelectorAll('.image-container img')[currentIndex];

    // Ajouter la classe active à la nouvelle image
    newImage.classList.add('active');
}

// Créer dynamiquement les éléments <img> et les ajouter à l'HTML
const imageContainer = document.querySelector('.image-container');
images.forEach((image, index) => {
    const imgElement = document.createElement('img');
    imgElement.src = image;
    imgElement.alt = `Image ${index + 1}`;
    if (index === 0) imgElement.classList.add('active'); // L'image initiale est visible
    imageContainer.appendChild(imgElement);
});

// Changer d'image toutes les 5 secondes
setInterval(changeImage, 2000);
