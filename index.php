<?php
require_once __DIR__ . '/core/security.php'; // starts session and sets secure cookie params


$currentUser = SessionManager::getCurrentUser();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mazi Coffee</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/icons/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/icons/css/brands.min.css">
    <link rel="stylesheet" href="assets/icons/css2/fontawesome.min.css">
    <link rel="stylesheet" href="assets/icons/css2/solid.min.css">

</head>

<body>

    <?php include 'views/partials/header.php'; ?>

    <div id="hero"></div>

    <main id="app"></main>

    <!-- Expose current user to JS safely -->
    <!-- Expose app config to JS safely -->
    <script>
        window.APP_CONFIG = {
            currentUser: <?php echo json_encode($currentUser, JSON_UNESCAPED_UNICODE); ?>,
            routes: <?php echo json_encode([
                'home' => 'views/pages/home.php',
                'about' => 'views/pages/about.php',
                'menu' => 'views/pages/menu.php',
                'contact' => 'views/pages/contact.php',
                'login' => 'views/auth/login.php',
                'register' => 'views/auth/register.php'
            ], JSON_UNESCAPED_UNICODE); ?>
        };
        // Backwards compatibility if needed, or just use APP_CONFIG.currentUser
        window.CURRENT_USER = window.APP_CONFIG.currentUser; 
    </script>

    <script src="assets/js/script.js"></script>
</body>
</html>
