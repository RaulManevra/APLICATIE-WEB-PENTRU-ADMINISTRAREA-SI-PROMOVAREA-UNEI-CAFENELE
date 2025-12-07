<div class="header">
<div class="navbar">
    <h1>Mazi Coffee</h1>

    <ul>
        <li><a href="?page=home" class="nav-link" data-page="home">Home</a></li>
        <li><a href="?page=about" class="nav-link" data-page="about">About</a></li>
        <li><a href="?page=menu" class="nav-link" data-page="menu">Menu</a></li>
        <li><a href="?page=contact" class="nav-link" data-page="contact">Contact</a></li>
        <li><a href="?page=tables" class="nav-link" data-page="tables">Tables</a></li>
        <?php if (in_array('admin', $currentUserRoles)): ?>
            <li><a href="?page=admin" class="nav-link" data-page="admin">Admin</a></li>
        <?php endif; ?>
        <li class="profile-li">
            <button id="profile-btn" class="profile-icon" aria-haspopup="true" aria-expanded="false" title="Account">
                <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
            </button>
                
            <div id="profile-popup" class="profile-popup" hidden>
                <button id="profile-close" class="profile-close" aria-label="Close">&times;</button>
                <div class="profile-popup-content">
                    <?php if (empty($currentUser)): ?>
                    <button id="popup-login" class="popup-action">Log In</button>
                    <button id="popup-register" class="popup-action">Register</button>
                    <?php else: ?>
                    <button id="popup-logout" class="popup-action" data-page="logout">Logout</button>
                    <?php endif; ?>
                </div>
            </div>
        </li>
    </ul>

    <div class="social-icons">
        <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
    </div>
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
        <div class="social-icons">
            <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
        </div>
    </div>
</div>
</div>