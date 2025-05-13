-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql204.infinityfree.com
-- Generation Time: May 13, 2025 at 05:16 PM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_38910137_bd-esouk-2.sql`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id_cart` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id_cart`, `user_id`, `created_at`) VALUES
(2, 1, '2025-04-24 03:39:59');

-- --------------------------------------------------------

--
-- Table structure for table `cart_product`
--

CREATE TABLE `cart_product` (
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_product`
--

INSERT INTO `cart_product` (`cart_id`, `product_id`, `quantity`, `unit_price`) VALUES
(2, 21, 1, '14.00');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id_category` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id_category`, `name`, `description`, `image`) VALUES
(1, 'Tapis et Kilim', 'Ajoutez chaleur, caractère et authenticité à vos espaces avec notre collection de tapis et kilims faits main, issus du riche patrimoine artisanal tunisien. Chaque pièce est soigneusement tissée par des artisanes utilisant des techniques ancestrales et des matières naturelles, comme la laine ou le coton.\r\n\r\nQu’ils soient traditionnels ou revisités dans un style contemporain, nos tapis et kilims apportent une touche d’élégance artisanale à votre intérieur : salon, chambre, entrée ou bureau.\r\n\r\n🔸 Motifs berbères, couleurs naturelles, textures authentiques – chaque tapis raconte une histoire, entre culture, savoir-faire et identité.\r\n\r\nUn tapis Esouk, c’est plus qu’un simple objet décoratif : c’est une œuvre vivante, qui traverse le temps avec style.', '6809a8d9ee691.jpg'),
(2, 'Articles ménagers', 'Découvrez notre sélection unique d’articles ménagers faits main, alliant savoir-faire traditionnel et utilité moderne. Chaque pièce est conçue avec soin par des artisans tunisiens passionnés, en utilisant des matériaux durables et naturels.\r\n\r\nQue ce soit pour la cuisine, la table, le rangement ou la décoration, nos produits apportent une touche chaleureuse et authentique à votre intérieur. Transformez vos gestes du quotidien en moments de plaisir avec des objets à la fois pratiques, élégants et empreints de culture.\r\n\r\n🧺 Panier en osier, plateau en bois d’olivier, poterie décorative ou ustensiles artisanaux – chaque article raconte une histoire.', '6809a8cae1228.jpg'),
(3, 'Accessories', '👜 Accessoires Artisanaux\r\nAffirmez votre style avec notre collection d’accessoires artisanaux uniques, faits main par des créateurs tunisiens talentueux. Chaque pièce mêle authenticité, originalité et finesse, pour accompagner votre quotidien avec élégance.\r\n\r\nDécouvrez des bijoux faits main, des sacs en cuir ou en fibres naturelles, des porte-clés, ceintures, étuis et petits objets de charme – pensés pour sublimer vos tenues ou faire de jolis cadeaux.\r\n\r\n🎨 Entre design traditionnel et touches modernes, nos accessoires sont plus que de simples objets : ce sont des symboles de culture et d’art de vivre.\r\n\r\nFaites le choix d’un style responsable et authentique, avec des créations qui ont une âme.', '6809a7eea6b84.jpg'),
(6, 'Céramiques artisanales', 'E-souk vous propose un large choix de céramiques authentiques. Partout en Tunisie, les différentes céramiques sont fabriquées et peintes à la main. Nabeul est non seulement considérée comme une capitale touristique, mais aussi comme la capitale tunisienne de la céramique. E-souk Tounsio propose des céramiques à des prix raisonnables.\r\n\r\nLes céramiques originales de Nabeul vous proposent une variété d\'ustensiles de cuisine tels que de la vaisselle, des planches, des bols et des tajines. Chaque pièce de nos différentes collections de céramique est fabriquée avec soin et finesse pour nos clients.', '680c3ebc88430.jpg'),
(7, 'Poterie', 'Plongez dans l’univers riche et coloré de la poterie artisanale tunisienne, où chaque pièce est le fruit d’un héritage transmis de génération en génération. Modelées à la main avec passion, nos poteries allient esthétique traditionnelle et utilité moderne, pour embellir votre intérieur ou votre table avec caractère.\r\n\r\nDécouvrez une sélection de vases, plats, bols, tasses et objets décoratifs, aux motifs inspirés de la culture méditerranéenne. Nos artisans utilisent des techniques ancestrales et des matières naturelles, donnant naissance à des pièces uniques, durables et respectueuses de l’environnement.\r\n\r\n✨ Chaque poterie est une œuvre d’art qui raconte une histoire – celle d’un artisan, d’un village, d’une tradition.', '68220edc3d85a.png');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_order`, `user_id`, `order_date`, `total_price`, `status`) VALUES
(1, 4, '2025-04-20 02:01:04', '10.00', 'Processing'),
(2, 1, '2025-04-28 22:22:51', '12.00', 'Pending'),
(3, 1, '2025-05-05 14:57:53', '22.00', 'Pending'),
(4, 3, '2025-05-05 15:03:30', '37.00', 'Cancelled'),
(5, 1, '2025-05-12 06:53:57', '21.00', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_product`
--

