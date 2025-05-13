<?php require_once '../config/init.php';

// Initialize variables
$edit_mode = false;
$product_id = '';
$title = '';
$description = '';
$price = '';
$stock = '';
$category_id = '';
$is_best_seller = 0;
$image = '';
$error_msg = '';
$success_msg = '';

// Get success message from session if exists
if(isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Search parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category_filter']) ? intval($_GET['category_filter']) : 0;
$stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';
$best_seller_filter = isset($_GET['best_seller']) ? $_GET['best_seller'] : '';

// Process add/edit product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new product code - unchanged
                $title = $_POST['title'];
                $description = $_POST['description'];
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category_id = intval($_POST['category_id']);
                $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
                
                // Handle image upload
                $image_filename = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $upload_dir = '../root_uploads/products/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $image_filename = time() . '_' . basename($_FILES['image']['name']);
                    $target_path = $upload_dir . $image_filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        // File uploaded successfully
                    } else {
                        $error_msg = "Échec du téléchargement de l'image.";
                    }
                }
                
                try {
                    // Insert into database using PDO
                    $stmt = $db->prepare("INSERT INTO product (title, description, price, image, stock, category_id, is_best_seller, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    if ($stmt->execute([$title, $description, $price, $image_filename, $stock, $category_id, $is_best_seller])) {
                        $_SESSION['success_msg'] = "Produit ajouté avec succès!";
                        header('Location: product.php');
                        exit();
                    }
                } catch (PDOException $e) {
                    $error_msg = "Erreur: " . $e->getMessage();
                }
                break;
                
            case 'edit':
                // Edit product code - unchanged
                $product_id = $_POST['product_id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category_id = intval($_POST['category_id']);
                $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
                
                try {
                    // Handle image update
                    $image_params = [];
                    $image_sql = "";
                    
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $upload_dir = '../root_uploads/products/';
                        $image_filename = time() . '_' . basename($_FILES['image']['name']);
                        $target_path = $upload_dir . $image_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            // Get and delete old image if exists
                            $stmt = $db->prepare("SELECT image FROM product WHERE id_product = ?");
                            $stmt->execute([$product_id]);
                            $old_image = $stmt->fetchColumn();
                            
                            if (!empty($old_image) && file_exists($upload_dir . $old_image)) {
                                unlink($upload_dir . $old_image);
                            }
                            
                            $image_sql = ", image = ?";
                            $image_params[] = $image_filename;
                        }
                    }
                    
                    // Update product in database
                    $sql = "UPDATE product SET 
                            title = ?, 
                            description = ?,
                            price = ?, 
                            stock = ?, 
                            category_id = ?, 
                            is_best_seller = ?
                            $image_sql
                            WHERE id_product = ?";
                    
                    $params = [$title, $description, $price, $stock, $category_id, $is_best_seller];
                    
                    if (!empty($image_params)) {
                        $params = array_merge($params, $image_params);
                    }
                    
                    $params[] = $product_id;
                    
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    
                    $_SESSION['success_msg'] = "Produit mis à jour avec succès!";
                    header('Location: product.php');
                    exit();
                } catch (PDOException $e) {
                    $error_msg = "Erreur: " . $e->getMessage();
                }
                break;
                
            // Other cases unchanged (delete, toggle_best_seller)
            case 'delete':
                $product_id = $_POST['product_id'];
                
                try {
                    // Get image filename
                    $stmt = $db->prepare("SELECT image FROM product WHERE id_product = ?");
                    $stmt->execute([$product_id]);
                    $image_to_delete = $stmt->fetchColumn();
                    
                    // Delete product from database
                    $stmt = $db->prepare("DELETE FROM product WHERE id_product = ?");
                    $stmt->execute([$product_id]);
                    
                    // Delete image file if exists
                    if (!empty($image_to_delete)) {
                        $image_path = '../root_uploads/products/' . $image_to_delete;
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                    $_SESSION['success_msg'] = "Produit supprimé avec succès!";
                    header('Location: product.php');
                    exit();
                } catch (PDOException $e) {
                    $error_msg = "Erreur lors de la suppression du produit: " . $e->getMessage();
                }
                break;
                
            case 'toggle_best_seller':
                $product_id = $_POST['product_id'];
                $current_status = $_POST['current_status'];
                $new_status = $current_status == 1 ? 0 : 1;
                
                try {
                    $stmt = $db->prepare("UPDATE product SET is_best_seller = ? WHERE id_product = ?");
                    $stmt->execute([$new_status, $product_id]);
                    $_SESSION['success_msg'] = "Statut de best-seller mis à jour!";
                    
                    // Maintain search parameters when redirecting
                    $redirect_url = 'product.php';
                    $params = [];
                    
                    if (!empty($search_term)) $params[] = 'search=' . urlencode($search_term);
                    if (!empty($category_filter)) $params[] = 'category_filter=' . $category_filter;
                    if (!empty($stock_status)) $params[] = 'stock_status=' . $stock_status;
                    if (!empty($best_seller_filter)) $params[] = 'best_seller=' . $best_seller_filter;
                    if ($current_page > 1) $params[] = 'page=' . $current_page;
                    
                    if (!empty($params)) {
                        $redirect_url .= '?' . implode('&', $params);
                    }
                    
                    header('Location: ' . $redirect_url);
                    exit();
                } catch (PDOException $e) {
                    $error_msg = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
                }
                break;
        }
    }
}

