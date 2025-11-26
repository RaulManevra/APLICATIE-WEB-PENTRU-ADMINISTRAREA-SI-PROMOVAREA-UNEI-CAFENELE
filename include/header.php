<div class="navbar">
    <h1>Mazi Coffee</h1>

    <ul>
        <li><a href="?page=home" class="nav-link" data-page="home">Home</a></li>
        <li><a href="?page=about" class="nav-link" data-page="about">About</a></li>
        <li><a href="?page=menu" class="nav-link" data-page="menu">Menu</a></li>
        <li><a href="?page=contact" class="nav-link" data-page="contact">Contact</a></li>
        <li><a href="#" class="nav-link" data-page="logout">Logout</a></li>
        <li class="profile-li">
            <button id="profile-btn" class="profile-icon" aria-haspopup="true" aria-expanded="false" title="Account">
                <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
            </button>

            <div id="profile-popup" class="profile-popup" hidden>
                <button id="profile-close" class="profile-close" aria-label="Close">&times;</button>
                <div class="profile-popup-content">
                    <button id="popup-login" class="popup-action">Log In</button>
                    <button id="popup-register" class="popup-action">Register</button>
                </div>
            </div>
        </li>
    </ul>

    <div class="social-icons">
        <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
    </div>
</div>