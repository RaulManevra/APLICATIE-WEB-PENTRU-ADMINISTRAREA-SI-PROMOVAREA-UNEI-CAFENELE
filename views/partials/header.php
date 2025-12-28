<div class="header">
    <div class="navbar">
        <a href="index.php" class="logo">
            <img src="assets/img/Logo Modificat.png" alt="Mazi Coffee Logo">
        </a>

        <ul>
            <li><a href="?page=home" class="nav-link" data-page="home">Home</a></li>
            <li><a href="?page=about" class="nav-link" data-page="about">About</a></li>
            <li><a href="?page=menu" class="nav-link" data-page="menu">Menu</a></li>
            <li><a href="?page=tables" class="nav-link" data-page="tables">Tables</a></li>
            <?php if (in_array('admin', $currentUserRoles)): ?>
                <li><a href="?page=admin" class="nav-link" data-page="admin">Admin</a></li>
            <?php endif; ?>
        </ul>
        <?php if (!empty($currentUser)): ?>
    <div class="auth-buttons" id="profile-buttons">
        <button id="profile-btn" class="profile-icon" aria-haspopup="true" aria-expanded="false" title="Account">
            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
        </button>

        <div id="profile-popup" class="profile-popup" hidden>
            <button id="profile-close" class="profile-close" aria-label="Close">&times;</button>
            <div class="profile-popup-content">
                <button id="popup-logout" class="popup-action" data-page="logout">Logout</button>
            </div>
        </div>
    </div>
<?php endif; ?>

        <?php if (empty($currentUser)): ?>
            <div class="auth-buttons" id="auth-buttons">
                <a href="?page=login" class="nav-link auth-btn login" data-page="login">Login</a>
                <a href="?page=register" class="nav-link auth-btn signup" data-page="register">Sign up</a>
            </div>
        <?php endif; ?>


        <div class="toggle_btn">
            <i class="fa-solid fa-bars"></i>
        </div>
        <div class="dropdown_menu">
            <li><a href="?page=home" class="nav-link" data-page="home">Home</a></li>
            <li><a href="?page=about" class="nav-link" data-page="about">About</a></li>
            <li><a href="?page=menu" class="nav-link" data-page="menu">Menu</a></li>
            <li><a href="?page=contact" class="nav-link" data-page="contact">Contact</a></li>
            <li><a href="?page=tables" class="nav-link" data-page="tables">Tables</a></li>
            </li>
        </div>
    </div>
</div>
