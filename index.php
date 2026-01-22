<?php
ob_start();
require_once __DIR__ . '/core/security.php'; // starts session and sets secure cookie params
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/ReservationController.php';

// Simple router for controllers that need to run before output or via AJAX
if (isset($_GET['action']) || isset($_POST['action'])) {
    $page = $_GET['page'] ?? '';
    if ($page === 'cart_handler') {
        require_once __DIR__ . '/controllers/CartController.php';
        $controller = new CartController($conn);
        $controller->handleRequest();
        exit;
    }
    if ($page === 'order_handler') {
        require_once __DIR__ . '/controllers/OrderController.php';
        $controller = new OrderController($conn);
        $controller->handleRequest();
        exit;
    }
    if ($page === 'forgot_password_handler') {
        require_once __DIR__ . '/controllers/ForgotPasswordController.php';
        $controller = new ForgotPasswordController($conn);
        $controller->handleRequest();
        exit;
    }
    if ($page === 'reservation') {
        // ReservationController is already required at top, but ensure it handles request
        // $conn is available from config/db.php
        $controller = new ReservationController($conn);
        $controller->handleRequest();
        exit;
    }
}

$userData = SessionManager::getCurrentUserData();

// Remember Me Check
if (!SessionManager::isLoggedIn() && isset($_COOKIE['remember_me'])) {
    SessionManager::checkRememberMe($conn);
    $userData = SessionManager::getCurrentUserData(); // Refresh
}

$currentUser = $userData['username'] ?? null;
$currentUserRoles = $userData['roles'] ?? [];
$upcomingReservation = null;

if ($currentUser && isset($conn)) {
    $upcomingReservation = ReservationController::getUpcomingForUser($conn, $userData['id']);
}

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
    <!-- Page-specific CSS moved to respective views -->
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/popup.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
            currentUserRoles: <?php echo json_encode($currentUserRoles, JSON_UNESCAPED_UNICODE); ?>,
            currentUserData: <?php echo json_encode($userData, JSON_UNESCAPED_UNICODE); ?>,
            routes: <?php echo json_encode([
                'home' => 'views/pages/home.php',
                'about' => 'views/pages/about.php',
                'menu' => 'views/pages/menu.php',
                'contact' => 'views/pages/contact.php',
                'login' => 'views/auth/login.php',
                'register' => 'views/auth/register.php',
                'admin' => 'views/pages/admin.php',
                'tables' => 'views/pages/tables.php',
                'profile_picture_upload' => 'views/forms/profile_picture_upload.php',
                'cart' => 'views/pages/cart.php',
                'cart_handler' => 'controllers/CartController.php',
                'forgot_password' => 'views/auth/forgot_password.php',
                'reset_password' => 'views/auth/reset_password.php',
                'forgot_password_handler' => 'index.php?page=forgot_password_handler'
            ], JSON_UNESCAPED_UNICODE); ?>
        };
        // Backwards compatibility if needed, or just use APP_CONFIG.currentUser
        window.CURRENT_USER = window.APP_CONFIG.currentUser; 
        window.CURRENT_USER_ROLES = window.APP_CONFIG.currentUserRoles;
    </script>

    <script type="module" src="assets/js/main.js"></script>


</body>
</html>
