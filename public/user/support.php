<?php
// Inclusion du fichier d'initialisation avec la connexion à la base de données
require_once __DIR__ . '/../../config/init.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si non connecté
    header("Location: ../pages/login.php");
    exit;
}

// Check where user came from
$fromProfile = false;
$fromOrder = false;
$backUrl = '';
$backText = '';
$backIcon = '';

if(isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    
    if(strpos($referer, 'profile.php') !== false) {
        $fromProfile = true;
        $backUrl = 'profile.php';
        $backText = 'Retour au Profil';
        $backIcon = 'fa-user-circle';
    } 
    elseif(strpos($referer, 'edit_profile.php') !== false) {
        $fromProfile = true;
        $backUrl = 'edit_profile.php';
        $backText = 'Retour au Profil';
        $backIcon = 'fa-user-circle';
    }
    elseif(strpos($referer, 'order.php') !== false || strpos($referer, 'orders.php') !== false) {
        $fromOrder = true;
        $backUrl = strpos($referer, 'order.php') !== false ? 'order.php' : 'orders.php';
        $backText = 'Retour aux Commandes';
        $backIcon = 'fa-shopping-bag';
    }
}

$userId = $_SESSION['user_id'];

// Obtenir les tickets de l'utilisateur s'il est connecté
$userTickets = [];
try {
    $sql = "SELECT ticket_id, subject, category, status, created_at as created_date FROM support_tickets 
            WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId]);
    $userTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

