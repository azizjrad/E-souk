<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';

// Page metadata
$page_title = "Accueil - E-Souk Tounsi";
$page_description = "Découvrez l'artisanat tunisien de qualité";

// Initialize wishlist if not set
$_SESSION['wishlist'] = $_SESSION['wishlist'] ?? [];

// Get best-selling products
$productsQuery = $db->query("SELECT * FROM product WHERE is_best_seller = 1 LIMIT 8");
$productsArray = $productsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body>
    <?php include '../templates/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section position-relative mb-5">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center" style="min-height: 80vh">
                <div class="col-lg-6 text-white position-relative z-2">
                    <h1 class="hero-title mb-4">Découvrez le Meilleur<br>de l'Artisanat Tunisien</h1>
                    <p class="hero-subtitle mb-4">Des pièces uniques faites à la main par nos artisans locaux qui perpétuent un héritage ancestral.</p>
                    <div class="hero-buttons">
                        <a href="product.php" class="btn btn-primary btn-lg px-4 shadow-sm">Explorer les produits</a>
                        <a href="vision.php" class="btn btn-outline-light btn-lg px-4 ms-3">Nos artisans</a>
                    </div>
                </div>
                <div class="col-lg-6 position-relative z-2 d-none d-lg-block">
                    <div class="hero-featured-product">
                        <img src="../assets/images/featured-product.png" alt="Produit artisanal mis en avant" class="img-fluid rounded-lg shadow-lg">
                        <div class="hero-badge">
                            <span>Nouveauté</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,181.3C960,213,1056,224,1152,208C1248,192,1344,149,1392,128L1440,107L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Artisan Showcase -->
    <section class="artisan-showcase">
        <div class="container-fluid px-0">
            <div class="row g-0">
                <div class="col-md-6">
                    <div class="artisan-image-container">
                        <img src="../assets/images/historic/2.jpg" alt="artisan" class="img-fluid">
                        <div class="artisan-overlay">
                            <div class="artisan-info">
                                <h3>L'Art de l'Artisanat</h3>
                                <p>Des techniques transmises de génération en génération</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="artisan-text-container">
                        <div class="artisan-text-content">
                            <span class="artisan-badge">Notre Histoire</span>
                            <h2>Une Tradition Ancestrale</h2>
                            <p>L'artisanat tunisien est le reflet d'un patrimoine culturel riche et diversifié. Chaque pièce raconte une histoire et témoigne d'un savoir-faire transmis à travers les âges.</p>
                            <p>Nos artisans perpétuent ces techniques ancestrales tout en apportant leur touche de créativité pour créer des pièces uniques qui allient tradition et modernité.</p>
                            <a href="vision.php" class="btn btn-outline-primary">Découvrir nos artisans</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="container mb-5 py-4">
        <h2 class="text-center mb-5">Nos Catégories</h2>
        <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">
        <br>
        <div class="category-container">
            <?php
            $categories = [
                ['id' => 1, 'name' => 'Accessories', 'image' => 'Accessories.jpg', 'desc' => 'Découvrez notre collection d\'accessoires artisanaux.'],
                ['id' => 2, 'name' => 'Rugs & Kilim', 'image' => 'klim.jpg', 'desc' => 'Explorez nos tapis et kilims traditionnels faits à la main.'],
                ['id' => 3, 'name' => 'Handcrafted Ceramics', 'image' => 'Handcrafted-ceramic.jpg', 'desc' => 'Découvrez notre collection de céramiques artisanales tunisiennes faites à la main.']
            ];

            foreach ($categories as $index => $category):
                $position = $index % 2 == 0 ? 'category-start' : 'category-end';
            ?>
                <div class="category-card <?= $position ?>">
                    <div class="category-image-container">
                        <img src="../assets/images/category/<?= $category['image'] ?>" alt="<?= $category['name'] ?>">
                        <div class="category-overlay">
                            <h5><?= $category['name'] ?></h5>
                            <p><?= $category['desc'] ?></p>
                            <a href="category-details.php?id=<?= $category['id'] ?>" class="category-btn">Découvrir</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <p class="lead">Découvrez toutes nos catégories d'artisanat tunisien traditionnel</p>
            <a href="categories.php" class="btn categories-btn px-4 py-2">Voir toutes les catégories</a>
        </div>
    </section>

    <!-- Products Carousel -->
    <section id="products" class="container-fluid mb-5 py-5 bg-light">
        <h2 class="text-center mb-5">Nos Best-Sellers</h2>
        <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">
        
        <div class="product-carousel-container">
            <button class="carousel-arrow prev-arrow"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-arrow next-arrow"><i class="fas fa-chevron-right"></i></button>
            
            <div class="product-carousel">
                <?php foreach($productsArray as $product): 
                    $inWishlist = in_array($product['id_product'], $_SESSION['wishlist']);
                ?>
                    <div class="product-card" data-product-id="<?= $product['id_product'] ?>">
                        <div class="wishlist-icon" data-product-id="<?= $product['id_product'] ?>">
                            <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                        </div>
                        <div class="stock-badge <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                            <?= $product['stock'] > 0 ? 'En stock' : 'Rupture de stock' ?>
                        </div>

                        <div class="product-image">
                            <a href="product-detail.php?id=<?= $product['id_product'] ?>">
                                <img src="../../root_uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                    alt="<?= htmlspecialchars($product['title']) ?>">
                            </a>
                        </div>

                        <div class="product-details">
                            <div class="d-flex justify-content-between">
                                <h5><?= htmlspecialchars($product['title']) ?></h5>
                                <span class="price"><?= number_format($product['price'], 2) ?> DT</span>
                            </div>
                            <p class="product-description"><?= substr(htmlspecialchars($product['description']), 0, 80) ?>...</p>
                            <div class="product-btn-container">
                                <button class="add-to-cart-btn" data-product-id="<?= $product['id_product'] ?>">
                                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="toast-container" class="position-fixed bottom-0 end-0 p-3"></div>
    </section>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/newsletter.php'; ?>
    <?php include '../templates/footer.php'; ?>
    
   
    <script>
        // Make ROOT_URL available to JavaScript
        const ROOT_URL = '<?php echo ROOT_URL; ?>';
    </script>
    <script src="<?php echo ASSETS_URL; ?>js/cart.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/wishlist.js"></script>
</body>
</html>
