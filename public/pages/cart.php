<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';

$page_title = "Panier - E-Souk Tounsi";
$page_description = "Consultez et gérez les articles dans votre panier d'achat. Finalisez votre commande d'artisanat tunisien authentique.";


// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
    
    switch ($action) {
        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
                
                // If user is logged in, remove from database too
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    
                    // Get cart ID
                    $stmt = $db->prepare("SELECT id_cart FROM cart WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($cart) {
                        // Remove product from cart in database
                        $stmt = $db->prepare("DELETE FROM cart_product WHERE cart_id = ? AND product_id = ?");
                        $stmt->execute([$cart['id_cart'], $product_id]);
                    }
                }
            }
            break;
            
        case 'update':
            $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                
                // If user is logged in, update in database too
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    
                    // Get cart ID
                    $stmt = $db->prepare("SELECT id_cart FROM cart WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($cart) {
                        // Update product quantity in cart
                        $stmt = $db->prepare("UPDATE cart_product SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                        $stmt->execute([$quantity, $cart['id_cart'], $product_id]);
                    }
                }
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            
            // If user is logged in, clear database cart too
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                
                // Get cart ID
                $stmt = $db->prepare("SELECT id_cart FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $cart = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($cart) {
                    // Clear cart products
                    $stmt = $db->prepare("DELETE FROM cart_product WHERE cart_id = ?");
                    $stmt->execute([$cart['id_cart']]);
                }
            }
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: cart.php');
    exit;
}

// Load product details for cart items if needed
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => &$item) {
        // First check if $item is an array
        if (!is_array($item)) {
            // Convert scalar value to proper cart item format
            $item = [
                'quantity' => is_numeric($item) ? (int)$item : 1
            ];
        }
        
        // Check if we have a title but no name (handle both structures)
        if (isset($item['title']) && !isset($item['name'])) {
            $item['name'] = $item['title'];
        }
        
        // Now fetch missing product details from database if needed
        if (!isset($item['name']) || !isset($item['price']) || !isset($item['category'])) {
            // Fetch product details from database
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name 
                FROM product p
                LEFT JOIN category c ON p.category_id = c.id_category
                WHERE p.id_product = ?
            ");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $item['name'] = $product['title'];
                $item['price'] = $product['price'];
                $item['category'] = $product['category_name'];
                $item['image'] = $product['image'];
            }
        }
    }
    unset($item); // Break the reference
}

// Calculate totals
$subtotal = 0;
$shipping = !empty($_SESSION['cart']) ? 15 : 0; // Shipping is 15 DT if cart is not empty
$item_count = 0;

foreach ($_SESSION['cart'] as $product_id => $item) {
    // Check if item is an array with the required keys
    if (is_array($item) && isset($item['price']) && isset($item['quantity'])) {
        $subtotal += $item['price'] * $item['quantity'];
        $item_count += $item['quantity'];
    } else {
        // If item is not properly structured, remove it
        unset($_SESSION['cart'][$product_id]);
    }
}

$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include '../templates/navbar.php'; ?>

    <section class="container py-5 flex-grow-1">
        <h2 class="mb-4 text-center section-title">Mon Panier</h2>
        <hr class="mx-auto mb-5" style="width: 60px; border: 2px solid #fcd34d">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <?php if (empty($_SESSION['cart'])): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
                                <h4>Votre panier est vide</h4>
                                <p class="text-muted">Découvrez nos produits artisanaux et ajoutez-les à votre panier.</p>
                                <a href="product.php" class="btn btn-medium mt-3">Découvrir les produits</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($_SESSION['cart'] as $id => $item): ?>
                                <div class="d-flex flex-column flex-md-row align-items-center border-bottom pb-3 mb-3">
                                    <div class="flex-shrink-0 text-center mb-3 mb-md-0">
                                        <img src="<?= !empty($item['image']) ? '../../root_uploads/products/' . htmlspecialchars($item['image']) : 'https://via.placeholder.com/100x100?text=Produit' ?>" 
                                             alt="<?= !empty($item['name']) ? htmlspecialchars($item['name']) : 'Produit' ?>" 
                                             class="img-fluid rounded" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1 ms-md-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                            <form method="post">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <p class="text-muted mb-2">Catégorie: <?= htmlspecialchars($item['category'] ?? 'Non catégorisé') ?></p>
                                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                            <form method="post" class="d-flex align-items-center mb-2 mb-md-0">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                                <div class="input-group input-group-sm me-3" style="width: 120px;">
                                                    <button class="btn btn-outline-primary" type="button" onclick="this.nextElementSibling.stepDown();this.form.submit();">-</button>
                                                    <input type="number" name="quantity" class="form-control text-center" value="<?= $item['quantity'] ?>" min="1" max="99" onchange="this.form.submit()">
                                                    <button class="btn btn-outline-primary" type="button" onclick="this.previousElementSibling.stepUp();this.form.submit();">+</button>
                                                </div>
                                            </form>
                                            <div class="d-flex align-items-center">
                                                <p class="text-muted mb-0 me-3"><small><?= $item['quantity'] ?> × <?= $item['price'] ?> DT</small></p>
                                                <p class="text-primary fw-bold mb-0"><?= number_format($item['price'] * $item['quantity'], 2) ?> DT</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between flex-wrap">
                    <a href="product.php" class="btn btn-outline-primary mb-3 mb-sm-0">
                        <i class="fas fa-arrow-left me-2"></i>Continuer vos achats
                    </a>
                    
                    <?php if (!empty($_SESSION['cart'])): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-2"></i>Vider le panier
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 1">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Résumé de la commande</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Articles (<?= $item_count ?>)</span>
                            <span><?= number_format($subtotal, 2) ?> DT</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Frais de livraison</span>
                            <span><?= $shipping ?> DT</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span><?= number_format($total, 2) ?> DT</span>
                        </div>
                        <?php
                        // Check if cart is empty
                        $isCartEmpty = empty($_SESSION['cart']);
                        
                        // Check if user is logged in (works for both regular users and admins)
                        $isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
                        
                        // Determine the target URL based on login status and cart
                        $targetUrl = '#';
                        if (!$isCartEmpty) {
                            if ($isLoggedIn) {
                                $targetUrl = 'checkout.php';
                            } else {
                                // Use absolute path to ensure it works regardless of current location
                                $targetUrl = '../user/login.php?redirect=checkout';
                            }
                        }
                        ?>
                        <a href="<?= $targetUrl ?>" 
                           class="btn btn-primary w-100 mt-4 py-2 <?= $isCartEmpty ? 'disabled' : '' ?>">
                            <i class="fas fa-lock me-2"></i>Passer la commande
                        </a>
                        
                        <div class="mt-4">
                            <p class="mb-2 small"><i class="fas fa-shield-alt me-2 text-success"></i> Paiement sécurisé</p>
                            <p class="mb-2 small"><i class="fas fa-truck me-2 text-success"></i> Livraison rapide</p>
                            <p class="mb-0 small"><i class="fas fa-undo me-2 text-success"></i> Retours sous 30 jours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/footer.php'; ?>
    
    <script>
    // Update quantity with AJAX
    document.querySelectorAll('input[name="quantity"]').forEach(input => {
        input.addEventListener('change', function() {
            // Submit form without page reload using AJAX
            const form = this.form;
            const formData = new FormData(form);
            
            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Refresh key parts of the page without full reload
                location.reload(); // For simplicity, we'll reload the page
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    </script>
    
</body>
</html>