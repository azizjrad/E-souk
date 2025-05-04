<?php
// Database connection and session start
session_start();
require_once '../config/init.php';
$db = Database::getInstance();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    try {
        $update_stmt = $db->prepare("UPDATE orders SET status = ? WHERE id_order = ?");
        $update_stmt->execute([$new_status, $order_id]);
        $status_message = "Order #$order_id status updated to $new_status";
    } catch (PDOException $e) {
        $status_message = "Error updating order status: " . $e->getMessage();
    }
}

// Get order details if an order is selected
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    try {
        // Get products in the order
        $products_stmt = $db->prepare("
            SELECT op.*, p.title as name, p.image, op.unit_price 
            FROM order_product op
            JOIN product p ON op.product_id = p.id_product
            WHERE op.order_id = ?
        ");
        $products_stmt->execute([$order_id]);
        $order_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get order information
        $order_stmt = $db->prepare("
            SELECT o.*, u.name as customer_name, u.address, u.phone, u.email
            FROM orders o
            JOIN user u ON o.user_id = u.id_user
            WHERE o.id_order = ?
        ");
        $order_stmt->execute([$order_id]);
        $order_info = $order_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Count total items in order
        $total_items = 0;
        foreach ($order_products as $product) {
            $total_items += $product['quantity'];
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving order details: " . $e->getMessage();
    }
}

// Build query conditions for filtering
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(o.id_order LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total orders for pagination
try {
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM orders o 
        JOIN user u ON o.user_id = u.id_user
        $where_clause
    ";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_orders / $limit);
} catch (PDOException $e) {
    $error_message = "Error counting orders: " . $e->getMessage();
    $total_orders = 0;
    $total_pages = 1;
}

// Get all orders with customer info
try {
    $orders_sql = "
        SELECT o.id_order as order_id, o.status, o.total_price, o.order_date, u.name as customer_name, 
               u.email, COUNT(op.product_id) as item_count, SUM(op.quantity) as total_items
        FROM orders o
        JOIN user u ON o.user_id = u.id_user
        LEFT JOIN order_product op ON o.id_order = op.order_id
        $where_clause
        GROUP BY o.id_order
        ORDER BY o.order_date DESC
        LIMIT $limit OFFSET $offset
    ";
    $orders_stmt = $db->prepare($orders_sql);
    $orders_stmt->execute($params);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error retrieving orders: " . $e->getMessage();
    $orders = [];
}

// Get list of available statuses for filter
$statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/admin.css" />
    <style>
        .order-summary-card {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        .product-img-container {
            width: 60px;
            height: 60px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .product-img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                    <h1 class="h2">Order Management</h1>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        
                    </div>
                </div>
                
                <?php if (isset($status_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $status_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Order Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Total Orders</h6>
                                <h3><?php echo $total_orders; ?></h3>
                            </div>
                        </div>
                    </div>
                    <?php
                    // Get orders by status count
                    $status_counts = [
                        'Pending' => 0,
                        'Processing' => 0,
                        'Shipped' => 0,
                        'Completed' => 0,
                        'Cancelled' => 0
                    ];
                    
                    try {
                        $status_stmt = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
                        while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
                            if (isset($status_counts[$row['status']])) {
                                $status_counts[$row['status']] = $row['count'];
                            }
                        }
                    } catch (PDOException $e) {
                        // Silently fail
                    }
                    ?>
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <h6 class="text-muted">Pending Orders</h6>
                                <h3><?php echo $status_counts['Pending']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100" style="border-left-color: #0dcaf0;">
                            <div class="card-body">
                                <h6 class="text-muted">Processing Orders</h6>
                                <h3><?php echo $status_counts['Processing']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100" style="border-left-color: #198754;">
                            <div class="card-body">
                                <h6 class="text-muted">Completed Orders</h6>
                                <h3><?php echo $status_counts['Completed']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($status_filter === $status ? 'selected' : ''); ?>>
                                            <?php echo $status; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="orders.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td><?php echo $order['total_items'] ?? 0; ?> items</td>
                                        <td>
                                            <?php 
                                                $status_color = 'secondary';
                                                $status_icon = 'circle-question';
                                                
                                                switch($order['status']) {
                                                    case 'Pending':
                                                        $status_color = 'warning';
                                                        $status_icon = 'clock';
                                                        break;
                                                    case 'Processing':
                                                        $status_color = 'info';
                                                        $status_icon = 'gear';
                                                        break;
                                                    case 'Shipped':
                                                        $status_color = 'primary';
                                                        $status_icon = 'truck';
                                                        break;
                                                    case 'Completed':
                                                        $status_color = 'success';
                                                        $status_icon = 'check-circle';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_color = 'danger';
                                                        $status_icon = 'times-circle';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge bg-<?php echo $status_color; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($order['total_price'], 2); ?> DT</td>
                                        <td>
                                            <?php 
                                                $order_date = new DateTime($order['order_date']);
                                                echo $order_date->format('M d, Y'); 
                                                echo '<br><small class="text-muted">' . $order_date->format('H:i') . '</small>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?order_id=<?php echo $order['order_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <p>No orders found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Orders pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">Previous</a>
                                </li>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . '">' . $i . '</a></li>';
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Details Section (shown when an order is selected) -->
                <?php if (isset($order_info)): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Order #<?php echo $order_info['id_order']; ?> Details</h5>
                            <small class="text-muted">Placed on <?php echo date('F d, Y \a\t H:i', strtotime($order_info['order_date'])); ?></small>
                        </div>
                        <div>
                            <a href="print_order.php?id=<?php echo $order_info['id_order']; ?>" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                <i class="fas fa-print"></i> Print Invoice
                            </a>
                            <a href="orders.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? (!empty($search) ? '&' : '?') . 'status=' . urlencode($status_filter) : ''; ?><?php echo (!empty($search) || !empty($status_filter)) ? '&' : '?'; ?>page=<?php echo $page; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Back to All Orders
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-3 text-muted">Customer Information</h6>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order_info['customer_name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order_info['email']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_info['phone']); ?></p>
                                        <p class="mb-0"><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order_info['address'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-3 text-muted">Order Summary</h6>
                                        <p><strong>Order ID:</strong> #<?php echo $order_info['id_order']; ?></p>
                                        <p><strong>Items:</strong> <?php echo $total_items; ?> items</p>
                                        <p>
                                            <strong>Status:</strong> 
                                            <?php 
                                                $status_color = 'secondary';
                                                $status_icon = 'circle-question';
                                                
                                                switch($order_info['status']) {
                                                    case 'Pending':
                                                        $status_color = 'warning';
                                                        $status_icon = 'clock';
                                                        break;
                                                    case 'Processing':
                                                        $status_color = 'info';
                                                        $status_icon = 'gear';
                                                        break;
                                                    case 'Shipped':
                                                        $status_color = 'primary';
                                                        $status_icon = 'truck';
                                                        break;
                                                    case 'Completed':
                                                        $status_color = 'success';
                                                        $status_icon = 'check-circle';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_color = 'danger';
                                                        $status_icon = 'times-circle';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge bg-<?php echo $status_color; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                                <?php echo htmlspecialchars($order_info['status']); ?>
                                            </span>
                                        </p>
                                        <p class="h5 mb-0"><strong>Total:</strong> <?php echo number_format($order_info['total_price'], 2); ?> DT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Update Status Form -->
                        <form method="post" class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">Update Order Status</h6>
                                <input type="hidden" name="order_id" value="<?php echo $order_info['id_order']; ?>">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <select name="new_status" id="new_status" class="form-select">
                                            <option value="Pending" <?php echo ($order_info['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo ($order_info['status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipped" <?php echo ($order_info['status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Completed" <?php echo ($order_info['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo ($order_info['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Update Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Order Products -->
                        <h6 class="mb-3">Products in Order</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th width="15%">Unit Price</th>
                                        <th width="15%">Quantity</th>
                                        <th width="15%">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($product['image'])): ?>
                                                <div class="product-img-container me-3">
                                                    <img src="../root_uploads/products/<?php echo $product['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div><?php echo htmlspecialchars($product['name']); ?></div>
                                                    <small class="text-muted">ID: <?php echo $product['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($product['unit_price'], 2); ?> DT</td>
                                        <td><?php echo $product['quantity']; ?></td>
                                        <td><?php echo number_format($product['unit_price'] * $product['quantity'], 2); ?> DT</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th><?php echo number_format($order_info['total_price'], 2); ?> DT</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </main>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>