<?php
// Initialize the session
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once "../config/init.php";
// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("location: login.php");
    exit;
}


$conn = Database::getInstance();

// Process add user operation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "add_user") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "Le courriel est requis";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format de courriel invalide";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    elseif (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    if (empty($role)) $errors[] = "Le rôle est requis";
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
    $checkStmt->bindParam(1, $email, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->rowCount() > 0) {
        $errors[] = "Le courriel existe déjà";
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO user (name, email, password, role, address, phone, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $name, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(4, $role, PDO::PARAM_STR);
        $stmt->bindParam(5, $address, PDO::PARAM_STR);
        $stmt->bindParam(6, $phone, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $_SESSION["success_msg"] = "Utilisateur ajouté avec succès";
        } else {
            $_SESSION["error_msg"] = "Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
        }
        
        header("location: users.php");
        exit;
    } else {
        $_SESSION["error_msg"] = implode("<br>", $errors);
    }
}

// Process edit user operation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit_user") {
    $id = trim($_POST["id_user"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "Le courriel est requis";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format de courriel invalide";
    if (empty($role)) $errors[] = "Le rôle est requis";
    
    // Check if email already exists for other users
    $checkStmt = $conn->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
    $checkStmt->bindParam(1, $email, PDO::PARAM_STR);
    $checkStmt->bindParam(2, $id, PDO::PARAM_INT);
    $checkStmt->execute();
    if ($checkStmt->rowCount() > 0) {
        $errors[] = "Le courriel existe déjà";
    }
    
    if (empty($errors)) {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE user SET name = ?, email = ?, password = ?, role = ?, address = ?, phone = ? WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $name, PDO::PARAM_STR);
            $stmt->bindParam(2, $email, PDO::PARAM_STR);
            $stmt->bindParam(3, $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(4, $role, PDO::PARAM_STR);
            $stmt->bindParam(5, $address, PDO::PARAM_STR);
            $stmt->bindParam(6, $phone, PDO::PARAM_STR);
            $stmt->bindParam(7, $id, PDO::PARAM_INT);
        } else {
            $sql = "UPDATE user SET name = ?, email = ?, role = ?, address = ?, phone = ? WHERE id_user = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $name, PDO::PARAM_STR);
            $stmt->bindParam(2, $email, PDO::PARAM_STR);
            $stmt->bindParam(3, $role, PDO::PARAM_STR);
            $stmt->bindParam(4, $address, PDO::PARAM_STR);
            $stmt->bindParam(5, $phone, PDO::PARAM_STR);
            $stmt->bindParam(6, $id, PDO::PARAM_INT);
        }
        
        if ($stmt->execute()) {
            $_SESSION["success_msg"] = "Utilisateur mis à jour avec succès";
        } else {
            $_SESSION["error_msg"] = "Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
        }
        
        header("location: users.php");
        exit;
    } else {
        $_SESSION["error_msg"] = implode("<br>", $errors);
    }
}

// Process delete operation
if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = trim($_GET["id"]);
    $sql = "DELETE FROM user WHERE id_user = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION["success_msg"] = "Utilisateur supprimé avec succès";
    } else {
        $_SESSION["error_msg"] = "Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
    }
    
    header("location: users.php");
    exit;
}

try {
    // Fetch all users with PDO
    $sql = "SELECT id_user, name, email, role, address, phone, created_at FROM user ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION["error_msg"] = "Erreur de base de données : " . $e->getMessage();
    $users = [];
}

$role_translations = [
    "admin" => "Administrateur",
    "customer" => "Utilisateur" 
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Panneau Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/admin.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Missing sidebar here -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
                <h2>Gestion des Utilisateurs</h2>
                
                <?php if (isset($_SESSION["success_msg"])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION["success_msg"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                    <?php unset($_SESSION["success_msg"]); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION["error_msg"])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION["error_msg"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                    <?php unset($_SESSION["error_msg"]); ?>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between mb-3">
                    <p>Gérer les utilisateurs du système</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Ajouter un nouvel utilisateur
                    </button>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Liste des utilisateurs
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Identifiant</th>
                                        <th>Nom</th>
                                        <th>Courriel</th>
                                        <th>Rôle</th>
                                        <th>Adresse</th>
                                        <th>Téléphone</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach($users as $row): ?>
                                        <tr>
                                            <td><?= $row["id_user"] ?></td>
                                            <td><?= htmlspecialchars($row["name"]) ?></td>
                                            <td><?= htmlspecialchars($row["email"]) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $row["role"] == "admin" ? "danger" : "info" ?>">
                                                    <?= htmlspecialchars($role_translations[$row["role"]] ?? ucfirst($row["role"])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row["address"] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row["phone"] ?? '') ?></td>
                                            <td><?= !empty($row["created_at"]) ? date("d/m/Y H:i", strtotime($row["created_at"])) : '' ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="editUser(<?= $row['id_user'] ?>, '<?= addslashes(htmlspecialchars($row['name'])) // Ensure htmlspecialchars for security ?>', '<?= addslashes(htmlspecialchars($row['email'])) ?>', '<?= $row['role'] ?>', '<?= addslashes(htmlspecialchars($row['address'] ?? '')) ?>', '<?= addslashes(htmlspecialchars($row['phone'] ?? '')) ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="users.php?action=delete&id=<?= $row["id_user"] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Aucun utilisateur trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Ajouter un nouvel utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form action="users.php" method="post">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Courriel</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="customer">Utilisateur</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="address" class="form-label">Adresse</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form action="users.php" method="post">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="id_user" id="edit_id_user">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Courriel</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Mot de passe (Laissez vide pour conserver l'actuel)</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_role" class="form-label">Rôle</label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="customer">Utilisateur</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_address" class="form-label">Adresse</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour l'utilisateur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json"></script>
   
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json"
                }
            });
        });
        
        function editUser(id, name, email, role, address, phone) {
            // Set values in the edit modal
            document.getElementById('edit_id_user').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_address').value = address || '';
            document.getElementById('edit_phone').value = phone || '';
            
            // Clear password field
            document.getElementById('edit_password').value = '';
            
            // Show the modal
            var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        }
    </script>

<?php include('includes/footer.php'); ?>
</body>
</html>
