<div class="header">
    <div class="navbar">
        <a href="?page=home" class="logo nav-link" data-page="home">
            <img src="assets/img/Logo Modificat.png" alt="Mazi Coffee Logo">
        </a>

        <ul>
            <li><a href="?page=home" class="nav-link" data-page="home">Home</a></li>
            <li><a href="?page=about" class="nav-link" data-page="about">About</a></li>
            <li><a href="?page=menu" class="nav-link" data-page="menu">Menu</a></li>
            <li><a href="?page=tables" class="nav-link" data-page="tables">Tables</a></li>
            <li id="admin-link-li" style="<?php echo in_array('admin', $currentUserRoles) ? '' : 'display: none;'; ?>">
                <a href="?page=admin" class="nav-link" data-page="admin">Admin</a>
            </li>
        </ul>
        <div class="auth-buttons" id="auth-buttons" style="<?php echo !empty($currentUser) ? 'display: none;' : ''; ?>">
            <a href="?page=login" class="nav-link auth-btn login" data-page="login">Login</a>
            <a href="?page=register" class="nav-link auth-btn signup" data-page="register">Sign up</a>
        </div>

        <div class="auth-buttons" id="profile-buttons" style="<?php echo empty($currentUser) ? 'display: none;' : ''; ?>">
            <button id="profile-btn" class="profile-icon" aria-haspopup="true" aria-expanded="false" title="Account">
                Cont
                <i class="fa-regular fa-circle-user"></i>
            </button>

            <div id="profile-popup" class="profile-popup" hidden>
                <button id="profile-close" class="profile-close" aria-label="Close">&times;</button>
                <div class="profile-popup-content">
                    <?php if (isset($upcomingReservation) && $upcomingReservation): ?>
                        <div class="reservation-reminder-modern">
                            <h4>
                                <i class="fa-solid fa-calendar-check"></i> Upcoming Reservation
                            </h4>
                            <div class="reservation-details">
                                <div class="reservation-row">
                                    <span>Name:</span>
                                    <strong><?= htmlspecialchars($upcomingReservation['name'] ?? $currentUser['name'] ?? 'Guest') ?></strong>
                                </div>
                                <div class="reservation-row">
                                    <span>Table:</span>
                                    <strong><?= htmlspecialchars($upcomingReservation['table_id']) ?></strong>
                                </div>
                                <div class="reservation-row">
                                    <span>Time:</span>
                                    <strong><?= date('d M, H:i', strtotime($upcomingReservation['reservation_time'])) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <button id="popup-logout" class="popup-action" data-page="logout">Logout</button>
                </div>
            </div>
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
        </div>
    </div>
</div>