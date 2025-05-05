<?php
// Connexion à la base de données et démarrage de session
session_start();
require_once '../config/init.php';
$db = Database::getInstance();

// Vérifier si l'utilisateur est connecté et est administrateur
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialisation des variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Éléments par page
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Gestion de la mise à jour du statut de commande
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    try {
        $update_stmt = $db->prepare("UPDATE orders SET status = ? WHERE id_order = ?");
        $update_stmt->execute([$new_status, $order_id]);
        $status_message = "Statut de la commande #$order_id mis à jour en $new_status";
    } catch (PDOException $e) {
        $status_message = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}

// Obtenir les détails de la commande si sélectionnée
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    try {
        // Récupérer les produits de la commande
        $products_stmt = $db->prepare("
            SELECT op.*, p.title as name, p.image, op.unit_price 
            FROM order_product op
            JOIN product p ON op.product_id = p.id_product
            WHERE op.order_id = ?
        ");
        $products_stmt->execute([$order_id]);
        $order_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les informations de la commande
        $order_stmt = $db->prepare("
            SELECT o.*, u.name as customer_name, u.address, u.phone, u.email
            FROM orders o
            JOIN user u ON o.user_id = u.id_user
            WHERE o.id_order = ?
        ");
        $order_stmt->execute([$order_id]);
        $order_info = $order_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Compter le nombre total d'articles dans la commande
        $total_items = 0;
        foreach ($order_products as $product) {
            $total_items += $product['quantity'];
        }
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la récupération des détails de la commande: " . $e->getMessage();
    }
}

// Construire les conditions de la requête pour le filtrage
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(o.id_order LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Compter le nombre total de commandes pour la pagination
try {
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM orders o 
        JOIN user u ON o.user_id = u.id_user
        $where_clause
    ";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_orders / $limit);
} catch (PDOException $e) {
    $error_message = "Erreur lors du comptage des commandes: " . $e->getMessage();
    $total_orders = 0;
    $total_pages = 1;
}

// Récupérer toutes les commandes avec les infos clients
try {
    $orders_sql = "
        SELECT o.id_order as order_id, o.status, o.total_price, o.order_date, u.name as customer_name, 
               u.email, COUNT(op.product_id) as item_count, SUM(op.quantity) as total_items
        FROM orders o
        JOIN user u ON o.user_id = u.id_user
        LEFT JOIN order_product op ON o.id_order = op.order_id
        $where_clause
        GROUP BY o.id_order
        ORDER BY o.order_date DESC
        LIMIT $limit OFFSET $offset
    ";
    $orders_stmt = $db->prepare($orders_sql);
    $orders_stmt->execute($params);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des commandes: " . $e->getMessage();
    $orders = [];
}

// Obtenir la liste des statuts disponibles pour le filtre
$statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Panneau d'Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/admin.css" />
    <link rel="stylesheet" href="./css/orders.css" />
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Barre latérale -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Contenu principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                    <h1 class="h2">Gestion des Commandes</h1>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
                
                <?php if (isset($status_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $status_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Cartes récapitulatives des commandes -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Total des Commandes</h6>
                                <h3><?php echo $total_orders; ?></h3>
                            </div>
                        </div>
                    </div>
                    <?php
                    // Obtenir le nombre de commandes par statut
                    $status_counts = [
                        'Pending' => 0,
                        'Processing' => 0,
                        'Shipped' => 0,
                        'Completed' => 0,
                        'Cancelled' => 0
                    ];
                    
                    try {
                        $status_stmt = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
                        while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
                            if (isset($status_counts[$row['status']])) {
                                $status_counts[$row['status']] = $row['count'];
                            }
                        }
                    } catch (PDOException $e) {
                        // Échouer silencieusement
                    }
                    ?>
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <h6 class="text-muted">Commandes en Attente</h6>
                                <h3><?php echo $status_counts['Pending']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100" style="border-left-color: #0dcaf0;">
                            <div class="card-body">
                                <h6 class="text-muted">Commandes en Traitement</h6>
                                <h3><?php echo $status_counts['Processing']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card order-summary-card h-100" style="border-left-color: #198754;">
                            <div class="card-body">
                                <h6 class="text-muted">Commandes Terminées</h6>
                                <h3><?php echo $status_counts['Completed']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recherche et Filtrage -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Rechercher des commandes..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Rechercher
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">Tous les Statuts</option>
                                    <?php 
                                    $status_labels = [
                                        'Pending' => 'En Attente',
                                        'Processing' => 'En Traitement',
                                        'Shipped' => 'Expédiée',
                                        'Completed' => 'Terminée',
                                        'Cancelled' => 'Annulée'
                                    ];
                                    foreach ($statuses as $status): 
                                    ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($status_filter === $status ? 'selected' : ''); ?>>
                                            <?php echo $status_labels[$status]; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="orders.php" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tableau des Commandes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Commandes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Client</th>
                                        <th>Articles</th>
                                        <th>Statut</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td><?php echo $order['total_items'] ?? 0; ?> articles</td>
                                        <td>
                                            <?php 
                                                $status_color = 'secondary';
                                                $status_icon = 'circle-question';
                                                
                                                switch($order['status']) {
                                                    case 'Pending':
                                                        $status_color = 'warning';
                                                        $status_icon = 'clock';
                                                        $display_status = 'En Attente';
                                                        break;
                                                    case 'Processing':
                                                        $status_color = 'info';
                                                        $status_icon = 'gear';
                                                        $display_status = 'En Traitement';
                                                        break;
                                                    case 'Shipped':
                                                        $status_color = 'primary';
                                                        $status_icon = 'truck';
                                                        $display_status = 'Expédiée';
                                                        break;
                                                    case 'Completed':
                                                        $status_color = 'success';
                                                        $status_icon = 'check-circle';
                                                        $display_status = 'Terminée';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_color = 'danger';
                                                        $status_icon = 'times-circle';
                                                        $display_status = 'Annulée';
                                                        break;
                                                    default:
                                                        $display_status = $order['status'];
                                                }
                                            ?>
                                            <span class="status-badge bg-<?php echo $status_color; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                                <?php echo $display_status; ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($order['total_price'], 2); ?> DT</td>
                                        <td>
                                            <?php 
                                                $order_date = new DateTime($order['order_date']);
                                                echo $order_date->format('d M Y'); 
                                                echo '<br><small class="text-muted">' . $order_date->format('H:i') . '</small>';
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?order_id=<?php echo $order['order_id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <p>Aucune commande trouvée</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Pagination des commandes" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">Précédent</a>
                                </li>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . '">' . $i . '</a></li>';
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status_filter) ? '&status=' . urlencode($status_filter) : '') . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">Suivant</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Section Détails de Commande (affichée lorsqu'une commande est sélectionnée) -->
                <?php if (isset($order_info)): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Détails de la Commande #<?php echo $order_info['id_order']; ?></h5>
                            <small class="text-muted">Passée le <?php echo date('d F Y \à H:i', strtotime($order_info['order_date'])); ?></small>
                        </div>
                        <div>
                            <a href="print_order.php?id=<?php echo $order_info['id_order']; ?>" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                                <i class="fas fa-print"></i> Imprimer la Facture
                            </a>
                            <a href="orders.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? (!empty($search) ? '&' : '?') . 'status=' . urlencode($status_filter) : ''; ?><?php echo (!empty($search) || !empty($status_filter)) ? '&' : '?'; ?>page=<?php echo $page; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Retour aux Commandes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-3 text-muted">Informations Client</h6>
                                        <p><strong>Nom:</strong> <?php echo htmlspecialchars($order_info['customer_name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order_info['email']); ?></p>
                                        <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($order_info['phone']); ?></p>
                                        <p class="mb-0"><strong>Adresse de Livraison:</strong><br><?php echo nl2br(htmlspecialchars($order_info['address'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-3 text-muted">Résumé de la Commande</h6>
                                        <p><strong>N° Commande:</strong> #<?php echo $order_info['id_order']; ?></p>
                                        <p><strong>Articles:</strong> <?php echo $total_items; ?> articles</p>
                                        <p>
                                            <strong>Statut:</strong> 
                                            <?php 
                                                $status_color = 'secondary';
                                                $status_icon = 'circle-question';
                                                $display_status = $order_info['status'];
                                                
                                                switch($order_info['status']) {
                                                    case 'Pending':
                                                        $status_color = 'warning';
                                                        $status_icon = 'clock';
                                                        $display_status = 'En Attente';
                                                        break;
                                                    case 'Processing':
                                                        $status_color = 'info';
                                                        $status_icon = 'gear';
                                                        $display_status = 'En Traitement';
                                                        break;
                                                    case 'Shipped':
                                                        $status_color = 'primary';
                                                        $status_icon = 'truck';
                                                        $display_status = 'Expédiée';
                                                        break;
                                                    case 'Completed':
                                                        $status_color = 'success';
                                                        $status_icon = 'check-circle';
                                                        $display_status = 'Terminée';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_color = 'danger';
                                                        $status_icon = 'times-circle';
                                                        $display_status = 'Annulée';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge bg-<?php echo $status_color; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                                <?php echo $display_status; ?>
                                            </span>
                                        </p>
                                        <p class="h5 mb-0"><strong>Total:</strong> <?php echo number_format($order_info['total_price'], 2); ?> DT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulaire de mise à jour de statut -->
                        <form method="post" class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">Mettre à jour le Statut</h6>
                                <input type="hidden" name="order_id" value="<?php echo $order_info['id_order']; ?>">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <select name="new_status" id="new_status" class="form-select">
                                            <option value="Pending" <?php echo ($order_info['status'] == 'Pending') ? 'selected' : ''; ?>>En Attente</option>
                                            <option value="Processing" <?php echo ($order_info['status'] == 'Processing') ? 'selected' : ''; ?>>En Traitement</option>
                                            <option value="Shipped" <?php echo ($order_info['status'] == 'Shipped') ? 'selected' : ''; ?>>Expédiée</option>
                                            <option value="Completed" <?php echo ($order_info['status'] == 'Completed') ? 'selected' : ''; ?>>Terminée</option>
                                            <option value="Cancelled" <?php echo ($order_info['status'] == 'Cancelled') ? 'selected' : ''; ?>>Annulée</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Mettre à Jour
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Produits de la commande -->
                        <h6 class="mb-3">Produits de la Commande</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th width="15%">Prix Unitaire</th>
                                        <th width="15%">Quantité</th>
                                        <th width="15%">Sous-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($product['image'])): ?>
                                                <div class="product-img-container me-3">
                                                    <img src="../root_uploads/products/<?php echo $product['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div><?php echo htmlspecialchars($product['name']); ?></div>
                                                    <small class="text-muted">ID: <?php echo $product['product_id']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($product['unit_price'], 2); ?> DT</td>
                                        <td><?php echo $product['quantity']; ?></td>
                                        <td><?php echo number_format($product['unit_price'] * $product['quantity'], 2); ?> DT</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th><?php echo number_format($order_info['total_price'], 2); ?> DT</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </main>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>