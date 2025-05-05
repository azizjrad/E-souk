<?php 
include 'includes/header.php';

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$db = Database::getInstance();

// Traitement de la mise à jour du profil
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour des informations personnelles
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        
        // Vérification de l'email
        if ($email !== $_SESSION['user_email']) {
            $check_email = $db->prepare("SELECT * FROM user WHERE email = :email AND id_user != :user_id");
            $check_email->bindParam(':email', $email);
            $check_email->bindParam(':user_id', $user_id);
            $check_email->execute();
            
            if ($check_email->rowCount() > 0) {
                $error_message = "Cette adresse email est déjà utilisée.";
            }
        }
        
        if (empty($error_message)) {
            try {
                $stmt = $db->prepare("UPDATE user SET name = :name, email = :email, address = :address, phone = :phone WHERE id_user = :user_id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Mettre à jour les informations de session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $success_message = "Votre profil a été mis à jour avec succès!";
            } catch (PDOException $e) {
                $error_message = "Erreur lors de la mise à jour du profil: " . $e->getMessage();
            }
        }
    }
    
    // Mise à jour du mot de passe
    else if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Vérifier si le mot de passe actuel est correct
        $stmt = $db->prepare("SELECT password FROM user WHERE id_user = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if (!password_verify($current_password, $user['password'])) {
            $error_message = "Le mot de passe actuel est incorrect.";
        } 
        else if ($new_password !== $confirm_password) {
            $error_message = "Les nouveaux mots de passe ne correspondent pas.";
        } 
        else if (strlen($new_password) < 8) {
            $error_message = "Le mot de passe doit contenir au moins 8 caractères.";
        } 
        else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE user SET password = :password WHERE id_user = :user_id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                $success_message = "Votre mot de passe a été mis à jour avec succès!";
            } catch (PDOException $e) {
                $error_message = "Erreur lors de la mise à jour du mot de passe: " . $e->getMessage();
            }
        }
    }
    
    // Mise à jour de la photo de profil
    else if (isset($_POST['update_avatar'])) {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['avatar']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($ext), $allowed)) {
                $error_message = "Format de fichier non autorisé. Veuillez utiliser JPG, PNG ou GIF.";
            } else if ($_FILES['avatar']['size'] > 2097152) { // 2MB max
                $error_message = "Le fichier est trop volumineux. Taille maximale: 2MB.";
            } else {
                $new_filename = uniqid() . '.' . $ext;
                $upload_dir = '../uploads/avatars/';
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                    try {
                        // Récupérer l'ancienne image pour la supprimer
                        $stmt = $db->prepare("SELECT image FROM user WHERE id_user = :user_id");
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $old_image = $stmt->fetchColumn();
                        
                        // Mettre à jour la base de données
                        $stmt = $db->prepare("UPDATE user SET image = :image WHERE id_user = :user_id");
                        $stmt->bindParam(':image', $new_filename);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        
                        // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
                        if (!empty($old_image) && file_exists($upload_dir . $old_image) && $old_image != 'default.jpg') {
                            unlink($upload_dir . $old_image);
                        }
                        
                        $_SESSION['user_avatar'] = $new_filename;
                        $success_message = "Votre photo de profil a été mise à jour avec succès!";
                    } catch (PDOException $e) {
                        $error_message = "Erreur lors de la mise à jour de la photo de profil: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Erreur lors de l'upload du fichier.";
                }
            }
        } else {
            $error_message = "Veuillez sélectionner une image.";
        }
    }
}

