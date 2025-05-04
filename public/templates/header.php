<?php

// Include the configuration file
require_once __DIR__ . '/../../config/init.php';

?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="../assets/css/components/navbar.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    
    <link rel="stylesheet" href="../assets/css/components/Topbtn.css">
    <link rel="stylesheet" href="../assets/css/components/newsletter.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
</head>