-- Database: projet

-- DROP DATABASE IF EXISTS projet;

-- Connexion et accès au schéma
GRANT CONNECT ON DATABASE projetf TO dyhia;
GRANT USAGE ON SCHEMA public TO dyhia;

-- Permissions sur les tables existantes et futures
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO dyhia;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO dyhia;

-- Permissions sur les séquences
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO dyhia;

SELECT points,id_carte from client;

--Creation de la table magasin
CREATE TABLE magasin (
    id_magasin Serial,
    nom VARCHAR(50) NOT NULL,
    adresse VARCHAR(100) NOT NULL,
    telephone VARCHAR(10) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    nbr_employes INT NOT NULL,
    CONSTRAINT PK_magasin PRIMARY KEY (id_magasin),
    CONSTRAINT Tel_magasin CHECK (telephone ~ '^[0-9]{10}$'),
    CONSTRAINT Email_magasin CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT employes CHECK (nbr_employes >0)
);

--Creation de la table client
CREATE TABLE client (
    id_client VARCHAR(8),
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(10) UNIQUE NOT NULL,
    adresse VARCHAR(100) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255),
    date_inscription DATE NOT NULL,
    points INT DEFAULT 0,
    id_carte varchar(8) UNIQUE NOT NULL,
    CONSTRAINT PK_client PRIMARY KEY (id_client),
    CONSTRAINT Tel_client CHECK (telephone ~ '^[0-9]{10}$'),
    CONSTRAINT Email_client CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT Points_client CHECK (points >= 0)
);

--Creation de la table fournisseur
CREATE TABLE fournisseur (
    id_fournisseur SERIAL,
    nom VARCHAR(100) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    email VARCHAR(100) unique NOT NULL,
    telephone VARCHAR(10) unique NOT NULL,
    CONSTRAINT pk_fournisseur PRIMARY KEY (id_fournisseur),
    CONSTRAINT chk_email CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT chk_telephone CHECK (telephone ~ '^[0-9]{10}$')
);

--Creation de la table MSA (Matériel de Service Automatisé)
CREATE TABLE msa (
    id_msa VARCHAR(8),
    date_mise_service DATE NOT NULL,
    marque VARCHAR(50) NOT NULL,
    type_connexion VARCHAR(50) NOT NULL,
    id_magasin INT NOT NULL,
    CONSTRAINT PK_msa PRIMARY KEY (id_msa),
    CONSTRAINT FK_msa_magasin FOREIGN KEY (id_magasin) REFERENCES magasin (id_magasin) ON DELETE SET NULL,
    CONSTRAINT typ_con_CH CHECK (type_connexion IN ('cable', 'sans fil'))
);



--Creation de la table promotion 
CREATE TABLE promotion (
    id_promotion Serial,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    pourcentage INT,
    description VARCHAR(100) NOT NULL,
	CONSTRAINT PK_promo Primary Key (id_promotion),
	CONSTRAINT chk_date_fin CHECK (date_fin >= CURRENT_DATE),
	CONSTRAINT chk_pourcentage CHECK (pourcentage BETWEEN 0 AND 100)
);


--Creation de la table article
CREATE TABLE article (
    reference_produit Varchar(8),
    prix DECIMAL(10, 4) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    id_promotion INT DEFAULT NULL,
    image_path VARCHAR(255),
    point int not null,
    CONSTRAINT PK_article PRIMARY KEY (reference_produit),
    CONSTRAINT FK_article_promotion FOREIGN KEY (id_promotion) REFERENCES promotion (id_promotion) ON DELETE SET NULL,
    CONSTRAINT chk_point Check (point>=0),
    CONSTRAINT chk_prix Check (prix >0)
);

-- Creation de la table produit_coiffure(elle herite de la table article)
CREATE TABLE produit_coiffure (
    id_prod_coif VARCHAR(8),
    contenance DECIMAL(5, 2)NOT NULL,
    composition TEXT NOT NULL,
    usage VARCHAR(250) NOT NULL,
    type_produit VARCHAR(50)NOT NULL,
    CONSTRAINT PK_prod_coif PRIMARY KEY (id_prod_coif),
    CONSTRAINT FK_prod_coif_article FOREIGN KEY (id_prod_coif) REFERENCES article (reference_produit) ON DELETE CASCADE,
    CONSTRAINT chk_type_produit CHECK (type_produit IN ('Shampoing', 'Après-shampoing', 'Masque', 'Huile', 'Sérum', 'Spray','Mousse capillaire'))
   
);

