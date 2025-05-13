<?php
session_start();

// Check admin login
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("location: login.php");
    exit;
}

require_once '../config/init.php';


// Define upload directory
$upload_dir = '../root_uploads/categories/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle delete operation
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Get image before deleting record
    $stmt = $db->prepare("SELECT image FROM category WHERE id_category = ?");
    $stmt->execute([$delete_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete the category
    $stmt = $db->prepare("DELETE FROM category WHERE id_category = ?");
    if ($stmt->execute([$delete_id])) {
        // Delete associated image if exists
        if (!empty($category['image'])) {
            $image_path = $upload_dir . $category['image'];
            if (file_exists($image_path)) unlink($image_path);
        }
        $_SESSION['success'] = "Catégorie supprimée avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de la catégorie";
    }
    header('Location: categories.php');
    exit();
}

// Handle form submission (Add/Edit)
if (isset($_POST['submit'])) {
    $category_name = trim($_POST['category_name']);
    $category_description = trim($_POST['category_description'] ?? '');
    $image_name = null;
    $edit_id = $_POST['edit_id'] ?? null;
    
    // Process image upload if provided
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['category_image']['tmp_name'], $upload_path)) {
                $image_name = $new_filename;
                
                // Delete old image if updating
                if ($edit_id) {
                    $stmt = $db->prepare("SELECT image FROM category WHERE id_category = ?");
                    $stmt->execute([$edit_id]);
                    $old_image = $stmt->fetch(PDO::FETCH_COLUMN);
                    
                    if (!empty($old_image)) {
                        $old_path = $upload_dir . $old_image;
                        if (file_exists($old_path)) unlink($old_path);
                    }
                }
            } else {
                $_SESSION['error'] = "Échec du téléchargement de l'image";
                header('Location: categories.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Type de fichier invalide. Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
            header('Location: categories.php');
            exit();
        }
    }
    
    // Update or insert category
    if ($edit_id) {
        // Update existing category
        if ($image_name) {
            $sql = "UPDATE category SET name = ?, description = ?, image = ? WHERE id_category = ?";
            $params = [$category_name, $category_description, $image_name, $edit_id];
        } else {
            $sql = "UPDATE category SET name = ?, description = ? WHERE id_category = ?";
            $params = [$category_name, $category_description, $edit_id];
        }
        $success_msg = "Catégorie mise à jour avec succès";
    } else {
        // Add new category
        $sql = "INSERT INTO category (name, description, image) VALUES (?, ?, ?)";
        $params = [$category_name, $category_description, $image_name];
        $success_msg = "Catégorie ajoutée avec succès";
    }
    
    $stmt = $db->prepare($sql);
    if ($stmt->execute($params)) {
        $_SESSION['success'] = $success_msg;
    } else {
        $_SESSION['error'] = "Erreur : " . implode(", ", $stmt->errorInfo());
    }
    header('Location: categories.php');
    exit();
}

// Load category for editing if requested
$edit_category = null;
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $stmt = $db->prepare("SELECT * FROM category WHERE id_category = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all categories
$stmt = $db->prepare("SELECT * FROM category ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include('includes/header.php'); ?>
<body>
   
    
    <div class="container-fluid">
        <div class="row">
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gérer les catégories</h1>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            echo $_SESSION['success']; 
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-5 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <?php echo $edit_category ? 'Modifier la catégorie' : 'Ajouter une nouvelle catégorie'; ?>
                            </div>
                            <div class="card-body">
                                <form method="post" action="" enctype="multipart/form-data">
                                    <?php if ($edit_category): ?>
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_category['id_category']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="category_name" class="form-label">Nom de la catégorie</label>
                                        <input type="text" class="form-control" id="category_name" name="category_name" 
                                            value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                                    </div>
                                    
                                    <!-- Add description field -->
                                    <div class="mb-3">
                                        <label for="category_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="category_description" name="category_description" 
                                            rows="3"><?php echo $edit_category && isset($edit_category['description']) ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category_image" class="form-label">Image de la catégorie</label>
                                        <input type="file" class="form-control" id="category_image" name="category_image">
                                        <small class="form-text text-muted">Formats autorisés : JPG, JPEG, PNG, GIF</small>
                                        <?php if ($edit_category && !empty($edit_category['image'])): ?>
                                            <div class="mt-2">
                                                <p>Image actuelle :</p>
                                                <img src="<?php echo $upload_dir . $edit_category['image']; ?>" alt="Image de la catégorie" class="img-thumbnail" style="max-width: 150px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="submit" name="submit" class="btn btn-primary"><?php echo $edit_category ? 'Mettre à jour la catégorie' : 'Ajouter la catégorie'; ?></button>
                                    
                                    <?php if ($edit_category): ?>
                                        <a href="categories.php" class="btn btn-secondary">Annuler</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                Liste des catégories
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Nom</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if (count($categories) > 0):
                                                $counter = 1;
                                                foreach ($categories as $row): 
                                            ?>
                                                <tr>
                                                    
                                                    <td><?php echo $row['id_category']; ?></td>
                                                    <td>
                                                        <?php if (!empty($row['image'])): ?>
                                                            <img src="<?php echo $upload_dir . $row['image']; ?>" alt="Catégorie" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                                        <?php else: ?>
                                                            <span class="text-muted">Aucune image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php 
                                                    $content = isset($row['description']) ? $row['description'] : '';
                                                    // Display the content with truncation if needed
                                                    echo htmlspecialchars(substr($content, 0, 50)) . (strlen($content) > 50 ? '...' : '');
                                                ?></td>
                                                    <td>
                                                        <a href="categories.php?edit_id=<?php echo $row['id_category']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                                                        <a href="categories.php?delete_id=<?php echo $row['id_category']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">Supprimer</a>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endforeach; 
                                            else: 
                                            ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Aucune catégorie trouvée</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
</body>
<?php include('includes/footer.php'); ?>
</html>
