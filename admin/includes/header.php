<?php 

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/init.php';
// Redirect if not admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Souk Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/admin.css" />
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
        /* Improved Header */
        .navbar {
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15);
            background: linear-gradient(to right, var(--primary-color), #224abe) !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15);
        }
        
        /* Improved Sidebar */
        .sidebar {
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15);
            background-color: white;
            min-height: 100vh;
            position: fixed;
            z-index: 1;
            padding-top: 70px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: var(--dark-color);
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 0.35rem;
            margin: 0.2rem 0.8rem;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--light-color);
            color: var(--primary-color);
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .05);
        }
        
        .sidebar .nav-link i {
            color: var(--primary-color);
            opacity: 0.8;
            width: 1.5rem;
            text-align: center;
        }
        
        .sidebar-divider {
            border-top: 1px solid rgba(0,0,0,.1);
            margin: 1rem;
        }
        
        .sidebar-heading {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--dark-color);
            opacity: 0.7;
            padding: 0.5rem 1.5rem;
            margin-top: 1rem;
        }
        
        .main-content {
            margin-top: 70px;
            padding-top: 1.5rem;
        }

        /* Admin profile button */
        .admin-profile {
            display: flex;
            align-items: center;
            color: white !important;
            font-weight: 600;
        }
        
        .admin-profile .avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            margin-right: 0.5rem;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Improved Header -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store me-2"></i>E-Souk Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle admin-profile" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>public/pages/index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Improved Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-sticky">
                    <div class="sidebar-heading">Core</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-heading">Store Management</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'product.php' ? 'active' : ''; ?>" href="product.php">
                                <i class="fas fa-box"></i>
                                Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                                <i class="fas fa-tags"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                                <i class="fas fa-shopping-cart"></i>
                                Orders
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-heading">Users</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                                <i class="fas fa-users"></i>
                                Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">