--Creation de la table appareil_coiffure (elle herite de la table article)
CREATE TABLE appareil_coiffure (
    id_mat_coif VARCHAR(8),
    puissance INT NOT NULL,
    temperature_max INT NOT NULL,
    type_appareil VARCHAR(50) NOT NULL,
    technologie VARCHAR(50) NOT NULL,
    CONSTRAINT PK_app_coif PRIMARY KEY (id_mat_coif),
    CONSTRAINT FK_app_coif_article FOREIGN KEY (id_mat_coif) REFERENCES article (reference_produit) ON DELETE CASCADE,
    CONSTRAINT chk_technologie CHECK (technologie IN ('Ionique', 'Infrarouge', 'Céramique', 'Vapeur', 'Nano', 'Ultrasonique', 'Séchage rapide', 'ThermoProtect')),
    CONSTRAINT chk_type_appareil CHECK (type_appareil IN ('Sèche-cheveux', 'Fer à lisser', 'Boucleur', 'Tondeuse', 'Brosse chauffante', 'Diffuseur')),
    CONSTRAINT chk_temp CHECK (temperature_max > 0),
    CONSTRAINT chk_puiss CHECK (puissance > 0)
);
--table fournit
CREATE TABLE fournit (
    id_magasin INT NOT NULL,                           
    id_article varchar(8) NOT NULL,                          
    id_fournisseur INT NOT NULL,                      
    quantite_fournie INT NOT NULL ,  
    date DATE NOT NULL,                                
    CONSTRAINT PK_fournir PRIMARY KEY (id_magasin, id_article, id_fournisseur,date),  
    CONSTRAINT FK_fournir_magasin FOREIGN KEY (id_magasin) REFERENCES magasin(id_magasin) ON DELETE CASCADE,  
    CONSTRAINT FK_fournir_article FOREIGN KEY (id_article) REFERENCES article(reference_produit) ON DELETE CASCADE,  
    CONSTRAINT FK_fournir_fournisseur FOREIGN KEY (id_fournisseur) REFERENCES fournisseur(id_fournisseur) ON DELETE CASCADE
);
--Creation de la table achete 
CREATE TABLE achete (
    id_article VARCHAR(8),
    id_client VARCHAR(8),
    quantite_achetee INT NOT NULL,
    date_achat DATE NOT NULL,
    heure_achat TIME NOT NULL,
    CONSTRAINT fk_achat_article FOREIGN KEY (id_article) REFERENCES article(reference_produit) ON DELETE CASCADE,
    CONSTRAINT fk_achat_client FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE CASCADE,
    CONSTRAINT chk_quantite CHECK (quantite_achetee > 0),
    PRIMARY KEY (id_article, id_client,date_achat,heure_achat)
);

--Creation de la table facture
CREATE TABLE facture (
    id_facture SERIAL,
    points_cumules INT DEFAULT 0,
    montant_facture DECIMAL(10, 4) NOT NULL,
    date_facture DATE NOT NULL,
    heure_facture TIME NOT NULL,
    id_client VARCHAR(8) NOT NULL,
    id_magasin INT NOT NULL,
    CONSTRAINT PK_facture PRIMARY KEY (id_facture),
    CONSTRAINT FK_facture_client FOREIGN KEY (id_client) REFERENCES client (id_client) ON DELETE CASCADE,
    CONSTRAINT FK_facture_magasin FOREIGN KEY (id_magasin) REFERENCES magasin (id_magasin) ON DELETE CASCADE,
    CONSTRAINT montant_facture_CH CHECK (montant_facture >= 0)
);


--Insertion 

--insertion dans la table magasin
INSERT INTO magasin (id_magasin, nom, adresse, telephone, email, nbr_employes) 
VALUES 
(10000001, 'Shine & Silk', '10 rue de Paris, 75001 Paris', '0102030405', 'contactA@gmail.com', 10),
(10000002, 'Shine & Silk', '20 avenue des Champs-Élysées, 75008 Paris', '0102030406', 'contactB@gmail.com', 15),
(10000003, 'Shine & Silk', '30 boulevard Saint-Germain, 75005 Paris', '0102030407', 'contactC@gmail.com', 20);

--insertion dans la table client

INSERT INTO client (id_client, nom, prenom, telephone, adresse, email,date_inscription, points,id_carte) 
VALUES 
('12345678', 'Durand', 'Alice', '0601020304', '1 rue de la République, 75011 Paris', 'alice.durand@email.com', '2024-01-01', 20,'12000011'),
('12345679', 'Lemoine', 'Marc', '0601020305', '2 rue des Lilas, 75012 Paris', 'marc.lemoine@email.com',  '2024-02-02', 0,'12000012'),
('12345680', 'Martin', 'Claire', '0601020306', '3 avenue des Champs, 75013 Paris', 'claire.martin@email.com','2024-02-03' ,120,'12000013'),
('12345681', 'Lemoine', 'Julie', '0601020307', '4 rue des Amandiers, 75014 Paris', 'julie.lemoine@email.com','2024-02-10' ,50,'12000014'),
('12345682', 'Dumas', 'Sophie', '0601020308', '5 avenue Victor Hugo, 75015 Paris', 'sophie.dumas@email.com','2024-05-02',70,'12000015'),
('12345683', 'Benoit', 'Thierry', '0601020301', '6 rue du Faubourg, 75016 Paris', 'thierry.benoit@email.com','2024-06-02',0,'12000016'),
('12345684', 'Dehouche', 'Lisa', '0601020309', 'Saint martin , 950000 Cergy', 'lisouselisa@gmail.com','2024-11-30',0,'12000017');

