<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/init.php';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "public/user/login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: " . ROOT_URL . "public/user/login.php");
    exit();
}

// Get user statistics
$orderStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$orderStmt->execute([$user_id]);
$orderCount = $orderStmt->fetchColumn() ?: 0;

$wishlistStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$wishlistStmt->execute([$user_id]);
$wishlistCount = $wishlistStmt->fetchColumn() ?: 0;

$page_title = "Profile - E-Souk Tounsi";
$description = "Profile page of the user. View and edit your personal information, orders, and wishlist.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include ROOT_PATH . '/public/templates/header.php'; ?>
    <link rel="stylesheet" href="<?= ROOT_URL ?>public/assets/css/profile.css">
    <style>
        /* User dashboard layout */
        .user-dashboard {
            display: flex;
            min-height: calc(100vh - 150px);
        }
        .sidebar-container {
            width: 250px;
            flex-shrink: 0;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .content-container {
            flex-grow: 1;
            padding: 20px;
        }
        @media (max-width: 767px) {
            .user-dashboard {
                flex-direction: column;
            }
            .sidebar-container {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
        }
        
        /* Profile specific styles */
        .profile-header {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
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
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <div class="profile-header shadow-sm">
                    <div class="row">
                        <div class="col-md-9">
                            <h2><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                            <div class="d-flex mt-3 gap-2 flex-wrap">
                                <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
                                <a href="edit-profile.php" class="btn btn-outline-secondary">Change Password</a>
                                <?php if ($user['role'] == 'admin'): ?>
                                    <a href="<?= ROOT_URL ?>admin/index.php" class="btn btn-danger">
                                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100 stats-card shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-bag-check fs-1 text-primary mb-2"></i>
                                <h3><?= $orderCount ?></h3>
                                <p class="text-muted mb-0">Total Orders</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="orders.php" class="text-decoration-none">View Orders</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center h-100 stats-card shadow-sm">
                            <div class="card-body">
                                <i class="bi bi-heart fs-1 text-danger mb-2"></i>
                                <h3><?= $wishlistCount ?></h3>
                                <p class="text-muted mb-0">Wishlist Items</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="wishlist.php" class="text-decoration-none">View Wishlist</a>
                            </div>
                        </div>
                    </div>
                   
                
                <!-- User Information Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Personal Information</h5>
                        <a href="edit-profile.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Full Name:</div>
                            <div class="col-md-9"><?= htmlspecialchars($user['name']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Email:</div>
                            <div class="col-md-9"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Phone:</div>
                            <div class="col-md-9"><?= htmlspecialchars($user['phone'] ?: 'Not provided') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Address:</div>
                            <div class="col-md-9"><?= htmlspecialchars($user['address'] ?: 'Not provided') ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Role:</div>
                            <div class="col-md-9"><span class="badge bg-info"><?= htmlspecialchars($user['role']) ?></span></div>
                        </div>
                    </div>
                </div>
                
             
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/public/templates/footer.php'; ?>
</body>
</html>
