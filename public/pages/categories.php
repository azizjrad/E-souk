<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';


$page_title = "Catégories - E-Souk Tounsi";
$page_description = "Découvrez toutes nos catégories d'artisanat tunisien";
// Define hero section variables
$title = "Catégories d'Artisanat Tunisien";
$subtitle = "Explorez notre collection de catégories représentant le riche patrimoine artisanal de la Tunisie";
$breadcrumbs = [
    'Accueil' => 'index.php',
    'Catégories' => '#',  
];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/categories.css">
    <link rel="stylesheet" href="../assets/css/components/hero.css">
</head>
<body>
    <?php include '../templates/navbar.php'; ?>
    <?php include '../templates/hero.php'; ?>

    <!-- Categories Section -->
    <section class="container category-container my-5">
        <?php
        try {
            // Query to fetch all categories
            $query = "SELECT id_category, name, description, image FROM category ORDER BY id_category";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit;
        } 
        if(count($categories) > 0): ?>
            <?php foreach($categories as $index => $category): 
                $isEven = $index % 2 == 0;
            ?>
                <div class="row category-row align-items-center position-relative mb-5">
                    <!-- Add decorative shapes based on even/odd -->
                    <div class="decoration-shape <?= $isEven ? 'shape-1' : 'shape-2' ?>"></div>
                    <?php if($isEven): ?>
                        <!-- Left image, right text (even rows) -->
                        <div class="col-md-6 mb-4 mb-md-0">
                            <a href="category-details.php?id=<?= $category['id_category'] ?>" class="category-link">
                                <div class="category-card shadow-sm">
                                    <?php if(!empty($category['image'])): ?>
                                        <img src="../../root_uploads/categories/<?= htmlspecialchars($category['image']) ?>" 
                                             alt="<?= htmlspecialchars($category['name']) ?>" class="category-image">
                                    <?php else: ?>
                                        <img src="../../assets/images/no-image.jpg" 
                                             alt="No Image Available" class="category-image">
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-5 offset-md-1">
                            <h2 class="category-name"><?= htmlspecialchars($category['name']) ?></h2>
                            <div class="category-divider"></div>
                            <a href="category-details.php?id=<?= $category['id_category'] ?>" class="explore-btn">
                                Explorer <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Left text, right image (odd rows) -->
                        <div class="col-md-5">
                            <h2 class="category-name"><?= htmlspecialchars($category['name']) ?></h2>
                            <div class="category-divider"></div>
                            <a href="category-details.php?id=<?= $category['id_category'] ?>" class="explore-btn">
                                Explorer <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="col-md-6 offset-md-1">
                            <a href="category-details.php?id=<?= $category['id_category'] ?>" class="category-link">
                                <div class="category-card shadow-sm">
                                    <?php if(!empty($category['image'])): ?>
                                        <img src="../../root_uploads/categories/<?= htmlspecialchars($category['image']) ?>" 
                                             alt="<?= htmlspecialchars($category['name']) ?>" class="category-image">
                                    <?php else: ?>
                                        <img src="../../assets/images/no-image.jpg" 
                                             alt="No Image Available" class="category-image">
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">Aucune catégorie n'est disponible pour le moment.</div>
        <?php endif; ?>
    </section>
    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/newsletter.php'; ?>
    <?php include '../templates/footer.php'; ?>

</body>
</html>