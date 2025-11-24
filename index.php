<?php
require_once __DIR__ . '/include/security.php'; // starts session and sets secure cookie params

$currentUser = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mazi Coffee</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'include/header.php'; ?>

    <div id="hero"></div>

    <main id="app"></main>

    <!-- Expose current user to JS safely -->
    <script>
        // CURRENT_USER is null when not logged in, or a string username when logged in
        window.CURRENT_USER = <?php echo json_encode($currentUser, JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <script src="script.js"></script>
</body>
</html>
