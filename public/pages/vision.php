<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';  // Added init.php include

$page_title = "Notre Vision - E-Souk Tounsi";
$page_description = "Découvrez notre vision pour l'avenir de l'artisanat tunisien";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/vision.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>
    <?php include '../templates/navbar.php'; ?>

    <!-- Vision Hero Section -->
    <section class="vision-hero-section position-relative">
        <div class="vision-overlay"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-7 text-white position-relative z-2" data-aos="fade-right" data-aos-duration="1000">
                    <div class="coming-soon-badge">Bientôt Disponible</div>
                    <h1 class="vision-title">La Marketplace de <span class="highlight">l'Artisanat Tunisien</span></h1>
                    <p class="vision-subtitle">Notre vision est de créer la première plateforme numérique dédiée aux artisans tunisiens, leur permettant de vendre leurs créations au monde entier.</p>
                    <div class="countdown-container">
                        <div class="countdown-item">
                            <span id="days">00</span>
                            <span class="countdown-label">Jours</span>
                        </div>
                        <div class="countdown-item">
                            <span id="hours">00</span>
                            <span class="countdown-label">Heures</span>
                        </div>
                        <div class="countdown-item">
                            <span id="minutes">00</span>
                            <span class="countdown-label">Minutes</span>
                        </div>
                        <div class="countdown-item">
                            <span id="seconds">00</span>
                            <span class="countdown-label">Secondes</span>
                        </div>
                    </div>
                    <div class="hero-buttons mt-5">
                        <a href="#vision-details" class="btn btn-primary btn-lg px-4 shadow-sm mb-2 mb-sm-0">En savoir plus</a>
                        <a href="#newsletter" class="btn btn-outline-light btn-lg px-4 ms-0 ms-sm-3">Être notifié</a>
                    </div>
                </div>
                <div class="col-lg-5 position-relative z-2 d-none d-lg-block" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                    <div class="vision-illustration">
                        <div class="coming-soon-3d">
                            <div class="coming-soon-3d-inner">
                                <div class="coming-soon-3d-text">Bientôt<br>Disponible</div>
                                <div class="coming-soon-3d-text">Soon<br>Available</div>
                                <div class="coming-soon-3d-text">قريبا<br>متاح</div>
                                <div class="coming-soon-3d-text">E-Souk<br>Tounsi</div>
                                <div class="coming-soon-3d-text">2025</div>
                                <div class="coming-soon-3d-text"><i class="fas fa-hourglass-half"></i></div>
                            </div>
                        </div >
                    </div>
                </div>
            </div>
        </div>
        <div class="vision-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,181.3C960,213,1056,224,1152,208C1248,192,1344,149,1392,128L1440,107L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Vision Details Section -->
    <section id="vision-details" class="vision-details py-5">
        <div class="container">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h2>Notre Vision pour l'Avenir</h2>
                <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">
            </div>
            
            <div class="row g-4 mt-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="vision-card">
                        <div class="vision-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Soutenir les Artisans</h3>
                        <p>Une plateforme où les artisans tunisiens peuvent s'inscrire, créer leur boutique et vendre directement leurs créations, en préservant leur savoir-faire ancestral.</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="vision-card">
                        <div class="vision-card-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Livraison Nationale</h3>
                        <p>Un partenariat exclusif avec une entreprise de livraison pour garantir une distribution rapide et fiable partout en Tunisie et à l'international.</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="vision-card">
                        <div class="vision-card-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3>Qualité Vérifiée</h3>
                        <p>Chaque artisan est soigneusement vérifié pour garantir l'authenticité et la qualité des produits traditionnels tunisiens proposés.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="how-it-works py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h2>Comment Ça Fonctionnera</h2>
                <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d">
            </div>
            
            <div class="timeline" data-aos="fade-up">
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="timeline-content">
                        <h3>Création de Compte</h3>
                        <p>Les artisans s'inscrivent et complètent leur profil avec leur histoire, techniques et spécialités.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <h3>Vérification</h3>
                        <p>Notre équipe vérifie l'authenticité et la qualité du travail de chaque artisan.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="timeline-content">
                        <h3>Création de Boutique</h3>
                        <p>Une fois approuvé, l'artisan peut créer sa boutique et télécharger ses produits.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="timeline-content">
                        <h3>Ventes et Commandes</h3>
                        <p>Les clients découvrent et achètent des produits authentiques, directement des créateurs.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="timeline-content">
                        <h3>Livraison Simplifiée</h3>
                        <p>Notre partenaire de livraison s'occupe de la collecte et de la distribution des produits.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partnership Section -->
    <section class="partnership-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="partnership-image">
                        <img src="../assets/images/shipping-partner.png" alt="Partenaire de livraison" class="img-fluid rounded-lg shadow">
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="partnership-content">
                        <span class="partnership-badge">Partenariat Stratégique</span>
                        <h2>Livraison Fiable & Rapide</h2>
                        <p>Nous sommes en train de finaliser un partenariat exclusif avec une entreprise de livraison leader en Tunisie pour offrir:</p>
                        <ul class="partnership-features">
                            <li><i class="fas fa-check-circle"></i> Livraison dans toute la Tunisie sous 48h</li>
                            <li><i class="fas fa-check-circle"></i> Suivi en temps réel des commandes</li>
                            <li><i class="fas fa-check-circle"></i> Collecte directement chez l'artisan</li>
                            <li><i class="fas fa-check-circle"></i> Livraison internationale avec options d'expédition multiples</li>
                            <li><i class="fas fa-check-circle"></i> Système de retour simple et efficace</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter Section with ID for direct link -->
<section id="newsletter" class="newsletter-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <h2>Restez Informé</h2>
                <p class="lead mb-4">Inscrivez-vous à notre newsletter pour être parmi les premiers à être informés du lancement de notre marketplace.</p>
                
                <form class="signup-form">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Votre adresse email" aria-label="Email address" required>
                        <button class="btn btn-primary" type="submit">S'inscrire</button>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="artisanCheck">
                        <label class="form-check-label" for="artisanCheck">
                            Je suis un artisan intéressé à vendre sur la plateforme
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include '../templates/Topbtn.php'; ?> 
<?php include '../templates/footer.php'; ?> 

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Custom Scripts -->
    
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Countdown Timer
        const countDownDate = new Date("Dec 31, 2025 23:59:59").getTime();
        
        const countdownFunction = setInterval(function() {
            const now = new Date().getTime();
            const distance = countDownDate - now;
            
            document.getElementById("days").innerText = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            document.getElementById("hours").innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            document.getElementById("minutes").innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            document.getElementById("seconds").innerText = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');
            
            if (distance < 0) {
                clearInterval(countdownFunction);
                document.getElementById("days").innerText = "00";
                document.getElementById("hours").innerText = "00";
                document.getElementById("minutes").innerText = "00";
                document.getElementById("seconds").innerText = "00";
            }
        }, 1000);
    </script>
    
</body>
</html>