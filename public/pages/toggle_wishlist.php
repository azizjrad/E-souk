<?php
require_once __DIR__ . '/../../config/init.php';

header('Content-Type: application/json');

// Check if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Une erreur s\'est produite',
    'action' => '',
    'wishlist_count' => 0
];

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vous devez être connecté pour gérer vos favoris';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($isAjax) {
    // Handle toggle product in wishlist
    if (isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        
        try {
            // Check if product is already in wishlist
            $checkStmt = $db->prepare("SELECT id_wishlist FROM wishlist WHERE user_id = ? AND product_id = ?");
            $checkStmt->execute([$user_id, $product_id]);
            $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingItem) {
                // Remove from wishlist
                $deleteStmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $deleteStmt->execute([$user_id, $product_id]);
                
                $response = [
                    'success' => true,
                    'message' => 'Produit retiré des favoris',
                    'action' => 'removed'
                ];
                
                // Update session
                if (isset($_SESSION['wishlist'])) {
                    $_SESSION['wishlist'] = array_values(array_filter($_SESSION['wishlist'], function($id) use ($product_id) {
                        return (int)$id !== (int)$product_id;
                    }));
                }
            } else {
                // Add to wishlist
                $insertStmt = $db->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
                $insertStmt->execute([$user_id, $product_id]);
                
                $response = [
                    'success' => true,
                    'message' => 'Produit ajouté aux favoris',
                    'action' => 'added'
                ];
                
                // Update session
                if (!isset($_SESSION['wishlist'])) {
                    $_SESSION['wishlist'] = [];
                }
                if (!in_array($product_id, $_SESSION['wishlist'])) {
                    $_SESSION['wishlist'][] = $product_id;
                }
            }
            
            // Get updated wishlist count
            $countStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
            $countStmt->execute([$user_id]);
            $wishlist_count = $countStmt->fetchColumn();
            
            $response['wishlist_count'] = $wishlist_count;
            $_SESSION['wishlist_count'] = $wishlist_count;
            
        } catch (Exception $e) {
            $response['message'] = 'Erreur de base de données: ' . $e->getMessage();
        }
    }
    // Handle clear wishlist
    elseif (isset($_POST['clear_wishlist'])) {
        try {
            $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['wishlist'] = [];
            $_SESSION['wishlist_count'] = 0;
            
            $response = [
                'success' => true,
                'message' => 'Votre liste de favoris a été vidée',
                'action' => 'cleared',
                'wishlist_count' => 0
            ];
        } catch (Exception $e) {
            $response['message'] = 'Erreur lors de la suppression: ' . $e->getMessage();
        }
    }
}

echo json_encode($response);
exit;
