<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once __DIR__ . '/../../config/init.php';

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current user data
try {
    $stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données utilisateur : " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Basic validation
    if (empty($name) || empty($email)) {
        $error_message = "Le nom et l'email sont des champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Veuillez entrer une adresse email valide.";
    } else {
        try {
            // Check if email already exists for another user
            $stmt = $db->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->rowCount() > 0) {
                $error_message = "Cet email est déjà utilisé par un autre compte.";
            } else {
                // Update user data - removed image field
                $stmt = $db->prepare("UPDATE user SET name = ?, email = ?, phone = ?, address = ? WHERE id_user = ?");
                $stmt->execute([$name, $email, $phone, $address, $user_id]);
                
                // Handle password change if provided
                if (!empty($_POST['new_password'])) {
                    if (strlen($_POST['new_password']) < 6) {
                        $error_message = "Le mot de passe doit contenir au moins 6 caractères.";
                    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                        $error_message = "Les nouveaux mots de passe ne correspondent pas.";
                    } else {
                        $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE user SET password = ? WHERE id_user = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                    }
                }
                
                if (empty($error_message)) {
                    $success_message = "Profil mis à jour avec succès !";
                    // Refresh user data
                    $stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch(PDOException $e) {
            $error_message = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
        }
    }
}
$page_title = "Modifier le Profil - E-Souk Tounsi";
$description = "Modifier les informations de votre profil sur E-Souk Tounsi. Mettez à jour vos informations personnelles, adresse, et mot de passe.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include ROOT_PATH . '/public/templates/header.php'; ?>
    <style>
        :root {
            --primary-color: #2b3684;
            --primary-light: #3a45a0;
            --primary-dark: #1e2760;
            --secondary-color: #f5f7fa;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --white: #ffffff;
        }

        /* User dashboard layout */
        .user-dashboard {
            display: flex;
            min-height: calc(100vh - 150px);
            background-color: #f5f7fa;
        }
        
        .sidebar-container {
            width: 250px;
            flex-shrink: 0;
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .content-container {
            flex-grow: 1;
            padding: 25px;
        }

        /* Card styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .card-header {
            background-color: var(--white);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 18px 25px;
        }
        
        .card-header h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 25px;
        }

        /* Form styling */
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(43, 54, 132, 0.25);
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        /* Button styles */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(43, 54, 132, 0.2);
        }
        
        /* Section dividers */
        hr {
            opacity: 0.1;
        }
        
        h5 {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* Alert styling */
        .alert {
            border: none;
            border-radius: 10px;
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        /* Responsive adjustments */
        @media (max-width: 767px) {
            .user-dashboard {
                flex-direction: column;
            }
            
            .sidebar-container {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            
            .content-container {
                padding: 15px;
            }
            
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Include header -->
    <?php include ROOT_PATH . '/public/templates/navbar.php'; ?>
    
    <div class="container-fluid p-0">
        <div class="user-dashboard">
            <!-- Sidebar Container -->
            <div class="sidebar-container">
                <?php include ROOT_PATH . '/public/user/includes/sidebar.php'; ?>
            </div>
            
            <!-- Content Container -->
            <div class="content-container">
                <div class="px-3 py-3">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Modifier le Profil</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nom Complet</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Adresse Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Numéro de Téléphone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Adresse</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                <h5>Changer le Mot de Passe</h5>
                                <p class="text-muted small">Laissez vide si vous ne souhaitez pas changer votre mot de passe</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label">Nouveau Mot de Passe</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                        <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirmer le Nouveau Mot de Passe</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">Enregistrer les Modifications</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include ROOT_PATH . '/public/templates/footer.php'; ?>
</body>
</html>