<?php
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "public/user/login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user information for the sidebar
try {
    $userQuery = "SELECT * FROM user WHERE id_user = ?";
    $userStmt = $db->prepare($userQuery);
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Erreur lors de la récupération de l'utilisateur : " . $e->getMessage();
    exit();
}

// Fetch orders for the current user using PDO
try {
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
$page_title = "Mes Commandes - E-Souk Tounsi";
$description = "Consultez vos commandes passées, suivez leur statut et gérez vos achats sur E-Souk Tounsi.";

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include ROOT_PATH . '/public/templates/header.php'; ?>
    
    <style>
        :root {
            --primary-color: #2b3684;
            --primary-light: #3a45a0;
            --primary-dark: #1e2760;
            --accent-color: #f5a623;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --text-dark: #343a40;
            --text-light: #6c757d;
            --white: #ffffff;
        }
        
        /* User dashboard layout */
        .user-dashboard {
            display: flex;
            min-height: calc(100vh - 150px);
            background-color: #f5f7fa;
        }
        
        .sidebar-container {
            width: 250px;
            flex-shrink: 0;
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .content-container {
            flex-grow: 1;
            padding: 25px;
        }
        
        /* Orders page specific styles */
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }
        
        .orders-header h1 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
            font-size: 1.8rem;
        }
        
        /* Table styling */
        .table {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            overflow: hidden;
            background-color: var(--white);
        }
        
        .table thead {
            background-color: rgba(43, 54, 132, 0.03);
        }
        
        .table thead th {
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 14px 16px;
        }
        
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .table tbody tr:hover {
            background-color: rgba(43, 54, 132, 0.01);
        }
        
        /* Order status styling */
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: capitalize;
            text-align: center;
            min-width: 100px;
        }
        
        .status-pending {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .status-processing {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-shipped {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-delivered {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-cancelled {
            background-color: #fbe9e7;
            color: #d32f2f;
        }
        
        .orders-list {
            margin: 0;
        }
        
        .empty-orders {
            text-align: center;
            padding: 50px 0;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin: 20px 0;
        }
        
        .empty-orders p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: var(--text-light);
        }
        
        /* Button styles */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(43, 54, 132, 0.2);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background-color: transparent;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .btn-small {
            padding: 5px 12px;
            font-size: 0.875rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 767px) {
            .user-dashboard {
                flex-direction: column;
            }
            
            .sidebar-container {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            
            .content-container {
                padding: 15px;
            }
            
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 15px;
                border: 1px solid var(--border-color);
                border-radius: 8px;
                overflow: hidden;
            }
            
            .table td {
                text-align: right;
                padding: 10px 15px;
                position: relative;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            .table td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                font-weight: 600;
                color: var(--primary-color);
            }
            
            .table td:last-child {
                border-bottom: none;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include ROOT_PATH . '/public/templates/navbar.php'; ?>
    
    <div class="container-fluid p-0">
        <div class="user-dashboard">
            <!-- Sidebar Container -->
            <div class="sidebar-container">
                <?php include ROOT_PATH . '/public/user/includes/sidebar.php'; ?>
            </div>
            
            <!-- Content Container -->
            <div class="content-container">
                <div class="container-fluid px-0">
                    <div class="orders-header">
                        <h1>Mes Commandes</h1>
                        <a href="<?php echo ROOT_URL; ?>public/pages/product.php" class="btn btn-outline-primary">Continuer les Achats</a>
                    </div>
                    
                    <?php if (!empty($orders)): ?>
                        <div class="orders-list">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>N° de Commande</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td data-label="N° de Commande">#<?php echo $order['id_order']; ?></td>
                                            <td data-label="Date"><?php echo date('j F Y', strtotime($order['order_date'])); ?></td>
                                            <td data-label="Statut">
                                                <span class="status status-<?php echo strtolower($order['status']); ?>">
                                                    <?php 
                                                    // Translate status
                                                    $status = $order['status'];
                                                    switch($status) {
                                                        case 'Pending':
                                                            echo 'En attente';
                                                            break;
                                                        case 'Processing':
                                                            echo 'En cours';
                                                            break;
                                                        case 'Shipped':
                                                            echo 'Expédié';
                                                            break;
                                                        case 'Delivered':
                                                            echo 'Livré';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'Annulé';
                                                            break;
                                                        default:
                                                            echo $status;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td data-label="Total"><?php echo number_format($order['total_price'], 2); ?> DT</td>
                                            <td data-label="Action">
                                                <a href="<?php echo ROOT_URL; ?>/user/order_details.php?id=<?php echo $order['id_order']; ?>" class="btn btn-small btn-primary">Voir Détails</a>
                                                <?php if ($order['status'] == 'Pending'): ?>
                                                    <a href="<?php echo ROOT_URL; ?>/user/cancel_order.php?id=<?php echo $order['id_order']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande?')">Annuler</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-orders">
                            <p>Vous n'avez pas encore passé de commande.</p>
                            <a href="<?php echo ROOT_URL; ?>public/pages/product.php" class="btn btn-primary">Commencer vos Achats</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include ROOT_PATH . '/public/templates/footer.php'; ?>
</body>
</html>