<?php
// Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/init.php';

?>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?php echo ROOT_URL; ?>public/pages/index.php">
            <img src="<?php echo ROOT_URL; ?>public/assets/images/logo.png" alt="E-Souk Logo" height="40">
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav me-auto">
            <!-- Categories Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" >Categories</a>
              <ul class="dropdown-menu">
                
              <?php
    // Fetch categories from database
    $stmt = $db->prepare("SELECT id_category, name FROM category ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display first 5 categories directly
    $categoriesCount = count($categories);
    $displayLimit = 3;
    
    for ($i = 0; $i < min($displayLimit, $categoriesCount); $i++) {
      echo '<li><a class="dropdown-item" href="' . ROOT_URL . 'public/pages/category-details.php?id=' . $categories[$i]['id_category'] . '">' . 
           htmlspecialchars($categories[$i]['name']) . '</a></li>';
    }
    
    // If there are more than 5 categories, create a nested dropdown
    if ($categoriesCount > $displayLimit) {
      echo '<li class="dropdown-submenu">';
      echo '<a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">Autres catégories</a>';
      echo '<ul class="dropdown-menu dropdown-submenu">';
      
      for ($i = $displayLimit; $i < $categoriesCount; $i++) {
        echo '<li><a class="dropdown-item" href="' . ROOT_URL . 'public/pages/category-details.php?id=' . $categories[$i]['id_category'] . '">' . 
             htmlspecialchars($categories[$i]['name']) . '</a></li>';
      }
      
      echo '</ul></li>';
    }
    ?>
                
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>public/pages/categories.php">Tous les categories</a></li>
              </ul>
            </li>
<li class="nav-item">
              <a class="nav-link" href="<?php echo ROOT_URL; ?>public/pages/product.php">Produits</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ROOT_URL; ?>public/pages/about.php">À PROPOS</a>
            </li>

            <!-- FAQ Link -->
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ROOT_URL; ?>public/user/faq.php">FAQ</a>
            </li>
            
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ROOT_URL; ?>public/pages/vision.php">COMING SOON!</a>
            </li>
            
            
          </ul>

            <!-- Search Bar -->
            <form class="d-flex mx-lg-3" action="<?php echo ROOT_URL; ?>public/templates/search.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" placeholder="Rechercher...">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- User Section -->
            <div class="d-flex">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link me-3 dropdown-toggle" data-bs-toggle="dropdown" id="userDropdown">
                        <i class="fas fa-user"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="userDropdown">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- User is logged in -->
                            <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>public/user/profile.php">Mon Compte</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>core/logout.php">Déconnexion</a></li>
                        <?php else: ?>
                            <!-- User is not logged in -->
                            <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>public/user/login.php">Connexion</a></li>
                            <li><a class="dropdown-item" href="<?php echo ROOT_URL; ?>public/user/register.php">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <a href="<?php echo ROOT_URL; ?>public/pages/contact.php" class="nav-link me-3">
                    <i class="fas fa-envelope"></i>
                </a>

                <!-- Wishlist -->
                <a href="<?php echo ROOT_URL; ?>public/pages/wishlist.php" class="nav-link me-3 position-relative">
                    <i class="fas fa-heart"></i>
                    <?php if (isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count">
                            <?php echo count($_SESSION['wishlist']); ?>
                        </span>
                    <?php else: ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count" style="display: none;"></span>
                    <?php endif; ?>
                </a>

                <!-- Cart -->
                <a href="<?php echo ROOT_URL; ?>public/pages/cart.php" class="nav-link position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    <?php else: ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="display: none;"></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</nav>