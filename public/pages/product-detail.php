<?php
session_start();

require_once __DIR__ . '/../../config/init.php';
require_once ROOT_PATH . '/core/connection.php';
$db = Database::getInstance();

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID, redirect to products page
if ($product_id <= 0) {
    header('Location: product.php');
    exit;
}

// Fetch product data
$stmt = $db->prepare("SELECT p.*, c.name as category_name 
                     FROM product p 
                     LEFT JOIN category c ON p.category_id = c.id_category 
                     WHERE p.id_product = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// If product not found, redirect
if (!$product) {
    header('Location: product.php');
    exit;
}

// Set page title and description
$page_title = htmlspecialchars($product['title']) . " - E-Souk Tounsi";
$page_description = htmlspecialchars(substr($product['description'], 0, 155));

// Add to cart functionality (can be expanded later)
$message = '';
if (isset($_POST['add_to_cart'])) {
    // Simple cart functionality - can be expanded based on your cart implementation
    $message = '<div class="alert alert-success">Product added to cart successfully!</div>';
    // Here you would add the actual cart adding logic
}
$title = htmlspecialchars($product['title']);

$breadcrumbs = [
    'Accueil' => 'index.php',
    'Produits' => 'product.php',
    $product['title'] => '#'
];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/product-detail.css">
    <link rel="stylesheet" href="../assets/css/components/hero.css">
</head>
<body>
    <?php include '../templates/navbar.php'; ?>
    <?php include '../templates/hero.php'; ?>

    <!-- Product Detail Section -->
    <section class="product-detail-section py-4">
        <div class="container">
            <?php if (!empty($message)): ?>
                <?= $message ?>
            <?php endif; ?>
            
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-5 mb-4">
                    <div class="product-image-container">
                        <img src="../../root_uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['title']) ?>">
                    </div>
                </div>
                
                <!-- Product Details -->
                <div class="col-md-7">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="product-title mb-0"><?= htmlspecialchars($product['title']) ?></h1>
                        <div class="product-price">
                            <span class="price"><?= number_format($product['price'], 2) ?> DT</span>
                        </div>
                    </div>
                    
                    <?php if ($product['category_id']): ?>
                        <div class="mb-3">
                            <span class="badge bg-primary"><?= htmlspecialchars($product['category_name']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-sku">Ref: PROD-<?= $product['id_product'] ?></div>
                    
                    <div class="stock-badge <?= $product['stock'] > 0 ? '' : 'out-of-stock' ?>">
                        <?= $product['stock'] > 0 ? 'In stock' : 'Out of stock' ?>
                    </div>
                    
                    <div class="product-description mb-3">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>
                    
                    <form method="post" action="">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="input-group quantity-input-group">
                                <button type="button" class="btn btn-sm" id="decrease-qty">-</button>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                <button type="button" class="btn btn-sm" id="increase-qty">+</button>
                            </div>
                        </div>

                        <div class="button-container">
                            <button type="submit" name="add_to_cart" class="btn btn-primary add-to-cart-btn" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart"></i> Ajouter
                            </button>
                            
                            <button type="button" class="btn btn-outline-primary add-to-wishlist-btn">
                                <i class="far fa-heart"></i> Favoris
                            </button>
                        </div>
                    </form>
                    
                    <!-- Product Information Accordions -->
                    <div class="mt-4">
                        <div class="product-info-section border-bottom py-2">
                            <h5 data-toggle="collapse" data-target="#productInfo">
                                Information de produit <i class="fas fa-chevron-down toggle-icon"></i>
                            </h5>
                            <div class="product-info-content" id="productInfo">
                                <ul>
                                    <li><strong>Material:</strong> Handcrafted with natural clay</li>
                                    <li><strong>Origin:</strong> Made in Tunisia</li>
                                    <?php if ($product['created_at']): ?>
                                        <li><strong>Added:</strong> <?= date('F j, Y', strtotime($product['created_at'])) ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>                                
                    </div>
                </div>
            </div>
        </div>
    </section>
            
    <!-- Related Products Section -->
    <section class="related-products">
        <div class="container-fluid">
            <h3 class="text-center mb-3">Vous pourriez être intéressé</h3>
            <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">
            
            <div class="product-carousel-container">
                <button class="carousel-arrow prev-arrow"><i class="fas fa-chevron-left"></i></button>
                <button class="carousel-arrow next-arrow"><i class="fas fa-chevron-right"></i></button>
                
                <div class="product-carousel">
                    <?php
                    // Get related products (same category or featured products)
                    $relatedQuery = $db->prepare("SELECT *, CASE WHEN is_best_seller = 1 THEN 'Best Seller' ELSE NULL END AS badge 
                                                FROM product 
                                                WHERE category_id = ? AND id_product != ? 
                                                LIMIT 4");
                    $relatedQuery->execute([$product['category_id'], $product_id]);
                    $relatedProducts = $relatedQuery->fetchAll(PDO::FETCH_ASSOC);
                    
                    // If not enough related products, get some best sellers
                    if (count($relatedProducts) < 4) {
                        $limit = 4 - count($relatedProducts);
                        $bestSellersQuery = $db->prepare("SELECT *, 'Best Seller' AS badge 
                                                        FROM product 
                                                        WHERE is_best_seller = 1 AND id_product != ? 
                                                        LIMIT ?");
                        $bestSellersQuery->bindParam(1, $product_id, PDO::PARAM_INT);
                        $bestSellersQuery->bindParam(2, $limit, PDO::PARAM_INT);
                        $bestSellersQuery->execute();
                        $bestSellers = $bestSellersQuery->fetchAll(PDO::FETCH_ASSOC);
                        $relatedProducts = array_merge($relatedProducts, $bestSellers);
                    }
                    
                    foreach($relatedProducts as $relatedProduct): ?>
                        <div class="product-card">
                            <?php if (!empty($relatedProduct['badge'])): ?>
                                <div class="badge-container">
                                    <span class="best-seller-badge"><?= $relatedProduct['badge'] ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <a href="product-detail.php?id=<?= $relatedProduct['id_product'] ?>">
                                    <img src="../../root_uploads/products/<?= htmlspecialchars($relatedProduct['image']) ?>" alt="<?= htmlspecialchars($relatedProduct['title']) ?>">
                                </a>
                            </div>
                            
                            <div class="product-details">
                                <div class="product-info">
                                    <h5 class="product-title"><?= htmlspecialchars($relatedProduct['title']) ?></h5>
                                    <span class="price"><?= number_format($relatedProduct['price'], 2) ?> DT</span>
                                </div>
                                
                                <p class="product-description"><?= substr(htmlspecialchars($relatedProduct['description']), 0, 60) ?>...</p>
                                
                                <div class="product-price-action">
                                    <a href="product-detail.php?id=<?= $relatedProduct['id_product'] ?>" class="btn btn-primary add-to-cart-btn">
                                        Voir le produit
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/newsletter.php'; ?>
    <?php include '../templates/footer.php'; ?>
    <script src="../assets/js/product-detail.js"></script>
</body>
</html>