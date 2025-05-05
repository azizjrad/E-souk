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

// Get unread notifications count - add this if you want notification badge functionality
$unread_notifications = 0; // Replace with actual data from database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Souk Admin - <?php echo ucfirst(str_replace('.php', '', $current_page)); ?></title>
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

        /* Enhanced Admin Header Styles - Uses existing CSS variables */
        .e-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            height: var(--header-height);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            z-index: var(--z-fixed);
            display: flex;
            align-items: center;
            padding: 0 1rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .e-header-brand {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            margin-right: auto;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .e-header-brand:hover {
            color: white;
        }
        
        .e-header-brand i {
            width: 38px;
            height: 38px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1);
            margin-right: 0.75rem;
            font-size: 1.25rem;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .e-header-brand:hover i {
            transform: scale(1.05);
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .e-header-toggler {
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .e-header-toggler:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .e-header-toggler:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }
        
        /* Hamburger menu icon with animation */
        .e-hamburger {
            position: relative;
            width: 18px;
            height: 2px;
            background: white;
            transition: var(--transition);
        }
        
        .e-hamburger::before,
        .e-hamburger::after {
            content: '';
            position: absolute;
            width: 18px;
            height: 2px;
            background: white;
            transition: var(--transition);
        }
        
        .e-hamburger::before {
            transform: translateY(-6px);
        }
        
        .e-hamburger::after {
            transform: translateY(6px);
        }
        
        .e-header-toggler.active .e-hamburger {
            background: transparent;
        }
        
        .e-header-toggler.active .e-hamburger::before {
            transform: rotate(45deg);
        }
        
        .e-header-toggler.active .e-hamburger::after {
            transform: rotate(-45deg);
        }
        
        /* Header actions and profile */
        .e-header-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .e-header-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: var(--transition);
        }
        
        .e-header-action:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .e-header-action:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }
        
        .e-header-action .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            background-color: var(--danger);
            font-size: 0.65rem;
            font-weight: 600;
        }
        
        .e-header-profile {
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: var(--radius);
            color: white;
            padding: 0.375rem;
            padding-right: 0.75rem;
            margin-left: 0.5rem;
            transition: var(--transition);
        }
        
        .e-header-profile:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .e-header-profile:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }
        
        .e-header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
            transition: var(--transition);
            border: 2px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }
        
        .e-header-profile:hover .e-header-avatar {
            transform: scale(1.1);
        }
        
        .e-header-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .e-header-user {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .e-header-name {
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .e-header-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        
        /* Theme toggle switch */
        .e-theme-toggle {
            position: relative;
            width: 46px;
            height: 24px;
            margin-left: 0.5rem;
        }
        
        .e-theme-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .e-theme-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .e-theme-slider:before {
            position: absolute;
            content: "☀️";
            display: flex;
            align-items: center;
            justify-content: center;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: var(--transition);
            font-size: 0.75rem;
        }
        
        .e-theme-toggle input:checked + .e-theme-slider {
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .e-theme-toggle input:checked + .e-theme-slider:before {
            transform: translateX(22px);
            content: "🌙";
            background-color: #1a202c;
        }
        
        /* Dropdown styles */
        .e-dropdown-menu {
            min-width: 240px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            animation: fadeInDown 0.2s ease-out;
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .e-dropdown-menu::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 16px;
            width: 12px;
            height: 12px;
            background: white;
            border-top: 1px solid var(--border);
            border-left: 1px solid var(--border);
            transform: rotate(45deg);
        }
        
        .e-dropdown-menu .dropdown-item {
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }
        
        .e-dropdown-menu .dropdown-item:hover {
            background-color: var(--primary-bg);
            border-left-color: var(--primary);
            transform: translateX(3px);
        }
        
        .e-dropdown-menu .dropdown-item i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .e-header-user {
                display: none;
            }
            
            .e-header-profile {
                padding-right: 0.375rem;
            }
            
            .e-theme-toggle,
            .e-header-action.d-sm-none {
                display: none;
            }
        }
        
        /* Dark mode specific styles */
        .dark-mode .e-dropdown-menu {
            background-color: var(--dark-800);
            border-color: var(--dark-600);
        }
        
        .dark-mode .e-dropdown-menu::before {
            background-color: var(--dark-800);
            border-color: var(--dark-600);
        }
        
        .dark-mode .e-dropdown-menu .dropdown-item {
            color: var(--light);
        }
        
        .dark-mode .e-dropdown-menu .dropdown-item:hover {
            background-color: var(--dark-700);
        }
        
        .dark-mode .e-dropdown-menu .dropdown-divider {
            border-color: var(--dark-600);
        }
        
        /* Update sidebar to align with new header */
        .sidebar {
            top: var(--header-height);
            height: calc(100vh - var(--header-height));
        }
        
        /* Update main content area to work with new header */
        .main-content {
            padding-top: calc(var(--header-height) + 1rem);
        }
    </style>
</head>
<body>
    <!-- Enhanced Header -->
    <header class="e-header">
        <!-- Sidebar toggler button -->
        <button type="button" class="e-header-toggler" id="sidebarToggler">
            <span class="e-hamburger"></span>
        </button>
        
        <!-- Brand/logo -->
        <a href="index.php" class="e-header-brand">
            <i class="fas fa-store-alt"></i>
            <span>E-Souk Admin</span>
        </a>
        
        <!-- Right side actions -->
        <div class="e-header-actions">
            <!-- Quick action buttons - hidden on small screens -->
            <button type="button" class="e-header-action d-none d-md-flex" title="Rechercher">
                <i class="fas fa-search"></i>
            </button>
            
            <button type="button" class="e-header-action d-none d-md-flex" title="Actualiser" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i>
            </button>
            
            <a href="<?php echo ROOT_URL; ?>" target="_blank" class="e-header-action d-none d-md-flex" title="Visiter le site">
                <i class="fas fa-external-link-alt"></i>
            </a>
            
            <!-- Notifications with badge -->
            <div class="dropdown">
                <button class="e-header-action" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_notifications > 0): ?>
                    <span class="badge rounded-pill"><?php echo $unread_notifications > 9 ? '9+' : $unread_notifications; ?></span>
                    <?php endif; ?>
                </button>
                
                <div class="dropdown-menu dropdown-menu-end e-dropdown-menu p-0" aria-labelledby="notificationsDropdown">
                    <h6 class="dropdown-header">Notifications</h6>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-shopping-bag text-warning"></i>
                        Nouvelle commande reçue
                        <span class="small d-block text-muted mt-1">À l'instant</span>
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-user-plus text-success"></i>
                        Nouvel utilisateur inscrit
                        <span class="small d-block text-muted mt-1">Il y a 2 heures</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center" href="#">
                        <small>Voir toutes les notifications</small>
                    </a>
                </div>
            </div>
            
            <!-- Theme toggle switch -->
            <label class="e-theme-toggle d-none d-md-block">
                <input type="checkbox" id="themeToggle">
                <span class="e-theme-slider"></span>
            </label>
            
            <!-- User profile dropdown -->
            <div class="dropdown">
                <button class="e-header-profile dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="e-header-avatar">
                        <?php 
                        $user_initial = substr($_SESSION['user_name'] ?? 'A', 0, 1); 
                        if (!empty($_SESSION['user_avatar'])): ?>
                            <img src="<?php echo $_SESSION['user_avatar']; ?>" alt="Avatar utilisateur">
                        <?php else: 
                            echo $user_initial;
                        endif; ?>
                    </div>
                    <div class="e-header-user">
                        <div class="e-header-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></div>
                        <div class="e-header-role">Administrateur</div>
                    </div>
                </button>
                
                <div class="dropdown-menu dropdown-menu-end e-dropdown-menu" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user-circle"></i>
                        Mon Profil
                    </a>
                    <a class="dropdown-item" href="<?php echo ROOT_URL; ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        Visiter le Site
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Improved Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-sticky">
                    <div class="sidebar-heading">Base</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Tableau de bord
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-heading">Gestion de la boutique</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'product.php' ? 'active' : ''; ?>" href="product.php">
                                <i class="fas fa-box"></i>
                                Produits
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                                <i class="fas fa-tags"></i>
                                Catégories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                                <i class="fas fa-shopping-cart"></i>
                                Commandes
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-heading">Utilisateurs</div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                                <i class="fas fa-users"></i>
                                Utilisateurs
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    // Check for saved theme preference
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        body.classList.add('dark-mode');
        themeToggle.checked = true;
    }
    // Toggle theme when switch is clicked
    themeToggle.addEventListener('change', function() {
        if (this.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'true');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'false');
        }
    });

    // Sidebar toggle for mobile
    const sidebarToggler = document.getElementById('sidebarToggler');
    const sidebar = document.querySelector('.sidebar');
    sidebarToggler.addEventListener('click', function() {
        this.classList.toggle('active');
        sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = sidebarToggler.contains(event.target);
        if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            sidebarToggler.classList.remove('active');
        }
    });
});
</script>
</body>
</html>