// Récupérer les informations à jour de l'utilisateur
$stmt = $db->prepare("SELECT * FROM user WHERE id_user = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer quelques statistiques pour le tableau de bord
$stats = [
    'orders' => 0,
    'products' => 0,
    'customers' => 0,
    'admin_since' => ''
];

// Nombre de commandes
$stmt = $db->query("SELECT COUNT(*) FROM orders");
$stats['orders'] = $stmt->fetchColumn();

// Nombre de produits
$stmt = $db->query("SELECT COUNT(*) FROM product");
$stats['products'] = $stmt->fetchColumn();

// Nombre de clients
$stmt = $db->query("SELECT COUNT(*) FROM user WHERE role = 'customer'");
$stats['customers'] = $stmt->fetchColumn();

// Date d'inscription formatée
$stats['admin_since'] = date('d/m/Y', strtotime($user['created_at']));
?>

<div class="profile-container">
    <!-- Notifications -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="profile-header">
        <div class="profile-cover"></div>
        <div class="profile-user-info">
            <div class="profile-avatar">
                <?php if (!empty($user['image']) && file_exists('../uploads/avatars/' . $user['image'])): ?>
                    <img src="<?php echo '../uploads/avatars/' . $user['image']; ?>" alt="Profile Avatar">
                <?php else: ?>
                    <div class="profile-avatar-text"><?php echo substr($user['name'], 0, 1); ?></div>
                <?php endif; ?>
                
                <button class="profile-avatar-edit" data-bs-toggle="modal" data-bs-target="#avatarModal">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <div class="profile-details">
                <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="profile-role"><i class="fas fa-user-shield me-2"></i>Administrateur</p>
                <div class="profile-meta">
                    <span><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                    <span><i class="fas fa-phone me-1"></i> <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Non spécifié'; ?></span>
                    <span><i class="fas fa-clock me-1"></i> Membre depuis <?php echo $stats['admin_since']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-icon orders">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['orders']; ?></div>
                    <div class="stat-label">Commandes</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['products']; ?></div>
                    <div class="stat-label">Produits</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon customers">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $stats['customers']; ?></div>
                    <div class="stat-label">Clients</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="profile-sidebar">
                    <div class="sidebar-section">
                        <h4 class="section-title">Actions rapides</h4>
                        <div class="action-buttons">
                            <a href="index.php" class="action-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Tableau de bord</span>
                            </a>
                            <a href="orders.php" class="action-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Gérer les commandes</span>
                            </a>
                            <a href="product.php" class="action-btn">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ajouter un produit</span>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>" target="_blank" class="action-btn">
                                <i class="fas fa-external-link-alt"></i>
                                <span>Voir le site</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="sidebar-section">
                        <h4 class="section-title">Sécurité du compte</h4>
                        <div class="security-info">
                            <div class="security-item">
                                <div class="security-label">Mot de passe</div>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                    Modifier <i class="fas fa-key ms-1"></i>
                                </button>
                            </div>
                            <div class="security-item">
                                <div class="security-label">Dernière connexion</div>
                                <div class="security-value"><?php echo date('d/m/Y H:i'); ?></div>
                            </div>
                            <div class="security-item">
                                <div class="security-label">Rôle</div>
                                <div class="security-value">Administrateur</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="profile-edit-card">
                    <div class="card-header">
                        <h4><i class="fas fa-user-edit me-2"></i> Modifier votre profil</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" id="profile-form">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="address" class="form-label">Adresse</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                </button>
                                <button type="reset" class="btn btn-light">
                                    <i class="fas fa-undo me-1"></i> Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour changer la photo de profil -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">Changer votre photo de profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data" id="avatar-form">
                    <div class="text-center mb-4">
                        <div class="avatar-preview">
                            <?php if (!empty($user['image']) && file_exists('../uploads/avatars/' . $user['image'])): ?>
                                <img src="<?php echo '../uploads/avatars/' . $user['image']; ?>" id="avatar-preview-img" alt="Preview">
                            <?php else: ?>
                                <div class="avatar-preview-text" id="avatar-preview-text"><?php echo substr($user['name'], 0, 1); ?></div>
                                <img src="" id="avatar-preview-img" alt="Preview" style="display:none;">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Sélectionnez une nouvelle image</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" onchange="previewImage()">
                        <div class="form-text">Formats autorisés: JPG, PNG, GIF. Taille max: 2MB</div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="update_avatar" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour changer le mot de passe -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Changer votre mot de passe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="password-form">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength mt-2">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="strength-text">Sécurité: Trop court</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text password-match"></div>
                    </div>
                    
                    <div class="password-rules mb-3">
                        <small class="text-muted d-block mb-1">Votre mot de passe doit :</small>
                        <div class="rule"><i class="fas fa-check-circle text-muted"></i> Contenir au moins 8 caractères</div>
                        <div class="rule"><i class="fas fa-check-circle text-muted"></i> Contenir au moins une majuscule</div>
                        <div class="rule"><i class="fas fa-check-circle text-muted"></i> Contenir au moins un chiffre</div>
                        <div class="rule"><i class="fas fa-check-circle text-muted"></i> Contenir au moins un caractère spécial</div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="update_password" class="btn btn-primary">Changer le mot de passe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
/* Styles pour la page de profil administrateur */
:root {
    --profile-primary: #4f46e5;
    --profile-primary-hover: #4338ca;
    --profile-success: #10b981;
    --profile-warning: #f59e0b;
    --profile-danger: #ef4444;
    --profile-info: #3b82f6;
    --profile-light: #f9fafb;
    --profile-dark: #111827;
    --profile-gray: #6b7280;
    --profile-border: #e5e7eb;
}

.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem 0;
}