// Traitement de la soumission d'un nouveau ticket de support
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_ticket'])) {
    // Valider et nettoyer les entrées
    $subject = htmlspecialchars($_POST['subject']);
    $category = htmlspecialchars($_POST['category']);
    $message = htmlspecialchars($_POST['message']);
    
    // Validation supplémentaire
    $errors = [];
    if (empty($subject)) {
        $errors[] = "Le sujet est obligatoire";
    }
    if (empty($message)) {
        $errors[] = "Le message est obligatoire";
    }
    
    // Si pas d'erreurs, insérer le ticket
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO support_tickets (user_id, subject, category, message, status, created_at) 
                    VALUES (?, ?, ?, ?, 'Ouvert', NOW())";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$userId, $subject, $category, $message]);
            
            if ($result) {
                $successMessage = "Votre ticket de support a été soumis avec succès !";
                // Redirection pour éviter la resoumission du formulaire
                header("Location: support.php?success=1");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur: " . $e->getMessage();
        }
    }
}
$page_title = "Service Client - E-Souk Tounsi";
$description = "Centre d'assistance pour les clients d'E-Souk Tounsi. Obtenez de l'aide pour vos commandes, produits et compte.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Client - E-Souk</title>
    <!-- Inclusion du modèle d'en-tête -->
    <?php include '../templates/header.php'; ?>
    
    <style>
        /* Styles personnalisés */
        :root {
            --primary-color: #2b3684;
            --primary-dark: #212a63;
            --secondary-color: #f5f7fa;
            --accent-color: #ff9800;
            --text-color: #333;
            --light-gray: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            background-color: #f5f7fa;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .card {
            border-radius: 10px;
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }
        
        .card-header {
            border-bottom: none;
            padding: 1.25rem;
        }
        
        .support-card {
            border-left: 5px solid var(--primary-color);
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .contact-icon {
            font-size: 24px;
            margin-right: 15px;
            color: var(--primary-color);
        }
        
        .accordion-button:not(.collapsed) {
            background-color: var(--secondary-color);
            color: var(--primary-color);
            box-shadow: none;
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(58, 123, 213, 0.1);
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        
        .bg-success {
            background-color: #28a745 !important;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
        }
        
        .bg-danger {
            background-color: #dc3545 !important;
        }
        
        .table th {
            font-weight: 600;
            color: #495057;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(58, 123, 213, 0.25);
        }
        
        .sidebar-nav {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
        }
        
        .sidebar-nav .nav-link {
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }
        
        .sidebar-nav .nav-link i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        /* Animation pour le message de succès */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 30px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .alert-success {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Back to Profile button */
        .back-to-profile-btn {
            background-color: #2b3684;
            color: #ffffff;
            border-color: #2b3684;
            border-radius: 30px;
            padding: 8px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 200px;
            text-align: center;
        }
        
        .back-to-profile-btn:hover {
            background-color: #232c6a;
            border-color: #232c6a;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(43, 54, 132, 0.2);
        }
        
        /* Remove onclick effect */
        .back-to-profile-btn:active,
        .back-to-profile-btn:focus {
            background-color: #2b3684 !important;
            border-color: #2b3684 !important;
            color: #ffffff !important;
            box-shadow: none !important;
            outline: none !important;
        }
        
        @media (max-width: 768px) {
            .back-to-profile-btn {
                min-width: 180px;
                padding: 7px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .back-to-profile-btn {
                width: 90%;
                max-width: 300px;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Ajout de la barre de navigation -->
    <?php include '../templates/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-3 mb-4">
                <!-- Back button above sidebar -->
                <?php if($fromProfile || $fromOrder): ?>
                <div class="mb-4 text-center">
                    <a href="<?php echo $backUrl; ?>" class="btn back-to-profile-btn">
                        <i class="fas <?php echo $backIcon; ?> me-2"></i><?php echo $backText; ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Barre latérale -->
                <div class="sidebar-nav shadow-sm mb-4">
                    <h5 class="mb-3">Centre d'Assistance</h5>
                    <div class="nav flex-column">
                        <a class="nav-link active" href="support.php">
                            <i class="fas fa-question-circle"></i> Accueil Support
                        </a>
                        <a class="nav-link" href="faq.php">
                            <i class="fas fa-info-circle"></i> FAQ
                        </a>
                        <a class="nav-link" href="new_ticket.php">
                            <i class="fas fa-ticket-alt"></i> Nouveau Ticket
                        </a>
                        <a class="nav-link" href="my_tickets.php">
                            <i class="fas fa-clipboard-list"></i> Mes Tickets
                        </a>
                        <a class="nav-link" href="knowledge_base.php">
                            <i class="fas fa-book"></i> Base de Connaissances
                        </a>
                    </div>
                </div>
                
                <!-- Heures de contact -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Heures d'Ouverture</h5>
                    </div>
                    <div class="card-body">
                        <p><i class="fas fa-clock text-primary me-2"></i> Lun-Dim: 24h/7jrs</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <!-- Message de succès -->
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Votre ticket de support a été soumis avec succès !
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Messages d'erreur -->
                <?php if(isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Informations du Service Client</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Comment Obtenir de l'Aide</h5>
                            <p>Notre équipe du service client est disponible pour vous aider avec toutes questions ou préoccupations concernant vos commandes, produits ou compte.</p>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="support-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="fas fa-envelope contact-icon"></i>
                                            <h6 class="mb-0">Support par Email</h6>
                                        </div>
                                        <p class="mb-1">Demandes générales :</p>
                                        <p class="mb-1"><strong>support@e-souk.com</strong></p>
                                        <p class="mb-1">Urgences :</p>
                                        <p class="mb-1"><strong>urgent@e-souk.com</strong></p>
                                        <p class="text-muted small mb-0">Temps de réponse : Sous 24 heures</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="support-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="fas fa-phone contact-icon"></i>
                                            <h6 class="mb-0">Support Téléphonique</h6>
                                        </div>
                                        <p class="mb-1">Service Clientèle :</p>
                                        <p class="mb-1"><strong>+1-800-123-4567</strong></p>
                                        <p class="text-muted small mb-0">Horaires : Lundi à Vendredi, 9h00 - 18h00</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="support-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="fas fa-comment contact-icon"></i>
                                            <h6 class="mb-0">Chat en Direct</h6>
                                        </div>
                                        <p class="mb-1">Disponible sur notre site web</p>
                                        <p class="text-muted small mb-0">Horaires : Lundi à Samedi, 10h00 - 20h00</p>
                                        <button class="btn btn-sm btn-primary mt-3">
                                            <i class="fas fa-comments me-2"></i>Démarrer Chat
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Créer un Nouveau Ticket de Support</h5>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="subject" class="form-label">Sujet</label>
                                        <input type="text" class="form-control" id="subject" name="subject" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="category" class="form-label">Catégorie</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Sélectionner une catégorie</option>
                                            <option value="Order Issue">Problème de Commande</option>
                                            <option value="Product Inquiry">Renseignement Produit</option>
                                            <option value="Account Help">Aide Compte</option>
                                            <option value="Technical Support">Support Technique</option>
                                            <option value="Billing Question">Question Facturation</option>
                                            <option value="Return/Refund">Retour/Remboursement</option>
                                            <option value="Other">Autre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Pièces jointes (optionnel)</label>
                                    <input class="form-control" type="file" id="attachments" name="attachments">
                                    <div class="form-text">Taille max. fichier : 5Mo. Formats supportés : JPG, PNG, PDF</div>
                                </div>
                                <button type="submit" name="submit_ticket" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre le Ticket
                                </button>
                            </form>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Questions Fréquemment Posées</h5>
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                            Comment suivre ma commande ?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Vous pouvez suivre votre commande dans le tableau de bord de votre compte sous la section "Commandes". Cliquez sur le numéro de commande pour voir les informations détaillées de suivi. Si vous avez créé un compte lors de votre commande, vous pouvez vous connecter pour accéder à vos informations de suivi. Si vous avez commandé en tant qu'invité, vous pouvez utiliser l'outil de suivi de commande sur notre site en saisissant votre numéro de commande et votre adresse e-mail.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                            Quelle est votre politique de retour ?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Nous acceptons les retours dans les 30 jours suivant l'achat. Les articles doivent être dans leur état d'origine avec les étiquettes attachées. Pour initier un retour, veuillez vous connecter à votre compte et sélectionner la commande que vous souhaitez retourner. Suivez les instructions pour générer une étiquette d'expédition de retour. Une fois que nous recevons votre retour, comptez 7 à 10 jours ouvrables pour le traitement. Les remboursements seront effectués sur le mode de paiement d'origine.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                            Combien de temps prend la livraison ?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            La livraison standard prend généralement 3 à 5 jours ouvrables aux États-Unis continentaux. Des options de livraison accélérée sont disponibles lors du paiement. Les délais de livraison internationale varient selon la destination, généralement entre 7 et 14 jours ouvrables. Veuillez noter que le dédouanement peut ajouter des jours supplémentaires aux délais de livraison internationale.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                            Comment changer mon mot de passe ?
                                        </button>
                                    </h2>
                                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Pour changer votre mot de passe, connectez-vous à votre compte et allez dans "Paramètres du compte". Sous l'onglet "Sécurité", vous trouverez l'option pour changer votre mot de passe. Si vous avez oublié votre mot de passe, cliquez sur le lien "Mot de passe oublié" sur la page de connexion, et nous vous enverrons un lien de réinitialisation à votre adresse e-mail enregistrée.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="faq.php" class="btn btn-outline-primary">Voir Toutes les FAQs</a>
                            </div>
                        </div>
                        
                        <?php if(!empty($userTickets)): ?>
                        <hr class="my-4">
                        
                        <h5 class="border-bottom pb-2">Vos Tickets de Support Précédents</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Ticket</th>
                                        <th>Sujet</th>
                                        <th>Catégorie</th>
                                        <th>Statut</th>
                                        <th>Date de Création</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($userTickets as $ticket): ?>
                                    <tr>
                                        <td>#<?php echo $ticket['ticket_id']; ?></td>
                                        <td><?php echo $ticket['subject']; ?></td>
                                        <td><?php echo $ticket['category']; ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $status = $ticket['status'];
                                            if($status == 'Open') {
                                                $statusClass = 'bg-success';
                                                $status = 'Ouvert';
                                            } else if($status == 'Pending') {
                                                $statusClass = 'bg-warning text-dark';
                                                $status = 'En Attente';
                                            } else if($status == 'Closed') {
                                                $statusClass = 'bg-secondary';
                                                $status = 'Fermé';
                                            } else if($status == 'Urgent') {
                                                $statusClass = 'bg-danger';
                                                $status = 'Urgent';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($ticket['created_date'])); ?></td>
                                        <td>
                                            <a href="view_ticket.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php elseif(isset($_SESSION['user_id'])): ?>
                        <div class="text-center my-5">
                            <p>Vous n'avez pas encore de tickets de support.</p>
                            <a href="new_ticket.php" class="btn btn-primary">Créer un Ticket</a>
                        </div>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i> Veuillez <a href="login.php">vous connecter</a> pour voir vos tickets de support ou en créer un nouveau.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Carte de Contact Support -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5>Vous avez encore besoin d'aide ?</h5>
                                <p class="mb-0">Notre équipe de support n'est qu'à un clic. Nous sommes heureux de vous aider avec toutes questions ou préoccupations que vous pourriez avoir.</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <a href="../pages/contact.php" class="btn btn-primary">
                                    <i class="fas fa-headset me-2"></i>Contactez-Nous
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ajout du pied de page -->
    <?php include '../templates/footer.php'; ?>
    
    <script>
        // Auto-fermeture des alertes après 5 secondes
        document.addEventListener('DOMContentLoaded', function() {
            let alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    let closeButton = alert.querySelector('.btn-close');
                    if(closeButton) {
                        closeButton.click();
                    }
                }, 5000);
            });
            
            // Initialisation de la fonctionnalité de chat en direct
            document.querySelector('.btn-primary[class*="chat"]').addEventListener('click', function() {
                // C'est ici que vous initialiseriez votre widget de chat
                alert('La fonctionnalité de chat en direct sera bientôt disponible !');
            });
        });
    </script>
</body>
</html>