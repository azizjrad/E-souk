<?php
session_start();
require_once __DIR__ . '/../../config/init.php';

// Get search query
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';

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

// Get categories for filtering
$categoryQuery = "SELECT id_category, name FROM category ORDER BY name";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Build search query
$query = "SELECT p.*, c.name as category_name 
          FROM product p
          LEFT JOIN category c ON p.category_id = c.id_category
          WHERE (p.title LIKE :search OR p.description LIKE :search) 
          AND p.price BETWEEN :price_min AND :price_max";

$params = [
    ':search' => '%' . $search_term . '%',
    ':price_min' => $priceMin,
    ':price_max' => $priceMax
];

// Add category filter if selected
if ($categoryFilter) {
    $query .= " AND p.category_id = :category_id";
    $params[':category_id'] = $categoryFilter;
}

// Add sorting
switch ($sort) {
    case 'price-low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price-high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    case 'popular':
        $query .= " ORDER BY p.is_best_seller DESC, p.title ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

// Count total products for pagination
$countQuery = str_replace("p.*, c.name as category_name", "COUNT(*) as total", $query);
$countStmt = $db->prepare($countQuery);
foreach ($params as $param => $value) {
    $countStmt->bindValue($param, $value);
}
$countStmt->execute();
$totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// Apply pagination to product query
$query .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $productsPerPage;
$params[':offset'] = ($currentPage - 1) * $productsPerPage;

// Execute paginated product query
$stmt = $db->prepare($query);
foreach ($params as $param => $value) {
    if ($param === ':limit' || $param === ':offset') {
        $stmt->bindValue($param, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($param, $value);
    }
}
$stmt->execute();
$filtered_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function getSortLabel($sort) {
    switch ($sort) {
        case 'price-low': return 'Prix croissant';
        case 'price-high': return 'Prix décroissant';
        case 'newest': return 'Nouveautés';
        case 'popular': return 'Popularité';
        default: return 'Pertinence';
    }
}

function isInWishlist($productId) {
    return in_array($productId, $_SESSION['wishlist'] ?? []);
}

function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

// Set page title and description
$page_title = "Résultats pour: " . htmlspecialchars($search_term);
$page_description = "Découvrez nos produits correspondant à votre recherche: " . htmlspecialchars($search_term);

// Hero section variables
$title = "Résultats de recherche";
$subtitle = "Découvrez tous les produits correspondant à: \"" . htmlspecialchars($search_term) . "\"";
$breadcrumbs = [
    'Accueil' => 'index.php',
    'Recherche' => '#',  
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/product.css">
    <link rel="stylesheet" href="../assets/css/components/hero.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>
</head>
<body>
    <?php include '../templates/navbar.php'; ?>
    
    <!-- Hero section -->
    <?php include '../templates/hero.php'; ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar with filters -->
            <div class="col-lg-3">
                <div class="filter-section rounded shadow-sm p-4 mb-4">
                    <h5 class="mb-4">Filtres</h5>
                    
                    <form action="search.php" method="GET">
                        <!-- Keep the search term -->
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_term); ?>">
                        
                        <!-- Category filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Catégories</h6>
                            <?php foreach ($categories as $category): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" 
                                           name="category" id="cat-<?php echo $category['id_category']; ?>" 
                                           value="<?php echo $category['id_category']; ?>"
                                           <?php echo ($categoryFilter == $category['id_category']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cat-<?php echo $category['id_category']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" 
                                       name="category" id="cat-all" value=""
                                       <?php echo ($categoryFilter === null) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="cat-all">
                                    Toutes les catégories
                                </label>
                            </div>
                        </div>
                        
                        <!-- Price range filter -->
                        <div class="mb-4">
                            <h6 class="mb-3">Prix</h6>
                            <div id="price-range"></div>
                            <div class="d-flex justify-content-between mt-2">
                                <span id="price-min"><?php echo $priceMin; ?> DT</span>
                                <span id="price-max"><?php echo $priceMax; ?> DT</span>
                            </div>
                            <input type="hidden" name="price_min" id="price_min_input" value="<?php echo $priceMin; ?>">
                            <input type="hidden" name="price_max" id="price_max_input" value="<?php echo $priceMax; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Appliquer les filtres</button>
                    </form>
                </div>
            </div>
            
            <!-- Main content with products -->
            <div class="col-lg-9">
                <!-- Search results header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <?php if ($totalProducts > 0): ?>
                            <h5><?php echo $totalProducts; ?> produit(s) trouvé(s)</h5>
                        <?php else: ?>
                            <h5>Aucun produit trouvé</h5>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sort dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle product-sort-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Trier par: <?php echo getSortLabel($sort); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end product-sort-menu" aria-labelledby="sortDropdown">
                            <li><a class="dropdown-item <?php echo ($sort === 'default') ? 'active' : ''; ?>" 
                                   href="?q=<?php echo urlencode($search_term); ?>&sort=default<?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>">
                                   Pertinence</a></li>
                            <li><a class="dropdown-item <?php echo ($sort === 'price-low') ? 'active' : ''; ?>" 
                                   href="?q=<?php echo urlencode($search_term); ?>&sort=price-low<?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>">
                                   Prix croissant</a></li>
                            <li><a class="dropdown-item <?php echo ($sort === 'price-high') ? 'active' : ''; ?>" 
                                   href="?q=<?php echo urlencode($search_term); ?>&sort=price-high<?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>">
                                   Prix décroissant</a></li>
                            <li><a class="dropdown-item <?php echo ($sort === 'newest') ? 'active' : ''; ?>" 
                                   href="?q=<?php echo urlencode($search_term); ?>&sort=newest<?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>">
                                   Nouveautés</a></li>
                            <li><a class="dropdown-item <?php echo ($sort === 'popular') ? 'active' : ''; ?>" 
                                   href="?q=<?php echo urlencode($search_term); ?>&sort=popular<?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>">
                                   Popularité</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Product grid -->
                <?php if (!empty($filtered_products)): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($filtered_products as $product): ?>
                            <div class="col">
                                <div class="card product-card h-100">
                                    <?php if ($product['is_best_seller']): ?>
                                        <span class="badge bg-danger best-seller-badge">Coup de cœur</span>
                                    <?php endif; ?>
                                    
                                    <a href="../pages/product-detail.php?id=<?php echo $product['id_product']; ?>" class="position-relative">
                                        <img src="<?php echo ROOT_URL; ?>root_uploads/products/<?php echo !empty($product['image']) ? $product['image'] : 'placeholder.jpg'; ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    </a>
                                    
                                    <div class="wishlist-btn" data-product-id="<?php echo $product['id_product']; ?>"
                                         data-bs-toggle="tooltip" data-bs-placement="left" title="Ajouter à ma liste de souhaits">
                                        <i class="far fa-heart <?php echo isInWishlist($product['id_product']) ? 'active fas' : ''; ?>"></i>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="card-title-row d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                            <span class="price"><?php echo number_format($product['price'], 2); ?> DT</span>
                                        </div>
                                        
                                        <p class="card-text text-muted mb-1">
                                            <small><?php echo htmlspecialchars($product['category_name']); ?></small>
                                        </p>
                                        
                                        <p class="card-text mb-3"><?php echo substr(htmlspecialchars($product['description']), 0, 60); ?>...</p>
                                        
                                        <?php if ($product['stock'] > 0): ?>
                                            <span class="stock-badge in-stock mb-3 d-inline-block">
                                                <i class="fas fa-check-circle"></i> En stock
                                            </span>
                                        <?php else: ?>
                                            <span class="stock-badge out-of-stock mb-3 d-inline-block">
                                                <i class="fas fa-times-circle"></i> Rupture de stock
                                            </span>
                                        <?php endif; ?>
                                        
                                        <div class="d-grid">
                                            <a href="<?= ROOT_URL . 'public/pages/product-detail.php?id=' . $product['id_product']; ?>" class="btn btn-dark">
                                                Voir le produit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($currentPage - 1); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($currentPage + 1); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Aucun produit ne correspond à votre recherche. Veuillez essayer d'autres termes ou filtres.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../templates/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize price range slider
            const priceRange = document.getElementById('price-range');
            if (priceRange) {
                noUiSlider.create(priceRange, {
                    start: [<?php echo $priceMin; ?>, <?php echo $priceMax; ?>],
                    connect: true,
                    range: {
                        'min': 0,
                        'max': 500
                    },
                    format: {
                        to: function (value) {
                            return Math.round(value);
                        },
                        from: function (value) {
                            return Math.round(value);
                        }
                    }
                });

                const priceMinDisplay = document.getElementById('price-min');
                const priceMaxDisplay = document.getElementById('price-max');
                const priceMinInput = document.getElementById('price_min_input');
                const priceMaxInput = document.getElementById('price_max_input');

                priceRange.noUiSlider.on('update', function (values, handle) {
                    if (handle === 0) {
                        priceMinDisplay.innerHTML = values[0] + ' DT';
                        priceMinInput.value = values[0];
                    } else {
                        priceMaxDisplay.innerHTML = values[1] + ' DT';
                        priceMaxInput.value = values[1];
                    }
                });
            }

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Wishlist functionality
            document.querySelectorAll('.wishlist-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    
                    fetch('add-to-wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'product_id=' + productId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const heartIcon = this.querySelector('i');
                            heartIcon.classList.toggle('far');
                            heartIcon.classList.toggle('fas');
                            heartIcon.classList.toggle('active');
                            
                            // Add animation
                            heartIcon.classList.add('heart-beat');
                            setTimeout(() => {
                                heartIcon.classList.remove('heart-beat');
                            }, 800);
                            
                            // Update counter in navbar
                            const wishlistCounter = document.querySelector('.wishlist-count');
                            if (wishlistCounter) {
                                wishlistCounter.textContent = data.count;
                                wishlistCounter.style.display = data.count > 0 ? 'block' : 'none';
                            }
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>