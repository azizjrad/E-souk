<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';

$page_title = "Passer commande - E-Souk Tounsi";
$page_description = "Finalisez votre commande et procédez au paiement pour vos produits artisanaux.";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php?redirect=checkout');
    exit;
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
$shipping = 7; // Fixed shipping cost of 15 DT
$item_count = 0;

foreach ($_SESSION['cart'] as $product_id => $item) {
    if (is_array($item) && isset($item['price']) && isset($item['quantity'])) {
        $subtotal += $item['price'] * $item['quantity'];
        $item_count += $item['quantity'];
    }
}

$total = $subtotal + $shipping;

// Get user information for pre-filling the form
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="../assets/css/checkout.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include '../templates/navbar.php'; ?>

    <div class="checkout-container">
        <h1 class="text-center section-title">Finaliser la commande</h1>
        <hr class="mx-auto mb-5" style="width: 60px; border: 2px solid #fcd34d">

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="max-width: 800px; margin: 0 auto 20px;">
                <strong>Error:</strong> <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-content">
            <div class="checkout-form-container">
                <form id="checkout-form" method="POST" action="process_order.php">
                    <div class="form-section">
                        <h2>Informations de livraison</h2>
                        <div class="form-group">
                            <label for="full-name">Nom complet</label>
                            <input type="text" id="full-name" name="full_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Numéro de téléphone</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Adresse</label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" >
                        </div>                      
                        <div class="form-group">
                            <label for="country">Pays</label>
                            <select id="country" name="country" required>
                                <option value="">Sélectionnez un pays</option>
                                <option value="Tunisia" selected>Tunisie</option>
                                <option value="Algeria">Algérie</option>
                                <option value="Morocco">Maroc</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Mode de paiement</h2>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="cash-delivery" name="payment_method" value="cash" checked>
                                <label for="cash-delivery">Paiement à la livraison</label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="credit-card" name="payment_method" value="card">
                                <label for="credit-card">Carte bancaire</label>
                            </div>
                        </div>
                        
                        <div id="card-details" class="hidden">
                            <div class="form-group">
                                <label for="card-number">Numéro de carte</label>
                                <input type="text" id="card-number" name="card_number">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry">Date d'expiration</label>
                                    <input type="text" id="expiry" name="expiry" placeholder="MM/AA">
                                </div>
                                
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">J'accepte les <a href="conditions.php">conditions générales de vente</a></label>
                    </div>
                    
                    <button type="submit" class="btn checkout-btn">Confirmer la commande</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Récapitulatif de la commande</h2>
                <div class="order-items">
                    <?php foreach($_SESSION['cart'] as $id => $item): ?>
                        <div class="order-item">
                            <img src="<?= !empty($item['image']) ? '../../root_uploads/products/' . htmlspecialchars($item['image']) : '../assets/images/product-placeholder.jpg' ?>" 
                                alt="<?= htmlspecialchars($item['name'] ?? '') ?>">
                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                                <p>Quantité: <?= $item['quantity'] ?></p>
                                <p class="item-price"><?= number_format($item['price'] * $item['quantity'], 2) ?> DT</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Sous-total</span>
                        <span><?= number_format($subtotal, 2) ?> DT</span>
                    </div>
                    <div class="price-row">
                        <span>Frais de livraison</span>
                        <span><?= number_format($shipping, 2) ?> DT</span>
                    </div>
                    <div class="price-row total">
                        <span>Total</span>
                        <span><?= number_format($total, 2) ?> DT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cardPayment = document.getElementById('credit-card');
        const cashPayment = document.getElementById('cash-delivery');
        const cardDetails = document.getElementById('card-details');
        
        cardPayment.addEventListener('change', function() {
            if (this.checked) {
                cardDetails.classList.remove('hidden');
            }
        });
        
        cashPayment.addEventListener('change', function() {
            if (this.checked) {
                cardDetails.classList.add('hidden');
            }
        });
    });
    </script>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/footer.php'; ?>
</body>
</html>
``` 