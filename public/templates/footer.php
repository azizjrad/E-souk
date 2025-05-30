<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row gy-3">
            <!-- Logo et description -->
            <div class="col-md-3 mb-3 text-center text-md-start">
                <img src="../assets/images/logo.png" alt="E-Souk Logo" height="40">
                <p class="mt-2">L'artisanat tunisien depuis &copy; <?= date('Y') ?></p>
            </div>

            <!-- Information -->
            <div class="col-md-3 mb-3">
                <h5 class="text-uppercase mb-3">Informations</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo ROOT_URL; ?>public/pages/about.php" class="text-white text-decoration-none">
                            <i class="fas fa-info-circle me-2"></i>À Propos
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo ROOT_URL; ?>public/user/faq.php" class="text-white text-decoration-none">
                            <i class="fas fa-question-circle me-2"></i>FAQ
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ROOT_URL; ?>public/user/support.php" class="text-white text-decoration-none">
                            <i class="fas fa-headset me-2"></i>Support
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-3 mb-3">
                <h5 class="text-uppercase mb-3">Contactez-nous</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-phone me-2"></i>+216 70 000 000
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2"></i>contact@esouk.tn
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt me-2"></i>Tunis, Tunisie
                    </li>
                </ul>
            </div>

            <!-- Réseaux sociaux -->
            <div class="col-md-3 mb-3 text-center text-md-end">
                <h5 class="text-uppercase mb-3">Suivez-nous</h5>
                <div class="social-icons">
                    <a href="https://instagram.com" class="text-white" target="_blank" aria-label="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="https://facebook.com" class="text-white" target="_blank" aria-label="Facebook">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="https://pinterest.com" class="text-white" target="_blank" aria-label="Pinterest">
                        <i class="fab fa-pinterest fa-lg"></i>
                    </a>
                </div>
                <p class="mb-0 small">
                    &copy; <?= date('Y') ?> E-souk Tounsi. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts communs -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>

</body>
</html>