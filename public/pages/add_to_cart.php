<?php
header('Content-Type: application/json');

try {
    session_start();
    require_once __DIR__ . '/../../config/init.php';
    
    $response = ['success' => false, 'message' => 'Invalid request', 'cart_count' => 0];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID', 'cart_count' => 0]);
        exit;
    }

    // Initialize cart if needed
    $_SESSION['cart'] = $_SESSION['cart'] ?? [];

    // Verify product exists and has stock
    $stmt = $db->prepare("SELECT * FROM product WHERE id_product = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product || $product['stock'] <= 0) {
        $message = $product ? 'Product out of stock' : 'Product not found';
        echo json_encode(['success' => false, 'message' => $message, 'cart_count' => 0]);
        exit;
    }

    // Add to cart or update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'quantity' => $quantity,
            'name' => $product['title'],
            'price' => $product['price'],
            'image' => $product['image']
        ];
        
        // Add category if available
        if (!empty($product['category_id'])) {
            $stmt = $db->prepare("SELECT name FROM category WHERE id_category = ?");
            $stmt->execute([$product['category_id']]);
            if ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['cart'][$product_id]['category'] = $category['name'];
            }
        }
    }

    // Update database cart for logged-in users
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Get or create cart
        $stmt = $db->prepare("SELECT id_cart FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $cart_id = $cart['id_cart'] ?? null;
        if (!$cart_id) {
            $stmt = $db->prepare("INSERT INTO cart (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $db->lastInsertId();
        }
        
        // Update cart item
        $stmt = $db->prepare("SELECT * FROM cart_product WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cart_id, $product_id]);
        
        if ($stmt->fetch()) {
            $stmt = $db->prepare("UPDATE cart_product SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $cart_id, $product_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO cart_product (cart_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cart_id, $product_id, $quantity, $product['price']]);
        }
    }
    
    // Calculate total items in cart
    $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'cart_count' => 0
    ]);
}
exit;
