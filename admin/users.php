<?php
// Initialize the session
session_start();
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
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if (empty($role)) $errors[] = "Role is required";
    
    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
    $checkStmt->bindParam(1, $email, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->rowCount() > 0) {
        $errors[] = "Email already exists";
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
            $_SESSION["success_msg"] = "User added successfully";
        } else {
            $_SESSION["error_msg"] = "Something went wrong. Please try again later.";
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
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($role)) $errors[] = "Role is required";
    
    // Check if email already exists for other users
    $checkStmt = $conn->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
    $checkStmt->bindParam(1, $email, PDO::PARAM_STR);
    $checkStmt->bindParam(2, $id, PDO::PARAM_INT);
    $checkStmt->execute();
    if ($checkStmt->rowCount() > 0) {
        $errors[] = "Email already exists";
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
            $_SESSION["success_msg"] = "User updated successfully";
        } else {
            $_SESSION["error_msg"] = "Something went wrong. Please try again later.";
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
        $_SESSION["success_msg"] = "User deleted successfully";
    } else {
        $_SESSION["error_msg"] = "Something went wrong. Please try again later.";
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
    $_SESSION["error_msg"] = "Database error: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<!-- Head section remains the same -->
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>
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
                <h2>User Management</h2>
                
                <?php if (isset($_SESSION["success_msg"])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION["success_msg"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION["success_msg"]); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION["error_msg"])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION["error_msg"] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION["error_msg"]); ?>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between mb-3">
                    <p>Manage system users</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </div>
                
                <!-- Table structure updated -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Users List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Address</th>
                                        <th>Phone</th>
                                        <th>Created At</th>
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
                                                    <?= ucfirst(htmlspecialchars($row["role"])) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row["address"] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row["phone"] ?? '') ?></td>
                                            <td><?= $row["created_at"] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="editUser(<?= $row['id_user'] ?>, '<?= addslashes($row['name']) ?>', '<?= addslashes($row['email']) ?>', '<?= $row['role'] ?>', '<?= addslashes($row['address'] ?? '') ?>', '<?= addslashes($row['phone'] ?? '') ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="users.php?action=delete&id=<?= $row["id_user"] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No users found</td>
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
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="users.php" method="post">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
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
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="users.php" method="post">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="id_user" id="edit_id_user">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Password (Leave blank to keep current)</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_role" class="form-label">Role</label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_address" class="form-label">Address</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
   
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable();
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
