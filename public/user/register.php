<?php
require_once __DIR__ . '/../../config/init.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // No need for mysqli_real_escape_string with PDO - we'll use prepared statements
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = 'customer'; // Default role for new registrations
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide";
    } elseif ($password != $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        try {
            // Check if email already exists using PDO
            $check_stmt = $db->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
            $check_stmt->execute([$email]);
            $email_exists = $check_stmt->fetchColumn() > 0;
            
            if ($email_exists) {
                $error = "Cet email existe déjà";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user using PDO prepared statement
                $stmt = $db->prepare("INSERT INTO user (name, email, password, role, address, phone, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
                
                $result = $stmt->execute([$name, $email, $hashed_password, $role, $address, $phone]);
                
                if ($result) {
                    $success = "Inscription réussie! Vous pouvez maintenant vous connecter";
                    // Redirect to login page after 2 seconds
                    header("refresh:2; url=login.php");
                } else {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
        } catch (PDOException $e) {
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
$page_title = "Inscription - Artisanat Tunisien";
$description = "Inscrivez-vous pour découvrir l'artisanat tunisien de qualité, des produits faits main par nos artisans locaux.";
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <?php include ROOT_PATH . '/public/templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/register.css" />
  </head>
   
</head>
<body>
<?php include ROOT_PATH . '/public/templates/navbar.php'; ?>
<div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4">Créer un Compte</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="name">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Téléphone <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Adresse <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
            </form>
            
            <div class="text-center mt-3">
                Vous avez déjà un compte? <a href="login.php">Connectez-vous ici</a>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/public/templates/footer.php'; ?>