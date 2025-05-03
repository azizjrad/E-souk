<?php
require_once __DIR__ . '/../../config/init.php';

// Initialize wishlist properly from the database if user is logged in
if (isset($_SESSION['user_id']) && (!isset($_SESSION['wishlist']) || empty($_SESSION['wishlist']))) {
    // Fetch wishlist items from database
    $wishlistStmt = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wishlistStmt->execute([$_SESSION['user_id']]);
    $wishlistItems = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Store in session
    $_SESSION['wishlist'] = $wishlistItems;
    $_SESSION['wishlist_count'] = count($wishlistItems);
}

// Initialize filter variables with default values
$categoryFilter = $_GET['category'] ?? null;
$priceMin = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 0;
$priceMax = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 500;
$sort = $_GET['sort'] ?? 'default';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$productsPerPage = 12;

// Initialize wishlist if needed
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Get categories
$categoryQuery = "SELECT id_category, name FROM category ORDER BY name";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Build base product query
$query = "SELECT p.*, c.name as category_name 
          FROM product p
          LEFT JOIN category c ON p.category_id = c.id_category
          WHERE p.price BETWEEN :price_min AND :price_max";

// Add category filter if selected
if ($categoryFilter) {
    $query .= " AND p.category_id = :category_id";
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.id_product DESC";
        break;
    default:
        $query .= " ORDER BY p.title ASC";
}

// Count total products for pagination
$countQuery = str_replace("p.*, c.name as category_name", "COUNT(*) as total", $query);
$countStmt = $db->prepare($countQuery);
if ($categoryFilter) {
    $countStmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
}
$countStmt->bindParam(':price_min', $priceMin, PDO::PARAM_INT);
$countStmt->bindParam(':price_max', $priceMax, PDO::PARAM_INT);
$countStmt->execute();
$totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// Apply pagination to product query
$offset = ($currentPage - 1) * $productsPerPage;
$query .= " LIMIT :limit OFFSET :offset";

