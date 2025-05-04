<?php
session_start();
require_once __DIR__ . '/../../config/init.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php?redirect=checkout');
    exit;
}

// Redirect if cart is empty or form not submitted
if (empty($_SESSION['cart']) || empty($_POST)) {
    header('Location: cart.php');
    exit;
}

try {
    // Begin transaction for database consistency
    $db->beginTransaction();
    
    // Calculate totals
    $subtotal = 0;
    $shipping = 7; // Fixed shipping cost
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        if (is_array($item) && isset($item['price']) && isset($item['quantity'])) {
            $subtotal += $item['price'] * $item['quantity'];
        }
    }
    
    $total = $subtotal + $shipping;
    
    // Get form data
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method'] ?? 'cash'; // Get payment method with default
    
    // Create order - Fix column/values mismatch
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_price, status) 
                        VALUES (?, ?, 'Pending')");
    $stmt->execute([$user_id, $total]);
    $order_id = $db->lastInsertId();
    
    // Add order items
    foreach ($_SESSION['cart'] as $product_id => $item) {
        if (is_array($item) && isset($item['price']) && isset($item['quantity'])) {
            // Check if product is still in stock
            $checkStock = $db->prepare("SELECT stock FROM product WHERE id_product = ?");
            $checkStock->execute([$product_id]);
            $available = $checkStock->fetchColumn();
            
            if ($available < $item['quantity']) {
                throw new Exception("Sorry, product '{$item['name']}' only has {$available} units in stock.");
            }
            
            // Insert order item
            $stmt = $db->prepare("INSERT INTO order_product (order_id, product_id, quantity, unit_price) 
                                 VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
            
            // Update product stock
            $updateStock = $db->prepare("UPDATE product SET stock = stock - ? WHERE id_product = ?");
            $updateStock->execute([$item['quantity'], $product_id]);
        }
    }
    
    // Clean up the cart
    unset($_SESSION['cart']);
    
    // Commit the transaction
    $db->commit();
    
    // Redirect to order confirmation page
    $_SESSION['order_success'] = true;
    $_SESSION['order_id'] = $order_id;
    header('Location: order_confirmation.php');
    exit;
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $db->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: checkout.php'); // Add this line
    exit;
}