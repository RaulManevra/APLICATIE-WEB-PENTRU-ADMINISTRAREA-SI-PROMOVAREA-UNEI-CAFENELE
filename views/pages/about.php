<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<section class="about-section">
    <div class="about-card">
        <h2>About Mazi Coffee</h2>

        <p style="font-style: italic; font-weight: 600; color: #E0B574; margin-bottom: 20px;">
            "A small place in a small town for beautiful people."
        </p>
        <p>
            Situat în inima orașului Comănești, MAZI Coffee este mai mult decât o cafenea — este un loc de întâlnire.
            Un moment de pauză în care aromele și oamenii se întâlnesc.
        </p>

        <p>
            From carefully sourced beans to thoughtfully crafted brews,
            every cup reflects our passion for quality, warmth, and community.
        </p>

        <div class="social-box">
            <span class="social-title">Follow us</span>
            <div class="social-icons">
                <a href="#" class="icon instagram" aria-label="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/p/MAZI-coffee-shop-61573031510808/" class="icon facebook" aria-label="Facebook" target="_blank">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            </div>
        </div>
    </div>
</section>