// Execute paginated product query
$stmt = $db->prepare($query);
if ($categoryFilter) {
    $stmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
}
$stmt->bindParam(':price_min', $priceMin, PDO::PARAM_INT);
$stmt->bindParam(':price_max', $priceMax, PDO::PARAM_INT);
$stmt->bindParam(':limit', $productsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$filtered_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function getSortLabel($sort) {
    $labels = [
        'price_asc' => 'Prix croissant',
        'price_desc' => 'Prix décroissant',
        'newest' => 'Plus récents',
        'default' => 'Pertinence'
    ];
    return $labels[$sort] ?? 'Pertinence';
}

function isInWishlist($productId) {
    if (!isset($_SESSION['wishlist'])) {
        return false;
    }
    
    // Ensure $productId is treated as an integer for comparison
    $productId = (int)$productId;
    
    // Check if product is in wishlist
    foreach ($_SESSION['wishlist'] as $item) {
        if ((int)$item === $productId) {
            return true;
        }
    }
    
    return false;
}

function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

$page_title = "Tous les produits artisanaux";
$page_description = "Découvrez notre sélection de produits artisanaux uniques et faits main.";

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>
    <?php include "../templates/navbar.php"; ?>
</head>
<body>
    
<!-- hero with breadcumb section -->
<section class="category-hero-section">
    <div class="hero-bg-overlay"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Accueil</a></li> 
                        <li class="breadcrumb-item"><a href="categories.php">Catégories</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Produits</li>
                    </ol>
                </nav>
                <h1 class="hero-title">Découvrez Nos Produits Artisanaux</h1>
                <p class="hero-subtitle">Une sélection authentique d'artisanat tunisien fait à la main par nos artisans locaux</p>
            </div>
        </div>
    </div>
</section>


    <!-- Main Products Section -->
    <section class="container py-5">
        <div class="row">
            <!-- Filters Column -->
            <div class="col-lg-3 mb-4">
                <div class="filter-section">
                    <h5 class="mb-4">Filtrer les produits</h5>
                    
                    <form id="filterForm" method="GET" action="">
                        <!-- Categories Filter -->
                        <div class="mb-4">
                            <h6>Catégories</h6>
                            <?php foreach ($categories as $category): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                    name="category" 
                                    id="cat_<?= $category['id_category']; ?>" 
                                    value="<?= $category['id_category']; ?>"
                                    <?= ($categoryFilter == $category['id_category']) ? 'checked' : ''; ?> />
                                <label class="form-check-label" for="cat_<?= $category['id_category']; ?>">
                                    <?= htmlspecialchars($category['name']); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                    name="category" 
                                    id="cat_all" 
                                    value=""
                                    <?= ($categoryFilter === null) ? 'checked' : ''; ?> />
                                <label class="form-check-label" for="cat_all">
                                    Toutes les catégories
                                </label>
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div class="mb-4">
                            <h6>Prix</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span id="priceMinValue"><?= $priceMin; ?> DT</span>
                                <span id="priceMaxValue"><?= $priceMax; ?> DT</span>
                            </div>
                            <div id="priceRangeSlider" class="mt-2"></div>
                            <input type="hidden" id="priceMinRange" name="price_min" value="<?= $priceMin; ?>">
                            <input type="hidden" id="priceMaxRange" name="price_max" value="<?= $priceMax; ?>">
                        </div>
                        
                        <input type="hidden" name="sort" id="sortInput" value="<?= $sort; ?>">
                        <button type="submit" class="btn btn-primary w-100">Appliquer les filtres</button>
                    </form>
                </div>
            </div>

            <!-- Products Column -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Tous nos produits artisanaux</h2>
                    <div class="dropdown">
                        <button class="btn btn-outline-dark product-sort-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown">
                            Trier par: <?= getSortLabel($sort); ?>
                        </button>
                        <ul class="product-sort-menu dropdown-menu">
                            <li><a class="dropdown-item sort-option" data-sort="default" href="#">Pertinence</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="price_asc" href="#">Prix croissant</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="price_desc" href="#">Prix décroissant</a></li>
                            <li><a class="dropdown-item sort-option" data-sort="newest" href="#">Plus récents</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="row g-4">
                    <?php if (count($filtered_products) > 0): ?>
                        <?php foreach ($filtered_products as $product): ?>
                            <!-- Product Card -->
                            <div class="col-md-4 col-6">
                                <div class="card h-100 product-card">
                                    <?php if (isset($product['is_best_seller']) && $product['is_best_seller']): ?>
                                        <span class="badge bg-danger best-seller-badge">Best Seller</span>
                                    <?php endif; ?>
                                    
                                    <span class="stock-badge <?= ($product['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                                        <?= ($product['stock'] > 0) ? 'En stock' : 'Épuisé'; ?>
                                    </span>
                                    
                                    <button class="wishlist-btn" data-product-id="<?= $product['id_product']; ?>">
                                        <i class="<?= isInWishlist($product['id_product']) ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                    
                                    <a href="product-detail.php?id=<?= $product['id_product']; ?>">
                                        <img src="../../root_uploads/products/<?= htmlspecialchars($product['image']); ?>" 
                                            class="card-img-top" 
                                            alt="<?= htmlspecialchars($product['title']); ?>" />
                                    </a>
                                    <div class="card-body">
                                        <div class="card-title-row">
                                            <h5 class="card-title"><?= htmlspecialchars($product['title']); ?></h5>
                                            <span class="price"><?= number_format($product['price'], 2); ?> DT</span>
                                        </div>
                                        <p class="card-text small text-muted">
                                            <?= htmlspecialchars(substr($product['description'], 0, 50)); ?>...
                                        </p>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-dark flex-grow-1 add-to-cart-btn" data-product-id="<?= $product['id_product']; ?>">
                                                <i class="fas fa-shopping-cart me-1"></i> Ajouter au panier
                                            </button>
                                            <a href="product-detail.php?id=<?= $product['id_product']; ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun produit ne correspond à vos critères de recherche.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <?php if ($currentPage > 1): ?>
                                <a class="page-link" href="<?= buildPaginationUrl($currentPage - 1); ?>">Précédent</a>
                            <?php else: ?>
                                <span class="page-link">Précédent</span>
                            <?php endif; ?>
                        </li>
                            
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?= buildPaginationUrl($i); ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <?php if ($currentPage < $totalPages): ?>
                                <a class="page-link" href="<?= buildPaginationUrl($currentPage + 1); ?>">Suivant</a>
                            <?php else: ?>
                                <span class="page-link">Suivant</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include "../templates/Topbtn.php"; ?>
    <?php include "../templates/newsletter.php"; ?>
    <?php include "../templates/footer.php"; ?>
    
    <script>
        const ROOT_URL = '<?= ROOT_URL; ?>';
        // Initialize noUiSlider for price range filter
        const priceRangeSlider = document.getElementById('priceRangeSlider');
        const priceMinValue = document.getElementById('priceMinValue');
        const priceMaxValue = document.getElementById('priceMaxValue');
        const priceMinRange = document.getElementById('priceMinRange');
        const priceMaxRange = document.getElementById('priceMaxRange');
        const priceMin = <?= $priceMin; ?>;
        const priceMax = <?= $priceMax; ?>;
        
        noUiSlider.create(priceRangeSlider, {
            start: [priceMin, priceMax],
            connect: true,
            range: {
                'min': 0,
                'max': 1000
            },
            step: 1,
            format: {
                to: function (value) {
                    return Math.round(value);
                },
                from: function (value) {
                    return typeof value === 'string' && value.includes('DT') 
                        ? Number(value.replace('DT', '')) 
                        : Number(value);
                }
            }
        });
        
        priceRangeSlider.noUiSlider.on('update', function (values, handle) {
            if (handle === 0) {
                priceMinValue.innerHTML = values[0] + ' DT';
                priceMinRange.value = Math.round(values[0]);
            } else {
                priceMaxValue.innerHTML = values[1] + ' DT';
                priceMaxRange.value = Math.round(values[1]);
            }
        });
        
        // Handle sorting options
        document.querySelectorAll('.sort-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const sortValue = this.getAttribute('data-sort');
                document.getElementById('sortInput').value = sortValue;
                document.getElementById('filterForm').submit();
            });
        });
    </script>
    <script src="<?= ASSETS_URL; ?>js/cart.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/wishlist.js"></script>
    <script>
    // Pass the wishlist data to JavaScript
    const wishlisted_product_ids = <?php echo json_encode(array_values(array_map('intval', $_SESSION['wishlist'] ?? []))); ?>;
    
    // Check for initial load
    document.addEventListener('DOMContentLoaded', function() {
        // Store wishlist data in sessionStorage
        if (wishlisted_product_ids.length > 0) {
            sessionStorage.setItem('wishlistedProducts', JSON.stringify(wishlisted_product_ids));
        }
    });
    </script>
</body>
</html>
