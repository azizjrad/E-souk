<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';

$page_title = "À propos - E-Souk Tounsi";
$page_description = "Découvrez notre histoire et notre mission de promotion de l'artisanat tunisien";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    
    <?php include '../templates/header.php'; ?>

    <link rel="stylesheet" href="../assets/css/apropos.css">
</head>
<body>
<?php include '../templates/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section position-relative mb-5">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center" style="min-height: 80vh">
            <div class="col-lg-6 text-white position-relative z-2">
                <h1 class="hero-title mb-4">À Propos de<br>E-Souk Tounsi</h1>
                <p class="hero-subtitle mb-4">Découvrez l'histoire de notre passion pour l'artisanat tunisien et notre mission de préservation du patrimoine culturel.</p>
                <div class="hero-buttons position-relative z-3">
                    <a href="#notre-histoire" class="btn btn-primary btn-lg px-4 shadow-sm">Notre Histoire</a>
                    <a href="vision.php" class="btn btn-outline-light btn-lg px-4 ms-3">Notre Vision</a>
                </div>
            </div>
            <div class="col-lg-6 position-relative z-2 d-none d-lg-block">
                <div class="hero-featured-3d">
                    <!-- 3D Tunisian Chechia -->
                    <div class="floating-chechia">
                        <div class="chechia-base"></div>
                        <div class="chechia-top"></div>
                        <div class="chechia-tassel"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-shape">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,181.3C960,213,1056,224,1152,208C1248,192,1344,149,1392,128L1440,107L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</section>

        <!-- Notre Histoire Section -->
<section class="container py-5">

    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="position-relative" style="width: 90%; margin: 0 auto;">
                <!-- Image with shadow but no border -->
                <img src="../assets/images/historic/1.jpg" alt="Notre Histoire" class="img-fluid rounded transition-standard" style="box-shadow: 0 15px 30px var(--color-shadow);">                
                <!-- Decorative element - small accent border -->
                <div style="position: absolute; bottom: -15px; right: -15px; width: 60%; height: 60%; border: 3px solid var(--color-accent); border-radius: 10px; z-index: -1;"></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ps-lg-4">
                <!-- Title with accent underline -->
                <h2 class="mb-4 position-relative">Notre Histoire
                    <span style="display: block; width: 80px; height: 3px; background: var(--color-accent); margin-top: 10px;"></span>
                </h2>
                
                <!-- First paragraph with highlight effect -->
                <p class="mb-4" style="border-left: 3px solid var(--color-accent); padding-left: 15px;">
                    Fondé en 2025, E-Souk Tounsi est né d'une passion profonde pour l'artisanat tunisien et d'une volonté de préserver ce patrimoine culturel inestimable. Face à la mondialisation et à l'industrialisation qui menacent les savoir-faire traditionnels, nous avons créé une plateforme permettant aux artisans tunisiens de partager leur art avec le monde entier.
                </p>
                
                <!-- Second paragraph -->
                <p class="mb-4">
                    Notre voyage a commencé par des rencontres avec des artisans dans différentes régions de la Tunisie, de Nabeul à Kairouan, de Djerba à Sejnane. Chaque rencontre nous a confortés dans notre conviction : ces trésors artisanaux méritent d'être connus et reconnus à l'échelle internationale.
                </p>
                
                <!-- Third paragraph with statistics -->
                <p class="mb-4">
                    Aujourd'hui, E-Souk Tounsi est fier de collaborer avec plus de 100 artisans à travers le pays, offrant une vitrine numérique à leur talent et contribuant à la préservation de techniques ancestrales.
                </p>
                
                <!-- Statistics display -->
                <div class="row text-center mt-4">
                    <div class="col-4">
                        <div style="background-color: var(--color-primary); color: white; border-radius: 8px; padding: 15px;">
                            <h4 class="mb-0">100+</h4>
                            <p class="mb-0 small">Artisans</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background-color: var(--color-medium); color: white; border-radius: 8px; padding: 15px;">
                            <h4 class="mb-0">12</h4>
                            <p class="mb-0 small">Régions</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div style="background-color: var(--color-accent); color: white; border-radius: 8px; padding: 15px;">
                            <h4 class="mb-0">1500+</h4>
                            <p class="mb-0 small">Créations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</section>