-- Insertion dans la table fournisseur
INSERT INTO fournisseur (id_fournisseur, nom, adresse, email, telephone)
VALUES 
(1000001, 'BeautyPro', '12 rue des Produits de Beauté, 75009 Paris', 'contact@beautypro.com', '0102030400'),
(1000002, 'GlamourTech', '18 avenue des Technologies, 75012 Paris', 'contact@glamourtech.com', '0102030401'),
(1000003, 'HairEssentials', '22 boulevard Coiffure, 75013 Paris', 'contact@hairessentials.com', '0102030402');

-- Insertion des données dans la table MSA

INSERT INTO msa (id_msa, date_mise_service, marque, type_connexion, id_magasin)
VALUES
(11004001, '2024-01-10', 'MarqueX', 'cable', 10000001),
(11004002, '2024-02-20', 'MarqueY', 'sans fil', 10000002),
(11004003, '2024-02-20', 'MarqueY', 'sans fil', 10000003);

--insertion dans la table promotion
INSERT INTO promotion (id_promotion, date_debut, date_fin, pourcentage, description) 
VALUES 
(10000011, '2024-11-01', '2024-12-31', 20, 'promotion pour la fin de saison');


--insertion dans la table article
    --produit
INSERT INTO article (reference_produit, prix, nom, description, id_promotion, image_path, point) 
VALUES 
('12345684', 15.99, 'Shampoing Nourrissant', 'Shampoing hydratant pour cheveux secs', 10000011, 'images/shampoing1.jpg', 2),
('12345685', 12.99, 'Masque Capillaire', 'Masque réparateur pour cheveux abîmés', 10000011, 'images/masque1.jpg', 2),
('12345686', 19.99, 'Sérum Anti-Frisottis', 'Sérum lissant et protecteur', NULL, 'images/serum1.jpg', 3),
('12345687', 9.99, 'Après-Shampoing Revitalisant', 'Après-shampoing pour cheveux colorés', NULL, 'images/apres_shampoing1.jpg', 1),
('12345688', 29.99, 'Huile de réparatrice', 'Huile réparatrice pour cheveux secs', NULL, 'images/huile1.jpg', 5);

    --materiel
INSERT INTO article (reference_produit, prix, nom, description, id_promotion, image_path, point) 
VALUES 
('12345689', 49.99, 'Sèche-Cheveux Pro', 'Sèche-cheveux haute performance', 10000011, 'images/seche_cheveux1.jpg', 25),
('12345690', 39.99, 'Fer à Lisser', 'Fer à lisser céramique', NULL, 'images/fer_lisser1.jpg', 30),
('12345691', 59.99, 'Boucleur Automatique', 'Boucleur avec technologie ionique', NULL, 'images/boucleur1.jpg', 35),
('12345692', 89.99, 'Tondeuse Professionnelle', 'Tondeuse pour coupe précise', NULL, 'images/tondeuse1.jpg', 40),
('12345693', 99.99, 'Brosse Chauffante', 'Brosse chauffante 3 en 1', NULL, 'images/brosse1.jpg', 45);

--insertion dans la table produit_coiffure
INSERT INTO produit_coiffure (id_prod_coif, contenance, composition, usage, type_produit)
VALUES
('12345684', 250, 'Aloe Vera, Huile essentiel', 'Appliquer sur cheveux mouillés', 'Shampoing'),
('12345685', 500, 'Keratin, Huile de jojoba', 'Appliquer sur cheveux après lavage', 'Masque'),
('12345686', 100, 'Silicone, Acide hyaluronique', 'Appliquer sur cheveux secs', 'Sérum'),
('12345687', 300, 'Huiles naturelles', 'Après shampoing, laisser agir', 'Après-shampoing'),
('12345688', 100, 'Huile essentiel, Vitamine E', 'Appliquer avant le coucher', 'Huile');

--insertion dans la table materiel_coiffure
INSERT INTO appareil_coiffure (id_mat_coif, puissance, temperature_max, type_appareil, technologie)
VALUES
('12345689', 2000, 220, 'Sèche-cheveux', 'Ionique'),
('12345690', 35, 230, 'Fer à lisser', 'Céramique'),
('12345691', 50, 200, 'Boucleur', 'Ionique'),
('12345692', 45, 180, 'Tondeuse', 'Ionique'),
('12345693', 40, 190, 'Brosse chauffante', 'Céramique');




-- Insertion dans la table fournit
INSERT INTO fournit (id_magasin, id_article, id_fournisseur, quantite_fournie, date)
VALUES 
(10000001, '12345684', 1000001, 50, '2024-11-01'),
(10000001, '12345685', 1000001, 60, '2024-11-01'),
(10000002, '12345686', 1000002, 40, '2024-11-05'),
(10000002, '12345687', 1000002, 70, '2024-11-05'),
(10000003, '12345689', 1000003, 30, '2024-11-10'),
(10000003, '12345690', 1000003, 25, '2024-11-10');





