<style>
    /* Enhanced Sidebar Styles */
    .profile-sidebar {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .profile-header {
        background: linear-gradient(135deg, #2b3684, #3a45a0);
        padding: 20px 15px;
        color: white;
        position: relative;
    }
    
    .sidebar-profile-img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.2);
        object-fit: cover;
        margin-bottom: 10px;
    }
    
    .profile-sidebar h6 {
        font-weight: 600;
        margin-bottom: 5px;
        color: #2b3684;
    }
    
    .list-group-item-action {
        border: none;
        border-left: 4px solid transparent;
        padding: 12px 20px;
        margin: 2px 0;
        transition: all 0.3s ease;
        font-weight: 500;
        color: #495057;
    }
    
    .list-group-item-action:hover {
        background-color: rgba(43, 54, 132, 0.05);
        border-left-color: rgba(43, 54, 132, 0.5);
        color: #2b3684;
    }
    
    .list-group-item-action.active {
        background-color: rgba(43, 54, 132, 0.1);
        border-left-color: #2b3684;
        color: #2b3684;
    }
    
    .list-group-item-action i {
        width: 20px;
        text-align: center;
        margin-right: 10px;
        color: #2b3684;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .list-group-item-action.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.05);
        border-left-color: rgba(220, 53, 69, 0.5);
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>

<div class="p-0">
    <div class="bg-light py-3">
        <div class="profile-sidebar">
            <div class="profile-header text-center">
                
                <h6 class="text-white"><?php echo htmlspecialchars($user['name']); ?></h6>
                <p class="small text-white-50 mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <div class="py-2">
                <div class="list-group list-group-flush">
                    <?php
                    // Get current page filename
                    $current_page = basename($_SERVER['PHP_SELF']);
                    
                    // Define sidebar items with their URLs and icons
                    $menu_items = [
                        // Profile & Account
                        'profile.php' => ['icon' => 'fas fa-user', 'title' => 'Informations de profil'],
                        'edit-profile.php' => ['icon' => 'fas fa-edit', 'title' => 'Modifier le profil'],                    
                        // Orders & Payments
                        'orders.php' => ['icon' => 'fas fa-shopping-cart', 'title' => 'Mes commandes'],
                        
                        // Notifications & Support
                        'support.php' => ['icon' => 'fas fa-question-circle', 'title' => 'Aide & Support'],
                        
                        // Always place logout at the bottom
                        'logout.php' => ['icon' => 'fas fa-sign-out-alt', 'title' => 'Déconnexion', 'class' => 'text-danger'],
                    ];
                    
                    // Generate menu items
                    foreach ($menu_items as $page => $item) {
                        $url = ROOT_URL . 'public/user/' . $page;
                        $is_active = ($current_page == $page) ? 'active' : '';
                        $extra_class = isset($item['class']) ? $item['class'] : '';
                        echo '<a href="' . $url . '" class="list-group-item list-group-item-action ' . $is_active . ' ' . $extra_class . '">';
                        echo '<i class="' . $item['icon'] . ' me-2"></i> ' . $item['title'];
                        echo '</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>