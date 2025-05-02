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
        $products_stmt = $db->prepare("SELECT op.*, p.title as name, p.image 
                          FROM order_product op
                          JOIN product p ON op.product_id = p.id_product
                          WHERE op.order_id = ?");
        $products_stmt->execute([$order_id]);
        $order_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get order information
        $order_stmt = $db->prepare("SELECT o.*, u.name as customer_name, u.address, u.phone, u.email
                   FROM orders o
                   JOIN user u ON o.user_id = u.id_user
                   WHERE o.id_order = ?");
        $order_stmt->execute([$order_id]);
        $order_info = $order_stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error retrieving order details: " . $e->getMessage();
    }
}

// Get all orders with customer info
try {
    $orders_stmt = $db->prepare("SELECT o.id_order as order_id, o.status, o.total_price, o.order_date, u.name as customer_name
                   FROM orders o
                   JOIN user u ON o.user_id = u.id_user
                   ORDER BY o.order_date DESC");
    $orders_stmt->execute();
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error retrieving orders: " . $e->getMessage();
    $orders = [];
}
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
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-3">Order Management</h1>
                
                <?php if (isset($status_message)): ?>
                    <div class="alert alert-success"><?php echo $status_message; ?></div>
                <?php endif; ?>
                
                <!-- Orders Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">All Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Total Price</th>
                                        <th>Order Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo ($order['status'] == 'Pending') ? 'warning' : 
                                                     (($order['status'] == 'Shipped') ? 'info' : 
                                                     (($order['status'] == 'Completed') ? 'success' : 'secondary')); 
                                            ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <a href="?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Order Details Section (shown when an order is selected) -->
                <?php if (isset($order_info)): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Order #<?php echo $order_info['id_order']; ?> Details</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-secondary">Back to All Orders</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order_info['customer_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order_info['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_info['phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($order_info['address']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($order_info['order_date'])); ?></p>
                                <p>
                                    <strong>Status:</strong> 
                                    <span class="badge bg-<?php 
                                        echo ($order_info['status'] == 'Pending') ? 'warning' : 
                                             (($order_info['status'] == 'Shipped') ? 'info' : 
                                             (($order_info['status'] == 'Completed') ? 'success' : 'secondary')); 
                                    ?>">
                                        <?php echo htmlspecialchars($order_info['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Total Price:</strong> $<?php echo number_format($order_info['total_price'], 2); ?></p>
                            </div>
                        </div>
                        
                        <!-- Update Status Form -->
                        <form method="post" class="mb-4">
                            <input type="hidden" name="order_id" value="<?php echo $order_info['id_order']; ?>">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="new_status" class="col-form-label">Update Status:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="new_status" id="new_status" class="form-select">
                                        <option value="Pending" <?php echo ($order_info['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo ($order_info['status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Shipped" <?php echo ($order_info['status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Completed" <?php echo ($order_info['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo ($order_info['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Order Products -->
                        <h6>Products in Order</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($product['image'])): ?>
                                                <img src="../img/products/<?php echo $product['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     width="50" class="me-3">
                                                <?php endif; ?>
                                                <div><?php echo htmlspecialchars($product['name']); ?></div>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo $product['quantity']; ?></td>
                                        <td>$<?php echo number_format($product['price'] * $product['quantity'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th>$<?php echo number_format($order_info['total_price'], 2); ?></th>
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