/* Header du profil */
.profile-header {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    margin-bottom: 2rem;
    position: relative;
}

.profile-cover {
    height: 180px;
    background: linear-gradient(120deg, var(--profile-primary), #818cf8);
    position: relative;
}

.profile-cover::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTEuMTExLDExLjExMSBMODguODg5LDExLjExMSBMODguODg5LDg4Ljg4OSBMMTEuMTExLDg4Ljg4OSBMMTEuMTExLDExLjExMSBaIiBzdHJva2Utd2lkdGg9IjAuMiIgc3Ryb2tlPSIjZmZmZmZmMjAiIGZpbGwtb3BhY2l0eT0iMCIvPjwvc3ZnPg==') repeat;
    opacity: 0.2;
}

.profile-user-info {
    display: flex;
    padding: 1.5rem;
    position: relative;
    z-index: 1;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: #f3f4f6;
    border: 4px solid white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: -60px;
    position: relative;
    overflow: hidden;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-text {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(45deg, var(--profile-primary), #818cf8);
}

.profile-avatar-edit {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--profile-primary);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-avatar-edit:hover {
    background: var(--profile-primary);
    color: white;
    transform: scale(1.1);
}

.profile-details {
    margin-left: 1.5rem;
    flex-grow: 1;
}

.profile-name {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--profile-dark);
}

.profile-role {
    color: var(--profile-primary);
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--profile-gray);
}

.profile-meta span {
    display: flex;
    align-items: center;
}

/* Stats cards */
.profile-stats {
    display: flex;
    justify-content: space-between;
    padding: 0 1.5rem 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    flex: 1;
    min-width: 200px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--profile-border);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-right: 1rem;
}

.stat-icon.orders {
    background-color: rgba(235, 153, 28, 0.1);
    color: #eab308;
}

.stat-icon.products {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.stat-icon.customers {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--profile-dark);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--profile-gray);
}

/* Content section */
.profile-content {
    margin-top: 2rem;
}

/* Sidebar */
.profile-sidebar {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.sidebar-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--profile-border);
}

.sidebar-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--profile-dark);
}

.action-buttons {
    display: grid;
    gap: 0.75rem;
}

.action-btn {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-radius: 8px;
    color: var(--profile-dark);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    font-weight: 500;
}

.action-btn:hover {
    background: var(--profile-primary);
    color: white;
    transform: translateX(3px);
}

.action-btn i {
    margin-right: 0.75rem;
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.security-info {
    display: grid;
    gap: 1rem;
}

.security-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.security-label {
    font-size: 0.875rem;
    color: var(--profile-gray);
}

.security-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--profile-dark);
}

/* Profile edit card */
.profile-edit-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--profile-border);
    background: white;
}

.card-header h4 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--profile-dark);
    display: flex;
    align-items: center;
}

.card-body {
    padding: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    justify-content: flex-end;
}

