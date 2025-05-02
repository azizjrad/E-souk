<?php

require_once '../config/init.php';

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

// Process add/edit product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new product
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
                        $error_msg = "Failed to upload image.";
                    }
                }
                
                try {
                    // Insert into database using PDO
                    $stmt = $db->prepare("INSERT INTO product (title, description, price, image, stock, category_id, is_best_seller, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    if ($stmt->execute([$title, $description, $price, $image_filename, $stock, $category_id, $is_best_seller])) {
                        $_SESSION['success_msg'] = "Product added successfully!";
                        header('Location: product.php');  // Add this line
                        exit();  // Add this line
                    }
                } catch (PDOException $e) {
                    $error_msg = "Error: " . $e->getMessage();
                }
                break;
                
            case 'edit':
                // Update existing product
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
                    
                    $success_msg = "Product updated successfully!";
                    $edit_mode = false;
                    $title = $description = $price = $stock = $category_id = '';
                    $is_best_seller = 0;
                } catch (PDOException $e) {
                    $error_msg = "Error: " . $e->getMessage();
                }
                break;
                
            case 'delete':
                // Delete product
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
                    $success_msg = "Product deleted successfully!";
                } catch (PDOException $e) {
                    $error_msg = "Error deleting product: " . $e->getMessage();
                }
                break;
                
            case 'toggle_best_seller':
                // Toggle best seller status
                $product_id = $_POST['product_id'];
                $current_status = $_POST['current_status'];
                $new_status = $current_status == 1 ? 0 : 1;
                
                try {
                    $stmt = $db->prepare("UPDATE product SET is_best_seller = ? WHERE id_product = ?");
                    $stmt->execute([$new_status, $product_id]);
                    $success_msg = "Best seller status updated!";
                } catch (PDOException $e) {
                    $error_msg = "Error updating status: " . $e->getMessage();
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
        $error_msg = "Error loading product: " . $e->getMessage();
    }
}

// Get all categories for dropdown
$categories = [];
try {
    $stmt = $db->query("SELECT id_category, name FROM category ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Error loading categories: " . $e->getMessage();
}

// Get all products
$products = [];
try {
    $stmt = $db->query("SELECT p.*, c.name as category_name FROM product p 
                        LEFT JOIN category c ON p.category_id = c.id_category 
                        ORDER BY p.id_product DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Error loading products: " . $e->getMessage();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Product Management</h1>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <!-- Add/Edit Product Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Product Title*</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="price" class="form-label">Price*</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="stock" class="form-label">Stock*</label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($stock); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category*</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_category']; ?>" <?php echo ($category['id_category'] == $category_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <?php if (!empty($image)): ?>
                        <div class="mb-2">
                            <img src="../uploads/products/<?php echo htmlspecialchars($image); ?>" alt="Current product image" class="img-thumbnail" style="max-height: 100px;">
                            <p class="text-muted">Current image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_best_seller" name="is_best_seller" <?php echo $is_best_seller ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_best_seller">Mark as Best Seller</label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_mode ? 'Update Product' : 'Add Product'; ?>
                </button>
                
                <?php if ($edit_mode): ?>
                    <a href="product.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Product Listing -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            Product Inventory
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Best Seller</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No products found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id_product']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../root_uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                class="img-thumbnail" style="max-height: 50px; max-width: 50px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_best_seller">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $product['is_best_seller']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $product['is_best_seller'] ? 'btn-success' : 'btn-outline-secondary'; ?>">
                                                <?php echo $product['is_best_seller'] ? 'Featured' : 'Regular'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="product.php?edit=<?php echo $product['id_product']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="post" class="delete-form d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Confirm deletion
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    });
</script>

<?php include('includes/footer.php'); ?>


