<?php
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get wishlist products from database
$wishlisted_products = [];

// Build query to get wishlist products
$query = "SELECT p.*, c.name as category_name, w.created_at as added_on
          FROM wishlist w 
          JOIN product p ON w.product_id = p.id_product
          LEFT JOIN category c ON p.category_id = c.id_category
          WHERE w.user_id = ?
          ORDER BY w.created_at DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $wishlisted_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product IDs for client-side checks and wishlist count for navbar
    $wishlistItems = array_column($wishlisted_products, 'id_product');
    
    // Store in session for easy access across pages
    $_SESSION['wishlist'] = $wishlistItems;
    $_SESSION['wishlist_count'] = count($wishlistItems);
} catch (Exception $e) {
    // Handle error - could log this
    error_log('Wishlist fetch error: ' . $e->getMessage());
    $_SESSION['wishlist'] = [];
    $_SESSION['wishlist_count'] = 0;
}

$page_title = "Artisanat Tunisien - Wishlist";
$page_description = "Découvrez notre sélection de produits artisanaux tunisiens dans votre liste de souhaits. Ajoutez vos articles préférés et passez à l'achat facilement.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/wishlist.css">
</head>
<body>
    <?php include '../templates/navbar.php'; ?>
    <!-- Wishlist Section -->
    <section class="container py-5 wishlist-section">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title">Mes Articles Favoris</h2>
                <hr class="mx-auto mb-3" style="width: 60px; border: 2px solid #fcd34d">
                
                <?php if (!empty($wishlisted_products)) : ?>
                    <button id="clear-wishlist" class="btn btn-outline-danger btn-sm mt-2">
                        <i class="fas fa-trash-alt me-1"></i> Vider ma liste
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4 wishlist-container">
            <!-- Empty Wishlist State -->
            <?php if (empty($wishlisted_products)) : ?>
                <div class="col-12 empty-wishlist">
                    <div class="text-center py-5">
                        <i class="far fa-heart wishlist-empty-icon"></i>
                        <h3>Votre liste de favoris est vide</h3>
                        <p>Ajoutez des produits à votre liste pour les retrouver facilement plus tard</p>
                        <a href="product.php" class="btn btn-medium mt-3">Découvrir les produits</a>
                    </div>
                </div>
            <?php else : ?>
                <!-- Products in wishlist -->
                <?php foreach ($wishlisted_products as $product) : ?>
                <div class="col-lg-3 col-md-4 col-6 product-item" data-product-id="<?php echo $product['id_product']; ?>">
                    <div class="wishlist-card">
                        <div class="wishlist-remove">
                            <button class="btn-remove wishlist-icon" 
                                  title="Retirer des favoris" 
                                  data-product-id="<?php echo $product['id_product']; ?>">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="wishlist-image">
                            <a href="product-detail.php?id=<?php echo $product['id_product']; ?>">
                                <img src="../../root_uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                    alt="<?php echo htmlspecialchars($product['title']); ?>">
                            </a>
                        </div>
                        <div class="wishlist-details">
                            <h5><?php echo htmlspecialchars($product['title']); ?></h5>
                            <p class="wishlist-price"><?php echo number_format($product['price'], 2); ?> DT</p>
                            <button class="btn-add-cart add-to-cart-btn" data-product-id="<?php echo $product['id_product']; ?>">
                                <i class="fas fa-shopping-cart"></i> Ajouter au panier
                            </button>
                            <small class="text-muted d-block mt-2">Ajouté le <?php echo date('d/m/Y', strtotime($product['added_on'])); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="product.php" class="btn btn-continue-shopping">
                <i class="fas fa-arrow-left me-2"></i>Continuer vos achats
            </a>
        </div>
    </section>

    <!-- Toast container for notifications -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3"></div>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/newsletter.php'; ?>
    <?php include '../templates/footer.php'; ?>
    
    <!-- Scripts -->
    <script>
        // Make ROOT_URL available to JavaScript
        const ROOT_URL = '<?php echo ROOT_URL; ?>';
    </script>
    <script src="<?php echo ASSETS_URL; ?>js/cart.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/wishlist.js"></script>
    
</body>
</html>