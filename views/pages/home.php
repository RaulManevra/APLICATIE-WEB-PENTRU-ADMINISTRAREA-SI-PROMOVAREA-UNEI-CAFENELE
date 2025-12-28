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
            <img class="slide active" src="assets/img/Coffee_1.png" alt="">
            <img class="slide" src="assets/img/Coffee_2.png" alt="">
            <img class="slide" src="assets/img/Coffee_3.png" alt="">
        </div>
    </div>

    <div class="hero-right">
        <span class="hero-subtitle">Bine ai venit!</span>
        <h1 class="hero-title">Oferta<br>Săptămânii</h1>

        <p class="hero-text">
            Săptămâna asta te răsfățăm! La orice Caramel Macchiato cumpărat, primești încă unul din partea casei.
            Ofertă valabilă doar săptămâna aceasta – vino să te bucuri de gustul perfect al caramelului împreună cu un prieten!
        </p>

        <button class="hero-offer-btn nav-link" data-page="menu">
            12.99 RON 🛒
        </button>

        <div class="slider-controls">
            <button class="arrow prev">&#10094;</button>
            <div class="dots"></div>
            <button class="arrow next">&#10095;</button>
        </div>
    </div>

</section>