CREATE TABLE `order_product` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_product`
--

INSERT INTO `order_product` (`order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(5, 21, 1, '14.00');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id_product` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id_product`, `title`, `description`, `price`, `stock`, `image`, `is_best_seller`, `created_at`, `category_id`) VALUES
(17, 'Tapis Halfa de couleur Verte', 'Le smar fait partie de l\'artisanat tunisien, il est fabriqué à la main par des artisans tunisiens Le smar est utilisé dans le nord de la Tunisie, précisément au Cap Bon.\r\nDétails de couleur verte pour un look rafraîchissant\r\n\r\nFabriqué éthiquement en Tunisie\r\n\r\nSmar, 100 % fait à la main par un artisan tunisien\r\n\r\nDimension : 45*120 cm', '160.00', 10, '1747055123_image_2025-05-12_140512761.png', 1, '2025-05-12 06:05:23', 1),
(18, 'Kilim blanc avec lignes noires', 'Tapis à base blanche à motifs noirs berbères amazighs. Ce tapis de la région fait partie de l\'artisanat tunisien.\r\n\r\nInfo produit +\r\n\r\n Fait à la main éthique en Tunisie\r\nDimensions : 140*200 cm/56*79 pouces \r\n                                   - haut : 0,2 cm - 0,07 pouce \r\nbalise +\r\n\r\nBerbère, chambre, Décoration, fait main, Kelim, laine, Margoum, salon, Tapis.', '199.00', 10, '1747055356_image_2025-05-12_140725409.png', 0, '2025-05-12 06:09:16', 1),
(19, 'Tapis fait main Géométrique de couleur bleue et blanche', 'Tapis kilim en laine et jute avec motif traditionnel dans des tons bleus, blancs et noirs avec bords frangés Ce tapis de zone fait partie de l\'artisanat tunisien. Il est fabriqué en laine et peut être mis dans n\'importe quel coin de la maison. \r\n\r\nInfo produit +\r\n\r\n– Fait à la main éthique en Tunisie\r\n\r\n– Dimensions : 189 cm * 125 cm / 75 pouces * 50 pouces \r\n\r\n– haut : 0,2 cm – 0,07 pouce\r\n\r\nbalise +\r\n\r\nTapis kilim, Tapis kilim faits à la main, Tapis kilim traditionnel, tapis faits à la main, Tapis de sol Kilim, Kilim authentique, Tapis kilim vintage, Kilim tissé à la main, Tapis kilim bohème, kilim tunisien, Tapis de la région de Kilim, Tapis kilim ethnique, tapis kilim rustique, tapis kilim tribal.', '187.00', 15, '1747055620_image_2025-05-12_141207973.png', 0, '2025-05-12 06:13:40', 1),
(20, 'Tapis à motif berbère multicolore fait main sur blanc', 'Ces tapis mettent en valeur le riche patrimoine culturel de la Tunisie et l\'artisanat séculaire qui s\'est transmis de génération en génération.\r\n\r\nInfo produit +\r\n\r\n– Fait à la main éthique en Tunisie \r\n\r\n– Dimensions : 60 * 120 cm\r\n\r\n\r\nÉtiquette +\r\n\r\ntapis à vendre, tapis à vendre, tapis à vendre, tapis de la région, tapis d\'extérieur, tapis de boutique, tapis de salon, tapis de dépôt de maison, tapis directs, grands tapis, tapis près de moi, tapis bon marché, kilim à vendre ', '38.00', 16, '1747055857_image_2025-05-12_141551984.png', 0, '2025-05-12 06:17:37', 1),
(21, 'Bouteille d\'huile d\'olive ', 'Elevate your kitchen with our Handmade Tunisian Ceramic Olive Oil Bottle. Crafted by skilled artisans from sun-dried clay, this elegant cruet features a smooth glaze and a tapered spout for precise, drip-free pouring. Its sturdy design preserves the freshness and flavor of your finest olive oil while adding a touch of rustic Mediterranean charm to your table.\r\n\r\nProduct info + \r\n\r\n Ethically handmade in Nabeul, Tunisia\r\nCeramic art\r\nDifferent sizes ( 600 ml and 200 ml )\r\nMinimum Quantity: 20\r\ntag + \r\n\r\nHandmadeCeramics, TunisianPottery, ArtisanalCraft, ClayKitchenware, MediterraneanStyle, EcoFriendlyHome, DripFreePour, RusticChic, OliveOilLovers, TabletopStyle', '14.00', 19, '1747056227_image_2025-05-12_142309675.png', 1, '2025-05-12 06:23:47', 6),
(22, 'Bouteille d\'huile d\'olive en céramique', 'Elevate your kitchen with our Handmade Tunisian Ceramic Olive Oil Bottle. Crafted by skilled artisans from sun-dried clay, this elegant cruet features a smooth glaze and a tapered spout for precise, drip-free pouring. Its sturdy design preserves the freshness and flavor of your finest olive oil while adding a touch of rustic Mediterranean charm to your table.\r\n\r\nProduct info + \r\n\r\n Ethically handmade in Nabeul, Tunisia\r\nCeramic art\r\nDifferent sizes ( 600 ml and 200 ml )\r\ntag + \r\n\r\nHandmadeCeramics, TunisianPottery, ArtisanalCraft, ClayKitchenware, MediterraneanStyle, EcoFriendlyHome, DripFreePour, RusticChic, OliveOilLovers, TabletopStyle', '11.00', 16, '1747056435_image_2025-05-12_142707029.png', 0, '2025-05-12 06:27:15', 6),
(23, 'Fer forgé Vaisselle Ensembles céramiques bleues et blanches', 'Céramique, Poterie peinte à la main de Tunisie Ensemble de 19 pièces de céramique de table pour vaisselle Ensemble pour 6 personnes. Fer forgé Bleu Mélangé avec Blanc, il a l\'air incroyable ! Ce Bol de peinture à la main assure une nourriture sûre, avec une céramique tunisienne adaptée à toutes les occasions. 6 Grandes assiettes ; 6 assiettes profondes ; 6 bols ; 1 soupière\r\nInfo produit +\r\n\r\n- Éthiquement fait à la main en Tunisie\r\n\r\n- Céramique\r\n\r\n- Bleu mélangé avec Blanc\r\n\r\n- Lave-vaisselle et micro-ondes conviviaux.', '260.00', 16, '1747056746_image_2025-05-12_143045259.png', 0, '2025-05-12 06:32:26', 6),
(25, 'Fer Forgé Tajine', 'Tagine en céramique peint à la main de Tunisie, il est utilisé pour cuisiner, servir et garder les aliments au chaud pendant plus de 30 minutes. Ce produit en céramique fait main est un design unique qui peut ajouter une touche d\'élégance à votre expérience culinaire. \r\n\r\nInfo produit +\r\n\r\n– Éthiquement fait à la main en Tunisie.\r\n\r\n- Diamètre : 25, 30, 35, 40 cm (9,84 ; 11,81 ; 13,77 ; 15,74 \") / Profondeur sans couvercle : 5 cm (1,96\") \r\n\r\nbalise +\r\n\r\nTagine en céramique fait main, Pot de tajine en céramique, cuisine de tajine artisanale, Tagine en céramique tunisienne, Pot de tajine traditionnel, Plat de tajine fabriqué à la main, Tagine marocain en céramique, tagine en céramique rustique, Pot de tajine décoratif, Tagine de cuisine en céramique, tagine de poterie artisanale, Cuisine de tajine faite à la main, Authentique plat de tajine, Tagine de poterie fait à la main, tagine en céramique pour la cuisine.', '45.00', 16, '1747058686_image_2025-05-12_150100970.png', 0, '2025-05-12 07:04:45', 6),
(26, 'Foulards Hayek avec broderie', 'Description complète +\r\n\r\nFoulards en lin / Hayek, fait main en Tunisie Amazigh Motif bijoux\r\n\r\n– Artisan : l\'artisane | Hager \r\n\r\nInfo produit +\r\n\r\n– Éthiquement fait à la main en Tunisie.\r\n\r\n– Lin \r\n\r\n– Disponible en différentes couleurs.\r\n\r\n– Dimension : 2 m/80 cm | 78,7 X 35,4 pouces', '80.00', 60, '1747059040_image_2025-05-12_150850011.png', 0, '2025-05-12 07:10:40', 3),
(27, 'Foulards en lin avec broderie', 'Description complète\r\n\r\nFoulards en lin, faits à la main en Tunisie\r\n\r\n– Artisan : Katy Créations \r\n\r\nInfo produit +\r\n\r\n– Éthiquement fait à la main en Tunisie.\r\n\r\n– Lin \r\n\r\n– Disponible en différentes couleurs.\r\n\r\n– Dimension : 50 cm (19,68\") / 80 cm (31,49\")', '96.00', 50, '1747059155_image_2025-05-12_151154940.png', 1, '2025-05-12 07:12:35', 3),
(28, 'Sac et set de 3 serviettes roses', 'Description complète +\r\n\r\n3 serviettes roses L/M/S\r\n\r\nsac et sac à main utilisation quotidienne pour PC ou autre\r\n\r\nInfo produit\r\n\r\n– Éthiquement fait à la main à Joumine.\r\n\r\n– Crochet \r\n\r\n– artisan broderie\r\n\r\n— pompon \r\n\r\n- Dimensions : 45 cm x 45 cm - 25 cm/17,71 po x 17,71 po - 9,84 po', '120.00', 30, '1747059330_image_2025-05-12_151402719.png', 1, '2025-05-12 07:15:30', 3),
(29, 'Collier, Micro Mosaïque', 'Info produit +\r\n\r\n– Éthiquement fait à la main en Tunisie.\r\n\r\n– Couleurs : collier noir\r\n\r\n– Diamètre : 1,57 pou\r\n\r\n– Cuir : diamètre 0,08 pouce\r\n\r\n– Léger et bien fait.', '12.00', 35, '1747060014_image_2025-05-12_152532485.png', 0, '2025-05-12 07:26:54', 3),
(30, 'Abat-jour Margoum', 'Description complète +\r\n\r\nAbat-jour XL d\'angle décoré avec Margoum Fabriqués en Tunisie, nos abat-jour tunisiens faits à la main ajoutent un look cosy et unique à l\'espace.\r\n\r\nCe produit fait partie de l\'artisanat tunisien.\r\n\r\nInfo produit +\r\n\r\n— Éthiquement fait à la main en Tunisie.\r\n\r\n– Margoum, Bois de palme et Fer\r\n\r\n– La teinte mesure : 55*23 cm/21,65\"*\r\n\r\n– Bois de palmier : 150 * 53 cm/59,', '150.00', 7, '1747060793_image_2025-05-12_153013469.png', 1, '2025-05-12 07:39:53', 2),
(31, 'Lanier a linge avec petit sac', 'Boîte de rangement distinctive aux teintes rouges fabriquée à partir de fibres naturelles de palmier. Unité praticité avec une touche de couleur et de texture organique, offrant une solution de stockage unique et fonctionnelle.\r\n\r\nInfo produit +\r\n\r\n– Éthiquement fait à la main en Tunisie.\r\n\r\n– Taille : diamètre : 38 cm/14,96\'\'\r\n\r\n           Hauteur: 52 cm/20,47\'\'\r\n\r\n\r\nÉtiquette + \r\n\r\nFibre végétale naturelle, Produits en fibres végétales, Produits à base de fibres naturelles, Textiles respectueux de l\'environnement, Matériaux durables, Textiles végétaux, Produits biodégradables Textiles végétaliens, Mode écologique, Articles ménagers en fibres naturelles, Matériaux de construction verts, Emballages durables, Produits en fibres biologiques, Matériaux respectueux de l\'environnement, Matériaux biocomposit Écotextiles,  Produits en chanvre, Produits en fibre de coco, produits en fibre de bambou, produits en fibre de lin', '96.00', 25, '1747061196_image_2025-05-12_154149036.png', 1, '2025-05-12 07:46:36', 2),
(32, 'Table d\'échecs', 'Améliorez votre expérience de jeu avec cet échiquier en bois d\'olivier : fabriqué à la main par des artisans qualifiés en Tunisie. Fabriqué à partir de bois d\'olivier d\'origine durable, cet échiquier présente un magnifique motif de grain naturel, rendant chaque pièce vraiment unique. Ses tons riches et sa finition lisse offrent une surface élégante pour vos matchs d\'échecs, tandis que sa durabilité garantit qu\'il résistera à l\'épreuve du temps.\r\n\r\nInfo produit +\r\n\r\nFabriqué à la main en Tunisie à partir de bois d\'olivier d\'origine durable.\r\n\r\nBeau motif de grain naturel – chaque planche est unique en son genre.\r\n\r\nDurable, écologique et durable\r\n\r\nDimensions : -W: 40 cm-15,3” L : 40 cm-15,3”\r\n\r\nÉtiquette + \r\n\r\nOlive Wood Games, jeu d\'échecs en bois d\'olivier, échiquier en bois d\'olivier, échiquier naturel en bois d\'olivier, jeux d\'échiquiers rustiques, échiquier en bois, jeu d\'échiquier, échiquier Tunisie, échiquier tunisien.', '210.00', 15, '1747061487_image_2025-05-12_154934968.png', 0, '2025-05-12 07:51:27', 2),
(33, 'Abat-jour', 'Lampe à suspension en fibre de palme Sarah\r\nÉtiquette + \r\n\r\nFibre végétale naturelle, Produits en fibres végétales, Produits à base de fibres naturelles, Textiles respectueux de l\'environnement, Matériaux durables, Textiles végétaux, Produits biodégradables Textiles végétaliens, Mode écologique, Articles ménagers en fibres naturelles, Matériaux de construction verts, Emballages durables, Produits en fibres biologiques, Matériaux respectueux de l\'environnement, Matériaux biocomposit Écotextiles,  Produits en chanvre, Produits en fibre de coco, produits en fibre de bambou, produits en fibre de lin', '30.00', 35, '1747062025_image_2025-05-12_155823764.png', 0, '2025-05-12 08:00:25', 2),
(34, 'Cuisinière Couscous traditionnelle', 'Description complète +\r\n\r\nCuisinière couscous traditionnelle Sejnan en argile. Il est fait à la main et sa couleur est la couleur naturelle d\'argile cuite rouge. C\'est un merveilleux outil pour y cuisiner du couscous sain. Il fait partie de l\'artisanat tunisien.\r\n\r\nInfo produit +\r\n\r\n– Éthiquement fait à la main en Tunisie.\r\n\r\n– Cuisinière à couscous disponible en différentes tailles : 30 cm/11,811 po et 40 cm/15,74 po.\r\n\r\n– Artisanat tunisien\r\n\r\n– Poterie faite à la main.\r\n\r\n– Fabriqué par des femmes artisans\r\n\r\n– Produits uniques', '320.00', 16, '1747062677_image_2025-05-12_160937439.png', 0, '2025-05-12 08:11:17', 7);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id_review` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `name`, `email`, `password`, `role`, `address`, `phone`, `image`, `created_at`) VALUES
(1, 'admin', 'a@admin.es', '$2y$10$rPot/uqRwapGdwvWAtakreXajS7dyqZGc1fxofC5/44645y.9HtNq', 'admin', '', '', 'fedi-riahi.jpg', '2025-04-19 22:40:16'),
(3, 'test', 'aa@a.a', '$2y$10$wqrS7T9Ng7/.pZvWzEWjB.Sq4CGhCgnj69/XfVRQWBeUp4fyOcGZe', 'customer', 'a', '0', '', '2025-04-19 23:39:16'),
(4, 'aziz', 'aziz@a.a', '$2y$10$cVxNlwRW5p/n2PU9NMeMdO0PM9YGU5jQlQjBY7r9EZtr6.c4eLej6', 'customer', 'azazae', '99999', '', '2025-04-20 00:18:46'),
(5, 'Jrad', 'azizjrad9@gmail.com', '$2y$10$eBM5VKnUaCun67wn264PHekVZ54bv4ztH44iagkmGnBl/jZxd7TC2', 'customer', 'Nabeul', '95650081', '', '2025-05-05 15:20:20'),
(6, 'rarouri', 'rania@lo3bahub.com', '$2y$10$LavzKTODce5EhNAAm/GzT.o7itzxiqjFBb/mNnEkzo46vAOwibVba', 'customer', 'aaaaaaa', '28243070', '', '2025-05-06 16:07:31'),
(7, 'yosr jabloun ', 'yosr.jabloun@gmail.com', '$2y$10$eDjs04jEnZCyfwzO7wi3mOecsBh0Ymav6aYMKUV7JCP6O6aDkoTJC', 'customer', '.....', '29469140', '', '2025-05-06 16:42:30');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id_wishlist` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id_cart`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_product`
--
ALTER TABLE `cart_product`
  ADD PRIMARY KEY (`cart_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_product`
--
ALTER TABLE `order_product`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id_product`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_wishlist`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id_cart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id_wishlist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `cart_product`
--
ALTER TABLE `cart_product`
  ADD CONSTRAINT `cart_product_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id_cart`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `order_product`
--
ALTER TABLE `order_product`
  ADD CONSTRAINT `order_product_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`) ON DELETE SET NULL;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id_product`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