// Load product data for editing
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $product_id = $_GET['edit'];
    
    try {
        $stmt = $db->prepare("SELECT title, description, price, image, stock, category_id, is_best_seller FROM product WHERE id_product = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $title = $product['title'];
            $description = $product['description'];
            $price = $product['price'];
            $image = $product['image'];
            $stock = $product['stock'];
            $category_id = $product['category_id'];
            $is_best_seller = $product['is_best_seller'];
        }
    } catch (PDOException $e) {
        $error_msg = "Erreur lors du chargement du produit: " . $e->getMessage();
    }
}

// Get all categories for dropdown
$categories = [];
try {
    $stmt = $db->query("SELECT id_category, name FROM category ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Erreur lors du chargement des catégories: " . $e->getMessage();
}

// Get filtered products with pagination
$products = [];
$total_products = 0;

try {
    $where_conditions = [];
    $params = [];
    
    // Build search query
    if (!empty($search_term)) {
        $where_conditions[] = "(p.title LIKE ? OR p.id_product LIKE ?)";
        $params[] = "%$search_term%";
        $params[] = "%$search_term%";
    }
    
    if (!empty($category_filter)) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category_filter;
    }
    
    if ($stock_status === 'in_stock') {
        $where_conditions[] = "p.stock > 0";
    } elseif ($stock_status === 'out_of_stock') {
        $where_conditions[] = "p.stock = 0";
    } elseif ($stock_status === 'low_stock') {
        $where_conditions[] = "p.stock > 0 AND p.stock <= 5";
    }
    
    if ($best_seller_filter === '1') {
        $where_conditions[] = "p.is_best_seller = 1";
    } elseif ($best_seller_filter === '0') {
        $where_conditions[] = "p.is_best_seller = 0";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM product p $where_clause";
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetchColumn();
    
    // Get paginated results
    $sql = "SELECT p.*, c.name as category_name FROM product p 
            LEFT JOIN category c ON p.category_id = c.id_category 
            $where_clause
            ORDER BY p.id_product DESC
            LIMIT $items_per_page OFFSET $offset";
            
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate product statistics
    $stmt = $db->query("SELECT 
                        COUNT(*) as total_products,
                        COUNT(CASE WHEN stock = 0 THEN 1 END) as out_of_stock,
                        COUNT(CASE WHEN stock > 0 AND stock <= 5 THEN 1 END) as low_stock,
                        COUNT(CASE WHEN is_best_seller = 1 THEN 1 END) as featured_products
                        FROM product");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_msg = "Erreur lors du chargement des produits: " . $e->getMessage();
}

// Calculate total pages for pagination
$total_pages = ceil($total_products / $items_per_page);
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Produits</h1>
    
    </div>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>
    
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-box-seam me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h3 class="m-0"><?php echo $stats['total_products']; ?></h3>
                        <div>Produits Totaux</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h3 class="m-0"><?php echo $stats['low_stock']; ?></h3>
                        <div>Stock Faible</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-x-circle me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h3 class="m-0"><?php echo $stats['out_of_stock']; ?></h3>
                        <div>En Rupture de Stock</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-star-fill me-3" style="font-size: 2rem;"></i>
                    <div>
                        <h3 class="m-0"><?php echo $stats['featured_products']; ?></h3>
                        <div>Produits Vedettes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Product Form -->
    <div class="card mb-4" id="addProductForm">
        <div class="card-header bg-primary text-white">
            <?php echo $edit_mode ? 'Modifier le Produit #'.$product_id : 'Ajouter un Nouveau Produit'; ?>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Titre du Produit*</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="price" class="form-label">Prix*</label>
                        <div class="input-group">
                            <span class="input-group-text">DT</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="stock" class="form-label">Stock*</label>
                        <input type="number" min="0" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($stock); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Catégorie*</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Sélectionner une Catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_category']; ?>" <?php echo ($category['id_category'] == $category_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="image" class="form-label">Image du Produit</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                         <?php if ($edit_mode && !empty($image)): ?>
                            <small class="form-text text-muted">Laissez vide pour conserver l'image actuelle.</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($image)): ?>
                    <div class="row mb-3">
                        <div class="col-md-6 offset-md-6">
                            <div class="d-flex align-items-center">
                                <img src="../root_uploads/products/<?php echo htmlspecialchars($image); ?>" alt="Image actuelle du produit" class="img-thumbnail me-3" style="max-height: 100px;">
                                <span class="text-muted">Image actuelle</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_best_seller" name="is_best_seller" <?php echo $is_best_seller ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_best_seller">Marquer comme Meilleure Vente</label>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_mode ? 'Mettre à Jour le Produit' : 'Ajouter le Produit'; ?>
                    </button>
                    
                    <?php if ($edit_mode): ?>
                        <a href="product.php" class="btn btn-secondary">Annuler</a>
                    <?php else: ?>
                        <button type="reset" class="btn btn-outline-secondary">Réinitialiser</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Product Search and Filters -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-search me-2"></i> Rechercher & Filtrer les Produits
        </div>
        <div class="card-body">
            <form action="product.php" method="GET" class="row g-3">
                <div class="col-lg-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Rechercher par titre ou ID..." name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-2">
                    <select name="category_filter" class="form-select">
                        <option value="">Toutes les Catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_category']; ?>" <?php echo ($category['id_category'] == $category_filter) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-2">
                    <select name="stock_status" class="form-select">
                        <option value="">Tout le Stock</option>
                        <option value="in_stock" <?php echo ($stock_status === 'in_stock') ? 'selected' : ''; ?>>En Stock</option>
                        <option value="out_of_stock" <?php echo ($stock_status === 'out_of_stock') ? 'selected' : ''; ?>>En Rupture de Stock</option>
                        <option value="low_stock" <?php echo ($stock_status === 'low_stock') ? 'selected' : ''; ?>>Stock Faible</option>
                    </select>
                </div>
                
                <div class="col-lg-2">
                    <select name="best_seller" class="form-select">
                        <option value="">Tous les Produits</option>
                        <option value="1" <?php echo ($best_seller_filter === '1') ? 'selected' : ''; ?>>Meilleures Ventes</option>
                        <option value="0" <?php echo ($best_seller_filter === '0') ? 'selected' : ''; ?>>Produits Réguliers</option>
                    </select>
                </div>
                
                <div class="col-lg-2">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <?php if (!empty($search_term) || !empty($category_filter) || !empty($stock_status) || !empty($best_seller_filter)): ?>
                            <a href="product.php" class="btn btn-outline-secondary">Effacer les Filtres</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Product Listing -->
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i> Inventaire des Produits</span>
            <span class="badge bg-secondary"><?php echo $total_products; ?> produits trouvés</span>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Aucun produit trouvé</h4>
                    <?php if (!empty($search_term) || !empty($category_filter) || !empty($stock_status) || !empty($best_seller_filter)): ?>
                        <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                        <a href="product.php" class="btn btn-outline-primary">Effacer tous les filtres</a>
                    <?php else: ?>
                        <p class="text-muted">Commencez par ajouter votre premier produit</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Meilleure Vente</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id_product']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../root_uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                class="img-thumbnail" style="max-height: 50px; max-width: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted">Aucune image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>DT <?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            if ($product['stock'] == 0) { echo 'bg-danger'; }
                                            elseif ($product['stock'] <= 5) { echo 'bg-warning text-dark'; }
                                            else { echo 'bg-success'; }
                                        ?>">
                                            <?php 
                                                if ($product['stock'] == 0) { echo 'Rupture'; }
                                                elseif ($product['stock'] <= 5) { echo 'Faible (' . $product['stock'] . ')'; }
                                                else { echo $product['stock']; }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_best_seller">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $product['is_best_seller']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $product['is_best_seller'] ? 'btn-success' : 'btn-outline-secondary'; ?>" title="<?php echo $product['is_best_seller'] ? 'Retirer des meilleures ventes' : 'Marquer comme meilleure vente'; ?>">
                                                <?php if ($product['is_best_seller']): ?>
                                                    <i class="bi bi-star-fill me-1"></i> Vedette
                                                <?php else: ?>
                                                    <i class="bi bi-star me-1"></i> Régulier
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="product.php?edit=<?php echo $product['id_product']; ?>#addProductForm" class="btn btn-sm btn-warning" title="Modifier le produit">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                            <form method="post" class="delete-form d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer le produit">
                                                    <i class="bi bi-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Pagination des produits" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildPaginationUrl(1); ?>" aria-label="Première">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo buildPaginationUrl($current_page - 1); ?>" aria-label="Précédent">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php 
                                // Determine pagination range
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);

                                if ($current_page <= 3) {
                                    $end_page = min($total_pages, 5);
                                }
                                if ($current_page > $total_pages - 3) {
                                    $start_page = max(1, $total_pages - 4);
                                }
                            ?>

                            <?php if ($start_page > 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo buildPaginationUrl($current_page + 1); ?>" aria-label="Suivant">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildPaginationUrl($total_pages); ?>" aria-label="Dernière">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to build pagination URLs with existing search parameters
function buildPaginationUrl($page) {
    global $search_term, $category_filter, $stock_status, $best_seller_filter;
    
    $params = [];
    $params[] = "page=$page";
    
    if (!empty($search_term)) $params[] = 'search=' . urlencode($search_term);
    if (!empty($category_filter)) $params[] = 'category_filter=' . $category_filter;
    if (!empty($stock_status)) $params[] = 'stock_status=' . $stock_status;
    if (!empty($best_seller_filter)) $params[] = 'best_seller=' . $best_seller_filter;
    
    return 'product.php?' . implode('&', $params);
}
?>

<script>
    // Confirm deletion
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')) {
                event.preventDefault();
            }
        });
    });
    
   document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll to form when editing
    <?php if (isset($_GET['edit'])): ?>
    document.getElementById('addProductForm').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
    <?php endif; ?>
    
    // Initialize all dropdowns manually
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});
</script>

<?php include('includes/footer.php'); ?>


