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


        <div class="auth-buttons" id="profile-buttons">
            <a href="?page=cart" class="profile-icon" style="text-decoration: none; margin-right: 5px;">
                Cart
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            <button id="profile-btn" class="profile-icon" aria-haspopup="true" aria-expanded="false" title="Account">
                <?php if ($currentUser): ?>
                    <?= htmlspecialchars($currentUser) ?>
                    <?php 
                        $navProfilePic = $userData['profile_picture'] ?? 'assets/public/default.png';
                        // Ensure we have a valid path, otherwise default
                        if (empty($navProfilePic)) $navProfilePic = 'assets/public/default.png';
                    ?>
                    <img src="<?= htmlspecialchars($navProfilePic) ?>" alt="Profile" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    Cont
                    <i class="fa-regular fa-circle-user"></i>
                <?php endif; ?>
            </button>

            <div id="profile-popup" class="profile-popup" hidden>
                <button id="profile-close" class="profile-close" aria-label="Close">&times;</button>
                <div class="profile-popup-content">
                    
                    <!-- User Actions (Hidden by default, shown by JS if logged in) -->
                    <div id="popup-user-actions" style="display: none; width: 100%; display:flex; flex-direction:column; align-items:center;">
                        <?php if (isset($upcomingReservation) && $upcomingReservation): ?>
                            <div class="reservation-reminder-modern">
                                <h4>
                                    <i class="fa-solid fa-calendar-check"></i> Upcoming Reservation
                                </h4>
                                <div class="reservation-details">
                                    <div class="reservation-row">
                                        <span>Name:</span>
                                        <strong><?= htmlspecialchars($upcomingReservation['reservation_name'] ?? $currentUser['username'] ?? 'Guest') ?></strong>
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

                    <!-- Guest Actions (Shown by default, hidden by JS if logged in) -->
                    <div id="popup-guest-actions" style="width: 100%;">
                         <a href="?page=login" class="popup-action" style="text-decoration:none; display:block; margin-bottom:10px;">Login</a>
                         <a href="?page=register" class="popup-action" style="text-decoration:none; display:block;">Sign up</a>
                    </div>
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
            <li><a href="?page=cart" class="nav-link" data-page="cart">Cart</a></li>
        </div>
    </div>
</div>