<!-- Notre Mission Section with Hover Effects -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="mb-4">Notre Mission</h2>
                <p class="lead mb-5">Chez E-Souk Tounsi, nous sommes guidés par des valeurs fortes et une mission claire qui définissent chacune de nos actions.</p>
                    <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">

            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm mission-card">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-4 mission-icon" style="width: 80px; height: 80px;">
                            <i class="fas fa-hands text-white fa-2x"></i>
                        </div>
                        <h4 class="card-title mb-3">Préserver</h4>
                        <p class="card-text">Nous œuvrons à la préservation des techniques artisanales ancestrales en soutenant les artisans qui perpétuent ces traditions séculaires.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm mission-card">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-4 mission-icon" style="width: 80px; height: 80px;">
                            <i class="fas fa-globe-africa text-white fa-2x"></i>
                        </div>
                        <h4 class="card-title mb-3">Connecter</h4>
                        <p class="card-text">Nous créons des ponts entre les artisans tunisiens et les amateurs d'art du monde entier, permettant un échange culturel enrichissant.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm mission-card">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-4 mission-icon" style="width: 80px; height: 80px;">
                            <i class="fas fa-hand-holding-heart text-white fa-2x"></i>
                        </div>
                        <h4 class="card-title mb-3">Soutenir</h4>
                        <p class="card-text">Nous soutenons les communautés locales en assurant une rémunération équitable des artisans et en investissant dans le développement de leurs compétences.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Notre Équipe -->
<section class="container py-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="mb-4">Notre Équipe</h2>
            <p class="lead mb-5">Passionnés par l'artisanat et la culture tunisienne, nous mettons notre expertise au service de notre mission.</p>
                <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">
        </div>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm team-card">
                <div class="team-image-container">
                    <img src="../assets/images/dev-profile/fedi-riahi.jpg" alt="Développeur" class="card-img-top team-image">
                </div>
                <div class="card-body text-center p-4">
                    <h4 class="card-title mb-2">Fedi Riahi</h4>
                    <p class="text-muted mb-3">Développeur Full Stack</p>
                    <p class="card-text">Passionné par le développement web, Fedi a apporté son expertise technique pour créer une plateforme intuitive qui met en valeur l'artisanat tunisien.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-primary me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-primary"><i class="far fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm team-card">
                <div class="team-image-container">
                    <img src="../assets/images/dev-profile/aziz.png" alt="Développeur" class="card-img-top team-image">
                </div>
                <div class="card-body text-center p-4">
                    <h4 class="card-title mb-2">Aziz Jrad</h4>
                    <p class="text-muted mb-3">Développeur Web</p>
                    <p class="card-text">Expert en interfaces utilisateur, Aizi a travaillé sur l'expérience client du site pour offrir une vitrine digitale moderne aux artisans tunisiens.</p>
                    <div class="social-icons mt-3">
                        <a href="https://www.linkedin.com/in/azizjrad/" class="text-primary me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-primary"><i class="far fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Rejoignez l'Aventure E-Souk Tounsi</h2>
            <p class="lead mb-4">En achetant sur E-Souk Tounsi, vous soutenez l'artisanat tunisien et contribuez à préserver un patrimoine culturel unique.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="./product.php" class="btn btn-primary btn-lg px-4 shadow-sm">Découvrir nos produits</a>
                <a href="contact.php" class="btn btn-outline-primary btn-lg px-4">Nous contacter</a>
            </div>
        </div>
    </section>

    

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/newsletter.php'; ?>
    <?php include '../templates/footer.php'; ?>

</body>
</html>