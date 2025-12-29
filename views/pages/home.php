<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<section class="hero">
    <link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@1,700&display=swap"
  rel="stylesheet">

    
    <div class="hero-left">
        <div class="hero-slider" id="homeSlider">
            <!-- Slides will be injected dynamically -->
        </div>
    </div>

    <div class="hero-right">
        <div id="slide-content-wrapper">
            <span class="hero-subtitle" id="hero-subtitle">Bine ai venit!</span>
            <h1 class="hero-title" id="hero-title">Oferta<br>Săptămânii</h1>
            <p class="hero-text" id="hero-text">
                Săptămâna asta te răsfățăm! La orice Caramel Macchiato cumpărat, primești încă unul din partea casei.
                Ofertă valabilă doar săptămâna aceasta – vino să te bucuri de gustul perfect al caramelului împreună cu un prieten!
            </p>
            <a href="?page=menu" class="hero-offer-btn nav-link" id="hero-btn" data-page="menu">Vezi Meniul</a>
        </div>

        <div class="slider-controls">
            <button class="arrow prev">&#10094;</button>
            <div class="dots"></div>
            <button class="arrow next">&#10095;</button>
        </div>
    </div>

</section>

<!-- Features Section -->
<section class="features-section home-box">
    <h2 class="section-title">De ce Mazi?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-mug-hot feature-icon"></i>
            <h3>Cafea de Specialitate</h3>
            <p>Folosim doar boabe 100% Arabica, prăjite local pentru o aromă inconfundabilă.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-bread-slice feature-icon"></i>
            <h3>Patiserie Artizanală</h3>
            <p>Croissante și prăjituri proaspete, pregătite în fiecare dimineață în laboratorul nostru.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-couch feature-icon"></i>
            <h3>Atmosferă Relaxantă</h3>
            <p>Locul perfect pentru a lucra, a citi o carte sau a te întâlni cu prietenii.</p>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="about-container home-box">
        <div class="about-content">
            <h2 class="section-title" style="text-align: left;">Povestea Noastră</h2>
            <p class="about-text" style="font-style: italic; color: #2b1205;">
                "A small place in a small town for beautiful people."
            </p>
            <p class="about-text">
                MAZI Coffee s-a născut în Comănești din dorința de a aduce comunitatea împreună.
                Fiecare ceașcă pe care o servim este rezultatul unei călătorii lungi, de la fermierii dedicați până la barista noștri pasionați.
            </p>
            <p class="about-text">
                Credem că o cafea excelentă poate face ziua mai bună. Te invităm să descoperi aromele noastre și să te bucuri de moment.
            </p>
            <a href="?page=about" class="btn-primary nav-link" data-page="about" style="margin-top: 20px; display: inline-block;">Citește mai mult</a>
        </div>
        <div class="about-image">
            <img src="assets/img/slider_1.png" alt="Mazi Coffee Interior" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>
    </div>
</section>


