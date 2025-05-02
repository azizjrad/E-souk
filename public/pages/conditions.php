<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';

$page_title = "Conditions Générales de Vente - E-Souk Tounsi";
$page_description = "Consultez nos conditions générales de vente, politique de livraison et autres informations légales.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/conditions.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include '../templates/navbar.php'; ?>

    <div class="conditions-container">
        <div class="conditions-header">
            <h1 class="section-title">Conditions Générales de Vente</h1>
            <hr class="mx-auto mb-5" style="width: 60px; border: 2px solid #fcd34d">
        </div>
        
        <div class="conditions-content">
            <div class="conditions-section">
                <h2>1. Introduction</h2>
                <p>Bienvenue sur E-Souk Tounsi, la plateforme dédiée à l'artisanat tunisien authentique. Les présentes conditions générales de vente régissent l'utilisation de notre site web, accessible à l'adresse www.e-souk-tounsi.com.</p>
                <p>En passant commande sur notre site, vous acceptez expressément et sans réserve l'intégralité des présentes conditions générales de vente. Nous vous invitons à les lire attentivement avant toute utilisation de nos services.</p>
            </div>
            
            <div class="conditions-section">
                <h2>2. Produits et Services</h2>
                <p>E-Souk Tounsi propose une sélection de produits artisanaux tunisiens authentiques. Les photographies, textes, informations et caractéristiques illustrant les produits sont fournis à titre indicatif et ne sont pas contractuels.</p>
                
                <h3>2.1 Disponibilité des produits</h3>
                <p>Nos offres de produits sont valables tant qu'ils sont visibles sur le site et dans la limite des stocks disponibles. En cas d'indisponibilité après passation de votre commande, nous vous en informerons par email et vous proposerons soit d'attendre le réapprovisionnement, soit d'annuler votre commande.</p>
                
                <h3>2.2 Authenticité et qualité</h3>
                <p>Chaque produit proposé sur notre plateforme est créé par des artisans tunisiens. Des variations mineures peuvent exister entre le produit photographié et celui livré, caractéristiques de l'artisanat fait main.</p>
            </div>
            
            <div class="conditions-section">
                <h2>3. Prix et Paiement</h2>
                <p>Les prix de nos produits sont indiqués en Dinars Tunisiens (DT) toutes taxes comprises. Les frais de livraison sont facturés en supplément et indiqués avant la validation de la commande.</p>
                
                <h3>3.1 Méthodes de paiement</h3>
                <p>Nous acceptons les modes de paiement suivants :</p>
                <ul>
                    <li>Paiement à la livraison</li>
                    <li>Carte bancaire (Visa, Mastercard)</li>
                </ul>
                
                <h3>3.2 Sécurité des paiements</h3>
                <p>Toutes les transactions effectuées par carte bancaire sur notre site sont sécurisées. Les informations transmises sont cryptées et ne peuvent être interceptées.</p>
            </div>
            
            <div class="conditions-section">
                <h2>4. Livraison</h2>
                <p>Nous livrons dans toute la Tunisie et dans certains pays du Maghreb. Le délai de livraison standard est de 3 à 10 jours ouvrables selon votre localisation géographique.</p>
                
                <h3>4.1 Frais de livraison</h3>
                <p>Les frais de livraison sont calculés en fonction du poids, du volume et de la destination de votre commande. Ils vous sont communiqués avant la validation de votre commande.</p>
                
                <h3>4.2 Suivi de commande</h3>
                <p>Un numéro de suivi vous sera communiqué par email dès l'expédition de votre commande, vous permettant de suivre son acheminement.</p>
            </div>
            
            <div class="conditions-section">
                <h2>5. Droit de rétractation et retours</h2>
                <p>Conformément à la législation en vigueur, vous disposez d'un délai de 14 jours à compter de la réception de votre commande pour exercer votre droit de rétractation, sans avoir à justifier de motifs ni à payer de pénalités.</p>
                
                <h3>5.1 Modalités de retour</h3>
                <p>Pour retourner un article, veuillez nous contacter préalablement par email à service-client@e-souk-tounsi.com. Les frais de retour sont à votre charge sauf en cas d'erreur de notre part ou de produit défectueux.</p>
                
                <h3>5.2 Conditions des produits retournés</h3>
                <p>Les produits retournés doivent être dans leur état d'origine, complets, et accompagnés de leur emballage original. Tout produit incomplet, abîmé, ou dont l'emballage aurait été détérioré ne sera ni remboursé ni échangé.</p>
            </div>
            
            <div class="conditions-section">
                <h2>6. Protection des données personnelles</h2>
                <p>La protection de vos données personnelles est notre priorité. Les informations recueillies font l'objet d'un traitement informatique destiné à la gestion de votre commande et, si vous y avez consenti, à vous envoyer des offres commerciales.</p>
                <p>Conformément à la réglementation en vigueur, vous disposez d'un droit d'accès, de rectification et d'opposition aux informations qui vous concernent. Vous pouvez exercer ces droits en nous contactant à l'adresse privacy@e-souk-tounsi.com.</p>
            </div>
            
            <div class="conditions-section">
                <h2>7. Propriété intellectuelle</h2>
                <p>L'ensemble des éléments du site (textes, illustrations, photographies, etc.) est la propriété exclusive de E-Souk Tounsi ou de ses partenaires. Toute reproduction, représentation ou diffusion, totale ou partielle, du contenu de ce site par quelque procédé que ce soit, sans l'autorisation expresse de E-Souk Tounsi est interdite et constituerait une contrefaçon.</p>
            </div>
            
            <div class="conditions-section">
                <h2>8. Règlement des litiges</h2>
                <p>En cas de litige, une solution amiable sera recherchée avant toute action judiciaire. Si aucun accord n'est trouvé, le litige sera soumis aux tribunaux compétents de Tunis, conformément à la législation tunisienne.</p>
            </div>
            
            <div class="contact-info">
                <h3>Nous contacter</h3>
                <p>Pour toute question relative à ces conditions générales de vente, vous pouvez nous contacter :</p>
                <p>Email : contact@e-souk-tounsi.com</p>
                <p>Téléphone : +216 XX XXX XXX</p>
                <p>Adresse : Rue de l'Artisanat, Tunis, Tunisie</p>
            </div>
            
            <p class="last-updated">Dernière mise à jour : Mai 2023</p>
            
            <div class="text-center">
                <a href="javascript:history.back()" class="back-button">Retour</a>
            </div>
        </div>
    </div>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/footer.php'; ?>
</body>
</html>