<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<section class="about-section">
    <div class="about-card">
        <h2>About Mazi Coffee</h2>

        <p>
            At Mazi Coffee, we believe coffee is more than a drink — it’s a moment.
            A pause in the day where flavors, aromas, and people come together.
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
                <a href="#" class="icon facebook" aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            </div>
        </div>
    </div>
</section>