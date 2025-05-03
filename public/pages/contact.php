<?php 
session_start();
require_once __DIR__ . '/../../config/init.php';

$page_title = "Contactez-nous";
$page_description = "Contactez-nous pour toute question ou information supplémentaire.";

// Initialize variables for form fields and messages
$name = $email = $subject = $message = "";
$success_message = $error_message = "";
$form_submitted = false;

// Function to send email using PHPMailer if available, otherwise store in a file
function send_contact_email($to, $subject, $email_content, $from_email, $from_name) {
    $is_local = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');
    
    // Check if PHPMailer is available (you need to include PHPMailer files)
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com'; // Replace with your email
            $mail->Password = 'your_app_password'; // Replace with your password or app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $email_content;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $mail->ErrorInfo);
            return false;
        }
    } 
    // If we're on localhost and PHPMailer is not available, save to file as fallback
    else if ($is_local) {
        // Create a directory to store emails if it doesn't exist
        $email_dir = __DIR__ . '/../../logs/emails/';
        if (!file_exists($email_dir)) {
            mkdir($email_dir, 0777, true);
        }
        
        // Format email content for file storage
        $file_content = "To: $to\n";
        $file_content .= "From: $from_name <$from_email>\n";
        $file_content .= "Subject: $subject\n";
        $file_content .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
        $file_content .= "HTML Content:\n$email_content\n";
        
        // Generate a unique filename
        $filename = $email_dir . 'email_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.txt';
        
        // Save to file
        if (file_put_contents($filename, $file_content)) {
            return array(true, "Email saved to file: " . basename($filename));
        } else {
            return array(false, "Failed to save email to file");
        }
    } 
    // Try PHP mail() function as a last resort
    else {
        // Regular headers
        $headers = "From: $from_email" . "\r\n";
        $headers .= "Reply-To: $from_email" . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        
        // Try to send email
        if (mail($to, $subject, $email_content, $headers)) {
            return true;
        } else {
            $error = error_get_last();
            error_log("PHP mail() error: " . ($error ? $error['message'] : "Unknown error"));
            return false;
        }
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_submitted = true;
    
    // Get form data and sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Simple validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse email n'est pas valide.";
    } else {
        // Recipient email
        $to = "aziz.jrad@esen.tn";
        
        // Email content
        $email_content = "<html><body>";
        $email_content .= "<h2>Nouveau message de contact - E-souk</h2>";
        $email_content .= "<p><strong>Nom:</strong> $name</p>";
        $email_content .= "<p><strong>Email:</strong> $email</p>";
        $email_content .= "<p><strong>Sujet:</strong> $subject</p>";
        $email_content .= "<p><strong>Message:</strong><br>$message</p>";
        $email_content .= "</body></html>";
        
        // Send email using our custom function
        $result = send_contact_email($to, "Contact E-souk: $subject", $email_content, $email, $name);
        
        // Check if we got an array back (local development mode)
        if (is_array($result)) {
            list($success, $details) = $result;
            if ($success) {
                $success_message = "Vous êtes en mode développement local. Le message a été enregistré dans un fichier.<br><small>{$details}</small>";
                // Clear form fields after successful submission
                $name = $email = $subject = $message = "";
            } else {
                $error_message = "Erreur en mode développement: {$details}";
            }
        } 
        // Regular success/error handling
        else if ($result === true) {
            $success_message = "Votre message a été envoyé avec succès à aziz.jrad@esen.tn. Nous vous répondrons dans les plus brefs délais.";
            // Clear form fields after successful submission
            $name = $email = $subject = $message = "";
            // Store in session that email was sent successfully
            $_SESSION['email_sent'] = true;
            $_SESSION['email_time'] = date('Y-m-d H:i:s');
        } else {
            $error_message = "Une erreur s'est produite lors de l'envoi du message. ";
            $error_message .= "Si vous êtes en environnement de développement local, veuillez configurer un serveur SMTP ou utiliser une alternative comme Mailtrap.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
   <?php include '../templates/header.php'; ?>
   <link rel="stylesheet" href="../assets/css/contact.css">
   <style>
     .alert-success-custom {
       background-color: #d4edda;
       color: #155724;
       border-color: #c3e6cb;
       border-left: 5px solid #2b3684;
     }
     .alert-notice-custom {
       background-color: #e2e3e5;
       color: #383d41;
       border-color: #d6d8db;
       border-left: 5px solid #2b3684;
     }
     .email-confirmation {
       display: flex;
       align-items: center;
     }
     .email-confirmation i {
       font-size: 24px;
       margin-right: 15px;
       color: #2b3684;
     }
   </style>
</head>
<body>
    <?php include '../templates/navbar.php'; ?>

     <section class="contact-section py-5 bg-light">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="fw-bold text-custom-blue">Contactez-nous</h2>
          <p class="text-muted">
            Des questions ? Parlons-en. Contactez-nous dès maintenant.
          </p>
          <hr class="mx-auto" style="width: 60px; border: 2px solid #fcd34d" />
        </div>

        <?php if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1'): ?>
            <div class="alert alert-notice-custom p-3 mb-4" role="alert">
                <div class="email-confirmation">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <h5 class="mb-1">Mode Développement Local</h5>
                        <p class="mb-0">Vous êtes en environnement de développement local. Les emails ne seront pas envoyés réellement mais enregistrés dans le dossier /logs/emails/ si disponible.</p>
                        <p class="mb-0 small text-muted mt-1">Pour configurer l'envoi d'emails, veuillez installer PHPMailer ou configurer un serveur SMTP.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success-custom p-3 mb-4" role="alert">
                <div class="email-confirmation">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h5 class="mb-1">Message envoyé avec succès!</h5>
                        <p class="mb-0"><?php echo $success_message; ?></p>
                        <p class="mb-0 small text-muted mt-1">Envoyé le <?php echo date('d/m/Y à H:i'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger p-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-3" style="font-size: 24px;"></i>
                    <div>
                        <h5 class="mb-1">Erreur lors de l'envoi</h5>
                        <p class="mb-0"><?php echo $error_message; ?></p>
                        <p class="mb-0 small text-muted mt-1">
                            Solutions possibles pour XAMPP:
                            <br>1. Installez PHPMailer: <code>composer require phpmailer/phpmailer</code>
                            <br>2. Utilisez un service comme Mailtrap.io pour les tests
                            <br>3. Configurez un serveur SMTP dans le fichier php.ini
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
          <!-- Left: Infos pratiques -->
          <div class="col-md-6">
            <div
              class="p-4 rounded bg-white border border-1 shadow-sm"
              style="border-color: #2b3684 !important;"
            >
              <h5 class="text-custom-blue fw-bold mb-4">Nos infos pratiques</h5>
              <ul class="list-unstyled text-muted">
                <li class="mb-3">
                  <i class="fas fa-map-marker-alt me-2 text-custom-blue"></i>
                  <strong>Adresse</strong><br />Tunis, Tunisia
                </li>
                <li class="mb-3">
                  <i class="fas fa-phone me-2 text-custom-blue"></i>
                  <strong>Téléphone</strong><br />XXXXX
                </li>
                <li class="mb-3">
                  <i class="fas fa-envelope me-2 text-custom-blue"></i>
                  <strong>Email</strong><br />E-souk@gmail.com
                </li>
                <li class="mb-3">
                  <i class="fas fa-clock me-2 text-custom-blue"></i>
                  <strong>Horaires</strong><br />Lundi-Dimanche: 24h/7jrs
                </li>
                <li>
                  <i class="fab fa-instagram me-2 text-custom-blue"></i>
                  <strong>Réseaux sociaux</strong><br />@E-souk.tn
                </li>
              </ul>
            </div>
          </div>

          <!-- Right: Formulaire -->
          <div class="col-md-6">
            <div
              class="p-4 rounded bg-white border border-1 shadow-sm"
              style="border-color: #2b3684 !important;"
            >
              <h5 class="text-custom-blue fw-bold mb-4">Besoin de nous parler ?</h5>
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="contactForm">
                <div class="row mb-3">
                  <div class="col">
                    <input
                      type="text"
                      class="form-control"
                      name="name"
                      placeholder="Votre nom"
                      value="<?php echo $name; ?>"
                      required
                    />
                  </div>
                  <div class="col">
                    <input
                      type="email"
                      class="form-control"
                      name="email"
                      placeholder="Votre email"
                      value="<?php echo $email; ?>"
                      required
                    />
                  </div>
                </div>
                <div class="mb-3">
                  <input
                    type="text"
                    class="form-control"
                    name="subject"
                    placeholder="Sujet"
                    value="<?php echo $subject; ?>"
                    required
                  />
                </div>
                <div class="mb-3">
                  <textarea
                    class="form-control"
                    name="message"
                    rows="5"
                    placeholder="Votre message"
                    required
                  ><?php echo $message; ?></textarea>
                </div>
                <button type="submit" class="btn btn-custom-blue px-4" id="submitBtn">
                  Envoyer le message
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php include '../templates/Topbtn.php'; ?>
    <?php include '../templates/newsletter.php'; ?>
    <?php include '../templates/footer.php'; ?>

    <!-- Add validation JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('contactForm');
      const submitBtn = document.getElementById('submitBtn');
      
      form.addEventListener('submit', function(event) {
        // Prevent default form submission
        event.preventDefault();
        
        // Disable the button and show sending state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Envoi en cours...';
        
        // Validate form
        let isValid = true;
        const name = form.elements['name'].value.trim();
        const email = form.elements['email'].value.trim();
        const subject = form.elements['subject'].value.trim();
        const message = form.elements['message'].value.trim();
        
        if (!name || !email || !subject || !message) {
          isValid = false;
          alert('Tous les champs sont obligatoires');
        } else if (!isValidEmail(email)) {
          isValid = false;
          alert('Veuillez entrer une adresse email valide');
        }
        
        if (isValid) {
          // If validation passes, submit the form
          form.submit();
        } else {
          // Re-enable the button if validation fails
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Envoyer le message';
        }
      });
      
      // Email validation function
      function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
      }
    });
    </script>

</body>
</html>