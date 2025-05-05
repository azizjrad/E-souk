<?php

require_once '../config/init.php';

// Initialisation des variables
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

// Traitement du formulaire d'ajout/modification de produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Ajouter un nouveau produit
                $title = $_POST['title'];
                $description = $_POST['description'];
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category_id = intval($_POST['category_id']);
                $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
                
                // Gérer le téléchargement d'image
                $image_filename = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $upload_dir = '../root_uploads/products/';
                    
                    // Créer le répertoire s'il n'existe pas
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $image_filename = time() . '_' . basename($_FILES['image']['name']);
                    $target_path = $upload_dir . $image_filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        // Fichier téléchargé avec succès
                    } else {
                        $error_msg = "Échec du téléchargement de l'image.";
                    }
                }
                
                try {
                    // Insérer dans la base de données avec PDO
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
                // Mettre à jour un produit existant
                $product_id = $_POST['product_id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category_id = intval($_POST['category_id']);
                $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
                
                try {
                    // Gérer la mise à jour de l'image
                    $image_params = [];
                    $image_sql = "";
                    
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $upload_dir = '../root_uploads/products/';
                        $image_filename = time() . '_' . basename($_FILES['image']['name']);
                        $target_path = $upload_dir . $image_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            // Récupérer et supprimer l'ancienne image si elle existe
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
                    
                    // Mettre à jour le produit dans la base de données
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
                    
                    $success_msg = "Produit mis à jour avec succès!";
                    $edit_mode = false;
                    $title = $description = $price = $stock = $category_id = '';
                    $is_best_seller = 0;
                } catch (PDOException $e) {
                    $error_msg = "Erreur: " . $e->getMessage();
                }
                break;
                
            case 'delete':
                // Supprimer un produit
                $product_id = $_POST['product_id'];
                
                try {
                    // Obtenir le nom du fichier image
                    $stmt = $db->prepare("SELECT image FROM product WHERE id_product = ?");
                    $stmt->execute([$product_id]);
                    $image_to_delete = $stmt->fetchColumn();
                    
                    // Supprimer le produit de la base de données
                    $stmt = $db->prepare("DELETE FROM product WHERE id_product = ?");
                    $stmt->execute([$product_id]);
                    
                    // Supprimer le fichier image s'il existe
                    if (!empty($image_to_delete)) {
                        $image_path = '../root_uploads/products/' . $image_to_delete;
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                    }
                    $success_msg = "Produit supprimé avec succès!";
                } catch (PDOException $e) {
                    $error_msg = "Erreur de suppression du produit: " . $e->getMessage();
                }
                break;
                
            case 'toggle_best_seller':
                // Basculer le statut de meilleure vente
                $product_id = $_POST['product_id'];
                $current_status = $_POST['current_status'];
                $new_status = $current_status == 1 ? 0 : 1;
                
                try {
                    $stmt = $db->prepare("UPDATE product SET is_best_seller = ? WHERE id_product = ?");
                    $stmt->execute([$new_status, $product_id]);
                    $success_msg = "Statut de meilleure vente mis à jour!";
                } catch (PDOException $e) {
                    $error_msg = "Erreur de mise à jour du statut: " . $e->getMessage();
                }
                break;
        }
    }
}

// Charger les données du produit pour modification
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

// Obtenir toutes les catégories pour le menu déroulant
$categories = [];
try {
    $stmt = $db->query("SELECT id_category, name FROM category ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Erreur lors du chargement des catégories: " . $e->getMessage();
}

// Obtenir tous les produits
$products = [];
try {
    $stmt = $db->query("SELECT p.*, c.name as category_name FROM product p 
                        LEFT JOIN category c ON p.category_id = c.id_category 
                        ORDER BY p.id_product DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Erreur lors du chargement des produits: " . $e->getMessage();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Gestion des Produits</h1>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <!-- Formulaire d'ajout/modification de produit -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <?php echo $edit_mode ? 'Modifier le Produit' : 'Ajouter un Nouveau Produit'; ?>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
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
                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="stock" class="form-label">Stock*</label>
                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars($stock); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label">Catégorie*</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
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
                    <label for="image" class="form-label">Image du Produit</label>
                    <?php if (!empty($image)): ?>
                        <div class="mb-2">
                            <img src="../root_uploads/products/<?php echo htmlspecialchars($image); ?>" alt="Image actuelle du produit" class="img-thumbnail" style="max-height: 100px;">
                            <p class="text-muted">Image actuelle</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_best_seller" name="is_best_seller" <?php echo $is_best_seller ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_best_seller">Marquer comme Meilleure Vente</label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_mode ? 'Mettre à jour le Produit' : 'Ajouter le Produit'; ?>
                </button>
                
                <?php if ($edit_mode): ?>
                    <a href="product.php" class="btn btn-secondary">Annuler</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Liste des Produits -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            Inventaire des Produits
        </div>
        <div class="card-body">
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
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Aucun produit trouvé</td>
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
                                            <span class="text-muted">Pas d'image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?> DT</td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_best_seller">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $product['is_best_seller']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $product['is_best_seller'] ? 'btn-success' : 'btn-outline-secondary'; ?>">
                                                <?php echo $product['is_best_seller'] ? 'Mis en avant' : 'Standard'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="product.php?edit=<?php echo $product['id_product']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                            <form method="post" class="delete-form d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Supprimer
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
    // Confirmer la suppression
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit? Cette action ne peut pas être annulée.')) {
                event.preventDefault();
            }
        });
    });
</script>

<?php include('includes/footer.php'); ?>


