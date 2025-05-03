<?php
require_once __DIR__ . '/../../config/init.php';

// Check if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Une erreur s\'est produite',
    'wishlist_count' => 0
];

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vous devez être connecté pour gérer vos favoris';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($isAjax) {
    try {
        // Delete all wishlist items for this user
        $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Clear session wishlist
        $_SESSION['wishlist'] = [];
        $_SESSION['wishlist_count'] = 0;
        
        $response = [
            'success' => true,
            'message' => 'Votre liste de favoris a été vidée',
            'wishlist_count' => 0
        ];
    } catch (Exception $e) {
        $response['message'] = 'Erreur lors de la suppression: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;