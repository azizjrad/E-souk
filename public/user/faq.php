<?php
require_once __DIR__ . '/../../config/init.php';

$page_title = "FAQ - E-Souk Tounsi";
$description = "Foire Aux Questions - Trouvez des réponses à vos questions concernant E-Souk, nos produits et services.";

// Check if user came from support.php
$fromSupport = false;
if(isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if(strpos($referer, 'support.php') !== false) {
        $fromSupport = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <style>
        /* Internal styles for FAQ page */
        .faq-header {
            background-color: #f8f9fa;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        
        .faq-header h1 {
            color: #2b3684;
            font-weight: 700;
        }
        
        .faq-section {
            margin-bottom: 40px;
        }
        
        .faq-section h2 {
            color: #2b3684;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
            position: relative;
        }
        
        .faq-section h2::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 60px;
            height: 2px;
            background-color: #2b3684;
        }
        
        .accordion-item {
            border: none;
            border-radius: 10px;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .accordion-header {
            margin: 0;
        }
        
        .accordion-button {
            font-weight: 600;
            color: #444;
            background-color: #fff;
            box-shadow: none;
            padding: 16px 20px;
        }
        
        .accordion-button:not(.collapsed) {
            color: #2b3684;
            background-color: #f8f9fa;
            box-shadow: none;
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(43, 54, 132, 0.25);
        }
        
        .accordion-button::after {
            background-size: 16px;
            width: 16px;
            height: 16px;
            transition: all 0.3s ease;
        }
        
        .accordion-button:not(.collapsed)::after {
            transform: rotate(-180deg);
        }
        
        .accordion-body {
            padding: 16px 20px;
            color: #666;
            line-height: 1.6;
        }
        
        .search-faq {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-faq input {
            padding: 12px 20px;
            border-radius: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding-left: 45px;
            transition: all 0.3s ease;
        }
        
        .search-faq input:focus {
            border-color: #2b3684;
            box-shadow: 0 3px 15px rgba(43, 54, 132, 0.1);
        }
        
        .search-faq i {
            position: absolute;
            left: 18px;
            top: 15px;
            color: #999;
        }
        
        .still-have-questions {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        
        .still-have-questions h3 {
            color: #2b3684;
            margin-bottom: 15px;
        }
        
        .still-have-questions .btn-primary {
            background-color: #2b3684;
            border-color: #2b3684;
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .still-have-questions .btn-primary:hover {
            background-color: #232c6a;
            border-color: #232c6a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(43, 54, 132, 0.2);
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../templates/navbar.php'; ?>
    
    <div class="container py-5">
        <!-- FAQ Header -->
        <div class="faq-header text-center">
            <div class="container">
                <h1 class="display-5">Foire Aux Questions</h1>
                <p class="lead">Trouvez rapidement des réponses aux questions les plus fréquentes</p>
                
                <!-- Back to Support button - only shows if coming from support page -->
                <?php if($fromSupport): ?>
                <div class="text-start mb-3">
                    <a href="support.php" class="btn btn-dark" style="background-color: #2b3684; border-color: #2b3684; font-weight: 600; border-radius: 30px; padding: 8px 20px; transition: all 0.3s ease;">
                        <i class="fas fa-arrow-left me-2"></i>Retour au Support
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- FAQ Search -->
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8 col-lg-6">
                        <div class="search-faq">
                            <i class="fas fa-search"></i>
                            <input type="text" id="faq-search" class="form-control" placeholder="Rechercher une question...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Content -->
        <div class="row">
            <div class="col-lg-3 mb-4">
                <!-- FAQ Navigation -->
                <div class="list-group sticky-top" style="top: 80px;">
                    <a href="#account" class="list-group-item list-group-item-action">Compte et Profil</a>
                    <a href="#orders" class="list-group-item list-group-item-action">Commandes et Livraison</a>
                    <a href="#returns" class="list-group-item list-group-item-action">Retours et Remboursements</a>
                    <a href="#products" class="list-group-item list-group-item-action">Produits et Stock</a>
                    <a href="#payment" class="list-group-item list-group-item-action">Paiement et Sécurité</a>
                </div>
            </div>
            
            <div class="col-lg-9">
                <!-- Account Questions -->
                <div id="account" class="faq-section">
                    <h2>Compte et Profil</h2>
                    <div class="accordion" id="accountAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="account1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account1Collapse" aria-expanded="false" aria-controls="account1Collapse">
                                    Comment créer un compte sur E-Souk?
                                </button>
                            </h2>
                            <div id="account1Collapse" class="accordion-collapse collapse" aria-labelledby="account1" data-bs-parent="#accountAccordion">
                                <div class="accordion-body">
                                    Pour créer un compte, cliquez sur "S'inscrire" dans le coin supérieur droit de notre site. Remplissez le formulaire avec vos informations personnelles, y compris votre nom, adresse e-mail et un mot de passe sécurisé. Après la soumission, vous recevrez un e-mail de confirmation pour activer votre compte.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="account2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account2Collapse" aria-expanded="false" aria-controls="account2Collapse">
                                    Comment modifier mes informations personnelles?
                                </button>
                            </h2>
                            <div id="account2Collapse" class="accordion-collapse collapse" aria-labelledby="account2" data-bs-parent="#accountAccordion">
                                <div class="accordion-body">
                                    Connectez-vous à votre compte, cliquez sur votre nom d'utilisateur dans le coin supérieur droit, puis sélectionnez "Mon profil". Vous pourrez modifier vos informations personnelles, y compris votre adresse, numéro de téléphone et préférences de communication.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="account3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account3Collapse" aria-expanded="false" aria-controls="account3Collapse">
                                    Comment réinitialiser mon mot de passe?
                                </button>
                            </h2>
                            <div id="account3Collapse" class="accordion-collapse collapse" aria-labelledby="account3" data-bs-parent="#accountAccordion">
                                <div class="accordion-body">
                                    Si vous avez oublié votre mot de passe, cliquez sur "Se connecter", puis sur "Mot de passe oublié". Entrez l'adresse e-mail associée à votre compte et suivez les instructions que vous recevrez par e-mail pour réinitialiser votre mot de passe.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Questions -->
                <div id="orders" class="faq-section">
                    <h2>Commandes et Livraison</h2>
                    <div class="accordion" id="ordersAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="order1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order1Collapse" aria-expanded="false" aria-controls="order1Collapse">
                                    Comment suivre ma commande?
                                </button>
                            </h2>
                            <div id="order1Collapse" class="accordion-collapse collapse" aria-labelledby="order1" data-bs-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    Vous pouvez suivre votre commande en vous connectant à votre compte et en visitant la section "Mes commandes". Cliquez sur le numéro de la commande que vous souhaitez suivre pour voir son statut actuel, y compris les informations d'expédition et le numéro de suivi si disponible.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="order2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order2Collapse" aria-expanded="false" aria-controls="order2Collapse">
                                    Quel est le délai de livraison standard?
                                </button>
                            </h2>
                            <div id="order2Collapse" class="accordion-collapse collapse" aria-labelledby="order2" data-bs-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    Notre délai de livraison standard est de 2 à 5 jours ouvrables pour les commandes domestiques. Pour les commandes internationales, comptez entre 7 et 14 jours ouvrables, selon la destination. Veuillez noter que ces délais peuvent varier en période de forte activité.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="order3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order3Collapse" aria-expanded="false" aria-controls="order3Collapse">
                                    Comment modifier ou annuler ma commande?
                                </button>
                            </h2>
                            <div id="order3Collapse" class="accordion-collapse collapse" aria-labelledby="order3" data-bs-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    Vous pouvez modifier ou annuler votre commande uniquement si elle n'a pas encore été expédiée. Pour ce faire, connectez-vous à votre compte, accédez à "Mes commandes", sélectionnez la commande concernée et cliquez sur "Modifier" ou "Annuler". Si l'option n'est pas disponible, veuillez nous contacter immédiatement au service clientèle.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Returns Questions -->
                <div id="returns" class="faq-section">
                    <h2>Retours et Remboursements</h2>
                    <div class="accordion" id="returnsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="return1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#return1Collapse" aria-expanded="false" aria-controls="return1Collapse">
                                    Quelle est la politique de retour?
                                </button>
                            </h2>
                            <div id="return1Collapse" class="accordion-collapse collapse" aria-labelledby="return1" data-bs-parent="#returnsAccordion">
                                <div class="accordion-body">
                                    Vous disposez de 30 jours à compter de la date de livraison pour retourner un article. Le produit doit être dans son état d'origine, non utilisé, avec toutes les étiquettes et emballages intacts. Certains articles, comme les produits personnalisés ou d'hygiène, ne sont pas éligibles au retour.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="return2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#return2Collapse" aria-expanded="false" aria-controls="return2Collapse">
                                    Comment effectuer un retour?
                                </button>
                            </h2>
                            <div id="return2Collapse" class="accordion-collapse collapse" aria-labelledby="return2" data-bs-parent="#returnsAccordion">
                                <div class="accordion-body">
                                    Pour effectuer un retour, connectez-vous à votre compte, accédez à "Mes commandes", sélectionnez la commande concernée et cliquez sur "Retourner". Suivez les instructions à l'écran pour générer une étiquette de retour et choisissez la méthode de remboursement préférée. Une fois votre retour traité, vous recevrez une confirmation par e-mail.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="return3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#return3Collapse" aria-expanded="false" aria-controls="return3Collapse">
                                    Combien de temps faut-il pour être remboursé?
                                </button>
                            </h2>
                            <div id="return3Collapse" class="accordion-collapse collapse" aria-labelledby="return3" data-bs-parent="#returnsAccordion">
                                <div class="accordion-body">
                                    Une fois que nous avons reçu et vérifié votre retour, le remboursement est généralement traité dans un délai de 3 à 5 jours ouvrables. Le temps nécessaire pour que les fonds apparaissent sur votre compte dépend de votre banque ou de votre mode de paiement, mais cela prend généralement entre 5 et 10 jours ouvrables.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Products Questions -->
                <div id="products" class="faq-section">
                    <h2>Produits et Stock</h2>
                    <div class="accordion" id="productsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="product1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#product1Collapse" aria-expanded="false" aria-controls="product1Collapse">
                                    Comment savoir si un produit est en stock?
                                </button>
                            </h2>
                            <div id="product1Collapse" class="accordion-collapse collapse" aria-labelledby="product1" data-bs-parent="#productsAccordion">
                                <div class="accordion-body">
                                    La disponibilité du stock est indiquée sur la page de chaque produit. Si un article est en stock, vous verrez l'option "Ajouter au panier". Si un produit est épuisé, il sera marqué comme "Rupture de stock" ou "Bientôt disponible" avec une option pour être notifié lorsqu'il sera de nouveau disponible.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="product2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#product2Collapse" aria-expanded="false" aria-controls="product2Collapse">
                                    D'où proviennent vos produits artisanaux?
                                </button>
                            </h2>
                            <div id="product2Collapse" class="accordion-collapse collapse" aria-labelledby="product2" data-bs-parent="#productsAccordion">
                                <div class="accordion-body">
                                    Tous nos produits artisanaux proviennent d'artisans tunisiens talentueux. Nous travaillons directement avec des artisans locaux de différentes régions de Tunisie pour vous offrir des produits authentiques et de haute qualité, tout en soutenant l'économie locale et en préservant les techniques traditionnelles.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="product3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#product3Collapse" aria-expanded="false" aria-controls="product3Collapse">
                                    Les dimensions des produits sont-elles exactes?
                                </button>
                            </h2>
                            <div id="product3Collapse" class="accordion-collapse collapse" aria-labelledby="product3" data-bs-parent="#productsAccordion">
                                <div class="accordion-body">
                                    Nous nous efforçons de fournir des dimensions précises pour tous nos produits. Cependant, comme il s'agit de produits artisanaux faits à la main, de légères variations peuvent exister. Si les dimensions exactes sont cruciales pour votre achat, n'hésitez pas à nous contacter avant de commander.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Questions -->
                <div id="payment" class="faq-section">
                    <h2>Paiement et Sécurité</h2>
                    <div class="accordion" id="paymentAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="payment1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment1Collapse" aria-expanded="false" aria-controls="payment1Collapse">
                                    Quels modes de paiement acceptez-vous?
                                </button>
                            </h2>
                            <div id="payment1Collapse" class="accordion-collapse collapse" aria-labelledby="payment1" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body">
                                    Nous acceptons plusieurs modes de paiement pour votre commodité: cartes de crédit (Visa, MasterCard), PayPal, virement bancaire et paiement à la livraison (pour certaines régions uniquement). Tous les paiements sont sécurisés et cryptés pour protéger vos informations.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="payment2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment2Collapse" aria-expanded="false" aria-controls="payment2Collapse">
                                    Mes informations de paiement sont-elles sécurisées?
                                </button>
                            </h2>
                            <div id="payment2Collapse" class="accordion-collapse collapse" aria-labelledby="payment2" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body">
                                    Absolument. Nous utilisons des technologies de cryptage SSL de pointe pour protéger vos informations de paiement. Nous ne stockons pas vos données de carte de crédit sur nos serveurs. Toutes les transactions sont traitées via des passerelles de paiement sécurisées qui respectent les normes PCI DSS.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="payment3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment3Collapse" aria-expanded="false" aria-controls="payment3Collapse">
                                    Quand ma carte de crédit sera-t-elle débitée?
                                </button>
                            </h2>
                            <div id="payment3Collapse" class="accordion-collapse collapse" aria-labelledby="payment3" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body">
                                    Votre carte de crédit sera débitée au moment où vous passez votre commande. Si pour une raison quelconque nous ne pouvons pas expédier votre commande ou si un article est en rupture de stock, vous serez remboursé immédiatement pour les articles non disponibles.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Still Have Questions -->
                <div class="still-have-questions">
                    <h3>Vous n'avez pas trouvé de réponse à votre question?</h3>
                    <p>Notre équipe de support client est là pour vous aider avec toutes vos questions.</p>
                    <a href="../user/support.php" class="btn btn-primary">
                        <i class="fas fa-question-circle me-2"></i>Contacter le Support
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include Footer -->
    <?php include '../templates/footer.php'; ?>
    
    <script>
        // FAQ Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('faq-search');
            
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const accordionButtons = document.querySelectorAll('.accordion-button');
                
                accordionButtons.forEach(button => {
                    const text = button.textContent.toLowerCase();
                    const accordionItem = button.closest('.accordion-item');
                    
                    if (text.includes(searchTerm)) {
                        accordionItem.style.display = '';
                        
                        // Highlight the search term if needed
                        if (searchTerm.length > 2) {
                            const buttonText = button.textContent;
                            const regex = new RegExp(searchTerm, 'gi');
                            button.innerHTML = buttonText.replace(regex, match => `<span class="highlight" style="background-color: #ffe082;">${match}</span>`);
                        }
                    } else {
                        accordionItem.style.display = 'none';
                    }
                });
                
                // Check if any FAQ sections are completely empty and hide their headers
                document.querySelectorAll('.faq-section').forEach(section => {
                    const visibleItems = section.querySelectorAll('.accordion-item[style="display: none;"]');
                    const allItems = section.querySelectorAll('.accordion-item');
                    
                    if (visibleItems.length === allItems.length) {
                        section.style.display = 'none';
                    } else {
                        section.style.display = '';
                    }
                });
            });
            
            // Smooth scroll to sections when clicking on navigation
            document.querySelectorAll('.list-group-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    window.scrollTo({
                        top: targetElement.offsetTop - 20,
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>