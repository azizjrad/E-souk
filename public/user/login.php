<?php
require_once __DIR__ . '/../../config/init.php';

$title = "E-Souk Tounsi - Artisanat Tunisien";
$description = "Découvrez l'artisanat tunisien de qualité, des produits faits main par nos artisans locaux.";

// Process login form submission
if (isset($_POST['login_submit'])) {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember']);
  
  // Validate inputs
  if (empty($email) || empty($password)) {
    $error = "Veuillez remplir tous les champs.";
  } else {
    try {
      // Updated table name from "users" to "user"
      $sql = "SELECT id_user, name, email, password, role, address, phone FROM user WHERE email = ?";
      $stmt = $db->prepare($sql);
      $stmt->execute([$email]);
      $user = $stmt->fetch();
      
      // Verify user exists and password is correct
      if ($user && password_verify($password, $user['password'])) {
        // Login successful - store all relevant user data
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_address'] = $user['address'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user'] = true;
        // Set remember me cookie if requested
        if ($remember) {
          $token = bin2hex(random_bytes(16));
          $expires = time() + 30 * 24 * 60 * 60; // 30 days
          setcookie('remember_token', $token, $expires, '/');
          
          // You might want to store this token in the database for verification later
        }
        
        // Redirect to dashboard or home page
        header("Location: " . ROOT_URL . "public/user/profile.php");
        exit();
      } else {
        $error = "Email ou mot de passe incorrect.";
      }
    } catch (PDOException $e) {
      // Log the error and show a user-friendly message
      error_log("Database error: " . $e->getMessage());
      $error = "Une erreur s'est produite lors de la connexion. Veuillez réessayer plus tard.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <?php include ROOT_PATH . '/public/templates/header.php'; ?>
    <link rel="stylesheet" href="../assets/css/login.css">
  </head>
  <body>
    
    <!-- Navbar -->
    <?php include ROOT_PATH . '/public/templates/navbar.php'; ?>

    <!-- Login Section -->
    <section class="container my-5 py-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h2 class="mb-4 text-center text-primary">Connexion</h2>
              
              <?php
              // Display error message if any
              if (isset($error)) {
                echo '<div class="alert alert-danger">' . $error . '</div>';
              }
              ?>

              <form action="<?php echo htmlspecialchars(ROOT_URL . 'public/user/login.php'); ?>" method="POST">
                <div class="mb-3">
                  <label for="email" class="form-label">Adresse Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="nom@exemple.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                  />
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Mot de passe</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="********"
                    required
                  />
                </div>
                <div class="mb-3 form-check">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    id="remember"
                    name="remember"
                  />
                  <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                <button type="submit" class="btn btn-primary w-100" name="login_submit">
                  Se connecter
                </button>
              </form>

              <div class="text-center mt-3">
                <a href="<?php echo ROOT_URL; ?>public/user/reset_password.php">Mot de passe oublié ?</a>
              </div>
              <div class="text-center mt-2">
                <span>Vous n'avez pas de compte ?
                <a href="<?php echo ROOT_URL; ?>public/user/register.php">Créer un compte</a> </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <?php include '../templates/footer.php'; ?>
  </body>
</html>
