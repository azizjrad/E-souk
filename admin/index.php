<?php
session_start();


if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/init.php';
$db = Database::getInstance();


$stmt = $db->prepare("SELECT COUNT(*) as total FROM product");
$stmt->execute();
$totalProducts = $stmt->fetchColumn();

// Total orders
$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$totalOrders = $stmt->fetchColumn();

// Pending orders
$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$stmt->execute();
$pendingOrders = $stmt->fetchColumn();

// Total customers
$stmt = $db->prepare("SELECT COUNT(*) as total FROM user WHERE role = 'customer'");
$stmt->execute();
$totalCustomers = $stmt->fetchColumn();

// Total sales amount
$stmt = $db->prepare("SELECT COALESCE(SUM(total_price), 0) as total_sales FROM orders WHERE status != 'Cancelled'");
$stmt->execute();
$totalSales = $stmt->fetchColumn();

// Recent orders
$stmt = $db->prepare("SELECT o.id_order as order_id, o.order_date, o.status, 
                       u.name as customer_name, o.total_price as total_amount 
                       FROM orders o 
                       JOIN user u ON o.user_id = u.id_user 
                       ORDER BY o.order_date DESC LIMIT 5");
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Souk Tableau de Bord Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php include('includes/header.php'); ?>
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>
    
            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4">Tableau de Bord Admin</h1>
                
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-box fa-2x mb-2 text-primary"></i>
                                <h5 class="card-title">Produits Totaux</h5>
                                <p class="card-text fs-4"><?php echo $totalProducts; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-shopping-cart fa-2x mb-2 text-success"></i>
                                <h5 class="card-title">Commandes Totales</h5>
                                <p class="card-text fs-4"><?php echo $totalOrders; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x mb-2 text-warning"></i>
                                <h5 class="card-title">Commandes en Attente</h5>
                                <p class="card-text fs-4"><?php echo $pendingOrders; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x mb-2 text-info"></i>
                                <h5 class="card-title">Clients Totaux</h5>
                                <p class="card-text fs-4"><?php echo $totalCustomers; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="fas fa-money-bill-wave fa-2x mb-2 text-success"></i>
                                <h5 class="card-title">Revenus Totaux</h5>
                                <p class="card-text fs-4"><?php echo number_format($totalSales, 2); ?> DT</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Liens Rapides</h2>
                    </div>
                    <div class="card-body">
                        <a href="product.php" class="btn btn-primary me-2 mb-2">Gérer les Produits</a>
                        <a href="categories.php" class="btn btn-primary me-2 mb-2">Gérer les Catégories</a>
                        <a href="orders.php" class="btn btn-primary me-2 mb-2">Voir Toutes les Commandes</a>
                        <a href="users.php" class="btn btn-primary me-2 mb-2">Gestion des Clients</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Commandes Récentes</h2>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">Voir Toutes les Commandes →</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Commande</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2); ?> DT</td>
                                            <td>
                                                <?php 
                                                $statusClass = 'secondary';
                                                if (strtolower($order['status']) == 'pending') $statusClass = 'warning';
                                                if (strtolower($order['status']) == 'completed') $statusClass = 'success';
                                                if (strtolower($order['status']) == 'cancelled') $statusClass = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="orders.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
    
</body>
</html>