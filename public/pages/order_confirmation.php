<?php
session_start();
require_once __DIR__ . '/../../config/init.php';

$page_title = "Confirmation de commande - E-Souk Tounsi";
$page_description = "Votre commande a été confirmée. Merci pour votre achat!";

// Redirect if not coming from a successful order
if (!isset($_SESSION['order_success']) || !isset($_SESSION['order_id'])) {
    header('Location: index.php');
    exit;
}

// Get order details
$order_id = $_SESSION['order_id'];
$stmt = $db->prepare("SELECT o.*, u.name, u.email, u.phone, u.address
                     FROM orders o 
                     JOIN user u ON o.user_id = u.id_user 
                     WHERE o.id_order = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order items
$stmt = $db->prepare("SELECT op.*, p.title, p.image 
                     FROM order_product op 
                     JOIN product p ON op.product_id = p.id_product 
                     WHERE op.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clear session variables
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include '../templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <link rel="stylesheet" href="../assets/css/order_confirmation.css">
    <style>
        /* Internal styles for order confirmation page */
        :root {
            --primary-color: #2b3684;
            --hover-color: #232c6a;
        }
        
        .order-confirmation {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 30px;
        }
        
        .confirmation-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
            display: block;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .order-confirmation h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .order-confirmation .lead {
            color: #555;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .order-details, .order-items {
            margin-top: 40px;
            border-top: 1px solid #eee;
            padding-top: 30px;
        }
        
        .order-details h2, .order-items h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
        }
        
        .order-details h2::after, .order-items h2::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .order-details h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 15px;
        }
        
        .order-details p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 8px;
        }
        
        .order-details strong {
            color: #333;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid #eee;
            padding: 12px;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
        }
        
        .table img {
            border-radius: 5px;
            margin-right: 12px;
            border: 1px solid #eee;
            transition: transform 0.3s ease;
        }
        
        .table img:hover {
            transform: scale(1.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            padding: 10px 30px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(43, 54, 132, 0.2);
        }
        
        @media (max-width: 768px) {
            .order-confirmation {
                padding: 20px;
            }
            
            .confirmation-icon {
                font-size: 4rem;
            }
            
            .order-details, .order-items {
                margin-top: 30px;
                padding-top: 20px;
            }
        }

        /* Fix for alignment in the product column */
        .d-flex.align-items-center span {
            margin-left: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include '../templates/navbar.php'; ?>

    <div class="container my-5">
        <div class="order-confirmation">
            <div class="text-center mb-5">
                <i class="fas fa-check-circle confirmation-icon"></i>
                <h1>Commande Confirmée</h1>
                <p class="lead">Merci pour votre achat! Votre commande #<?= $order_id ?> a été traitée avec succès.</p>
            </div>
            
            <div class="order-details">
                <h2>Détails de la commande</h2>
                <div class="row">
                    <div class="col-md-6">
                        <h3>Informations de livraison</h3>
                        <p><strong>Nom:</strong> <?= htmlspecialchars($order['name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                        <p><strong>Téléphone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                        <p><strong>Adresse:</strong> <?= htmlspecialchars($order['address']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h3>Résumé de la commande</h3>
                        <p><strong>Numéro de commande:</strong> #<?= $order_id ?></p>
                        <p><strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
                        <p><strong>Statut:</strong> <span class="badge bg-success text-white"><?= htmlspecialchars($order['status']) ?></span></p>
                        <p><strong>Total:</strong> <span class="fw-bold text-primary"><?= number_format($order['total_price'], 2) ?> DT</span></p>
                    </div>
                </div>
            </div>
            
            <div class="order-items mt-4">
                <h2>Articles commandés</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($item['image']) ? '../../root_uploads/products/' . htmlspecialchars($item['image']) : '../assets/images/product-placeholder.jpg' ?>" 
                                                alt="<?= htmlspecialchars($item['title']) ?>" width="50">
                                            <span><?= htmlspecialchars($item['title']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['unit_price'], 2) ?> DT</td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td class="fw-bold"><?= number_format($item['unit_price'] * $item['quantity'], 2) ?> DT</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="product.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Continuer vos achats
                </a>
            </div>
        </div>
    </div>

    <?php include '../templates/footer.php'; ?>
</body>
</html>
``` 
