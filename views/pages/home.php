<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<section class="hero">
    <div class="hero-content">
        <h2>Wealcome<br>To Mazi Coffee</h2>
        <p>Discover hand-roasted blends from sustainable farms
        across the globe.</p>

        <button class="hero-btn">Cumpara acum</button>
    </div>
</section>