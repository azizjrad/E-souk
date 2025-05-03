<?php
/**
 * Universal Hero Section Component
 * 
 * @param string $title - The main hero title
 * @param string $subtitle - The subtitle text
 * @param array $breadcrumbs - Array of breadcrumbs in format ['title' => 'url'] (last item should be active page)
 */

// Default values if not provided
$title = $title ?? "Page Title";
$subtitle = $subtitle ?? " ";
$breadcrumbs = $breadcrumbs ?? [
    'Accueil' => 'index.php',
    $title => '#' // Current page as last item
];
?>

<!-- Hero Section -->
<section class="category-hero-section">
    <div class="hero-bg-overlay"></div>
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php 
                        $i = 0;
                        $total = count($breadcrumbs);
                        foreach ($breadcrumbs as $name => $url) {
                            $i++;
                            if ($i == $total) {
                                // Last item (active)
                                echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($name) . '</li>';
                            } else {
                                // Regular item with link
                                echo '<li class="breadcrumb-item"><a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($name) . '</a></li>';
                            }
                        }
                        ?>
                    </ol>
                </nav>
                <h1 class="hero-title"><?php echo htmlspecialchars($title); ?></h1>
                <p class="hero-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
            </div>
        </div>
    </div>
</section>