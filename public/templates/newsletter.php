<?php
// Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/init.php';
require_once ROOT_PATH . '/core/connection.php';

$db = Database::getInstance();

// Process form submission
$subscriptionMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM newsletter WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn() > 0;
        
        if (!$emailExists) {
            // Add email to newsletter
            $stmt = $db->prepare("INSERT INTO newsletter (email, subscription_date) VALUES (?, NOW())");
            if ($stmt->execute([$email])) {
                $subscriptionMessage = '<div class="alert alert-success">Merci pour votre abonnement!</div>';
            } else {
                $subscriptionMessage = '<div class="alert alert-danger">Une erreur est survenue. Veuillez réessayer.</div>';
            }
        } else {
            $subscriptionMessage = '<div class="alert alert-info">Vous êtes déjà abonné à notre newsletter.</div>';
        }
    } else {
        $subscriptionMessage = '<div class="alert alert-danger">Veuillez entrer une adresse email valide.</div>';
    }
}
?>

<!-- Enhanced Newsletter Section -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="newsletter-content p-4 rounded">
                    <div class="newsletter-icon mb-3">
                        <img src="./assets/images/envelope-icon.png" alt="Newsletter" class="icon-envelope" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwYXRoIGQ9Ik00IDRoMTZjMS4xIDAgMiAuOSAyIDJ2MTJjMCAxLjEtLjkgMi0yIDJINGMtMS4xIDAtMi0uOS0yLTJ2LTEyYzAtMS4xLjktMiAyLTJ6Ii8+PHBvbHlsaW5lIHBvaW50cz0iMjIsNiAxMiwxMyAyLDYiLz48L3N2Zz4=';" width="60" height="60"/>
                    </div>
                    <h3 class="text-white mb-3">Recevez nos dernières nouveautés</h3>
                    <p class="text-white mb-4">Inscrivez-vous pour découvrir en avant-première nos nouvelles collections et offres exclusives d'artisanat tunisien.</p>
                    
                    <?= $subscriptionMessage ?>
                    
                    <form class="newsletter-form" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>#newsletter" method="POST">
                        <div class="newsletter-inputs-container">
                            <div class="newsletter-email-input">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-at"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control border-start-0 py-3" placeholder="Votre email" required>
                                </div>
                            </div>
                            <div class="newsletter-submit-button">
                                <button type="submit" class="btn btn-dark w-100 py-3">
                                    <i class="fas fa-paper-plane me-2"></i>S'abonner
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="newsletter-footer mt-4">
                        <p class="text-footer">En vous inscrivant, vous acceptez de recevoir nos e-mails et confirmez avoir lu notre politique de confidentialité.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Wave SVG Shape -->
    <div class="newsletter-shape">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L48,80C96,96,192,128,288,122.7C384,117,480,75,576,64C672,53,768,75,864,90.7C960,107,1056,117,1152,112C1248,107,1344,85,1392,74.7L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
        </svg>
    </div>
</section>