<?php
session_start();
require_once '../config/init.php';

$error = '';

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate input
    if(empty($email) || empty($password)) {
        $error = "Veuillez saisir l'email et le mot de passe";
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM user WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if($admin && password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['user_id'] = $admin['id_user'];
                $_SESSION['user_name'] = $admin['name'];
                $_SESSION['user_role'] = $admin['role'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Email ou mot de passe invalide";
            }
        } catch(PDOException $e) {
            $error = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tableau de Bord Admin | E-Souk</title>
    <meta name="description" content="Panneau d'administration pour la plateforme E-Souk d'artisanat tunisien.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #3c48b5;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .logo-area {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-area img {
            max-height: 60px;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            font-weight: 500;
            padding: 0.75rem;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #375ad3;
            transform: translateY(-2px);
        }
        .form-control {
            padding: 0.75rem;
            border-radius: 7px;
        }
        .password-field {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 12px;
            cursor: pointer;
            color: #6c757d;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="logo-area">
                            <h2>E-Souk</h2>
                            <p class="text-muted">Panneau d'Administration</p>
                        </div>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                             placeholder="admin@e-souk.com" required autocomplete="email">
                                <div class="invalid-feedback">Veuillez entrer une adresse email valide</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="password-field">
                                    <input type="password" class="form-control" id="password" name="password" 
                                                 required autocomplete="current-password">
                                    <i class="toggle-password fas fa-eye-slash" onclick="togglePassword()"></i>
                                    <div class="invalid-feedback">Le mot de passe est requis</div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Connexion
                                </button>
                            </div>
                        </form>
                        
                        <div class="back-link">
                            <a href="../public/pages/index.php" 
                               target="_blank"
                            class="text-decoration-none text-muted">
                                <i class="fas fa-arrow-left me-2"></i>Retour à l'Accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
        
        // Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