/* Avatar modal preview */
.avatar-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 4px solid white;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-preview-text {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    font-weight: 700;
    color: white;
    background: linear-gradient(45deg, var(--profile-primary), #818cf8);
}

/* Password strength indicator */
.password-strength {
    margin-top: 0.5rem;
}

.password-strength .progress {
    height: 4px;
    margin-bottom: 4px;
}

.strength-text {
    font-size: 0.75rem;
    color: var(--profile-gray);
}

.password-rules .rule {
    font-size: 0.75rem;
    color: var(--profile-gray);
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
}

.password-rules .rule i {
    margin-right: 0.5rem;
    width: 14px;
}

.password-rules .rule.valid i {
    color: var(--profile-success);
}

/* Dark mode support */
.dark-mode .profile-header,
.dark-mode .profile-sidebar,
.dark-mode .profile-edit-card,
.dark-mode .modal-content,
.dark-mode .stat-card {
    background-color: var(--dark-800, #1e293b);
    border-color: var(--dark-600, #334155);
}

.dark-mode .profile-name,
.dark-mode .section-title,
.dark-mode .card-header h4,
.dark-mode .stat-value,
.dark-mode .security-value {
    color: var(--light, #f9fafb);
}

.dark-mode .profile-meta,
.dark-mode .security-label {
    color: var(--gray-400, #9ca3af);
}

.dark-mode .card-header,
.dark-mode .sidebar-section {
    border-color: var(--dark-600, #334155);
}

.dark-mode .action-btn {
    background-color: rgba(255, 255, 255, 0.05);
    color: #e5e7eb;
}

.dark-mode .action-btn:hover {
    background-color: var(--primary-dark, #4338ca);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .profile-stats {
        grid-template-columns: repeat(1, 1fr);
    }
}

@media (max-width: 768px) {
    .profile-user-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-details {
        margin-left: 0;
        margin-top: 1rem;
    }
    
    .profile-meta {
        justify-content: center;
    }
    
    .profile-stats {
        justify-content: center;
    }
    
    .stat-card {
        min-width: 100%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions button {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview de l'image lors de l'upload
    function previewImage() {
        const input = document.getElementById('avatar');
        const previewImg = document.getElementById('avatar-preview-img');
        const previewText = document.getElementById('avatar-preview-text');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                if (previewText) {
                    previewText.style.display = 'none';
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Rendre la fonction accessible globalement
    window.previewImage = previewImage;
    
    // Toggle pour afficher/masquer les mots de passe
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Vérification de la force du mot de passe
    const newPassword = document.getElementById('new_password');
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            const password = this.value;
            const progressBar = document.querySelector('.password-strength .progress-bar');
            const strengthText = document.querySelector('.strength-text');
            const rules = document.querySelectorAll('.password-rules .rule');
            
            // Critères
            const hasMinLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            // Mise à jour des règles visuelles
            rules[0].classList.toggle('valid', hasMinLength);
            rules[0].querySelector('i').className = hasMinLength ? 'fas fa-check-circle text-success' : 'fas fa-check-circle text-muted';
            
            rules[1].classList.toggle('valid', hasUppercase);
            rules[1].querySelector('i').className = hasUppercase ? 'fas fa-check-circle text-success' : 'fas fa-check-circle text-muted';
            
            rules[2].classList.toggle('valid', hasNumber);
            rules[2].querySelector('i').className = hasNumber ? 'fas fa-check-circle text-success' : 'fas fa-check-circle text-muted';
            
            rules[3].classList.toggle('valid', hasSpecial);
            rules[3].querySelector('i').className = hasSpecial ? 'fas fa-check-circle text-success' : 'fas fa-check-circle text-muted';
            
            // Calcul de la force du mot de passe (0-100)
            let strength = 0;
            
            if (password.length > 0) strength += 5;
            if (password.length >= 8) strength += 15;
            if (password.length >= 12) strength += 10;
            
            if (hasUppercase) strength += 20;
            if (hasNumber) strength += 20;
            if (hasSpecial) strength += 20;
            
            // Patterns répétitifs réduisent la force
            if (/(.)\1{2,}/.test(password)) strength -= 10;
            
            // Séquences communes réduisent la force
            if (/123|abc|qwerty|admin|password/i.test(password)) strength -= 10;
            
            // Ajuster en cas de dépassement
            strength = Math.max(0, Math.min(100, strength));
            
            // Mise à jour de l'UI
            progressBar.style.width = `${strength}%`;
            
            if (strength < 30) {
                progressBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Sécurité: Faible';
            } else if (strength < 60) {
                progressBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Sécurité: Moyenne';
            } else if (strength < 80) {
                progressBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'Sécurité: Bonne';
            } else {
                progressBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Sécurité: Excellente';
            }
        });
    }
    
    // Vérification de la correspondance des mots de passe
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPasswordValue = this.value;
            const matchText = document.querySelector('.password-match');
            
            if (confirmPasswordValue === '') {
                matchText.textContent = '';
                matchText.className = 'form-text password-match';
            } else if (password === confirmPasswordValue) {
                matchText.textContent = 'Les mots de passe correspondent';
                matchText.className = 'form-text password-match text-success';
            } else {
                matchText.textContent = 'Les mots de passe ne correspondent pas';
                matchText.className = 'form-text password-match text-danger';
            }
        });
    }
});
</script>