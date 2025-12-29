<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<section class="about-section">
    <div class="about-card">
        <p style="font-style: italic; font-weight: 600; color: #E0B574; margin-bottom: 20px;">
            "A small place in a small town for beautiful people."
        </p>
        <p class="welcome-line">
            <span class="welcome-text">Bine ati venit la MAZI Coffee</span>
            <svg class="welcome-svg" height="300px" width="300px" viewBox="0 0 512 512">
                <style type="text/css">
                    .st0 {
                        fill: #ffffffd4;
                    }
                </style>
                <g>
                    <path class="st0" d="M129.18,417.603c0,19.313,15.649,34.962,34.962,34.962H474.99c19.304,0,34.963-15.649,34.963-34.962v-27.946 H129.18V417.603z" />
                    <path class="st0" d="M479.949,59.435H143.092h-11.855c-5.2,0-12.247,0-22.184,0C48.825,59.435,0,108.26,0,168.489 c0,60.228,48.925,105.641,109.054,109.064c11.634,0.662,21.792-0.542,30.686-3.192c10.229,31.871,29.19,59.826,54.286,80.807 h255.186C487.568,323.094,512,274.932,512,221.018V91.487C512,73.78,497.646,59.435,479.949,59.435z M131.238,208.791 c-6.616,3.654-14.094,5.912-22.184,5.912c-25.518,0-46.206-20.688-46.206-46.215c0-25.516,20.688-46.205,46.206-46.205 c8.09,0,15.568,2.258,22.184,5.902V208.791z" />
                </g>
            </svg>
        </p>

        <p>
                Situat în inima orașului Comănești, MAZI Coffee este mai mult decât o cafenea — este un loc de întâlnire.
            Un moment de pauză în care aromele și oamenii se întâlnesc.
        </p>

        <p>
                Un moment de pauza, unde aromele se se mipletesc cu povestile oamenilor.
            De la boabe atent selectionate pana la bauturi pregatite cu grija, fiecare
            ceasca reflecta pasiunea noastra pentru calitate. caldura si spirit de
            comunitate.
        </p>

        <div class="social-box">
            <div class="social-icons">
                <a href="#" class="icon instagram" aria-label="Instagram">
                    Instagram<i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/p/MAZI-coffee-shop-61573031510808/" class="icon facebook" aria-label="Facebook" target="_blank">
                    Facebook<i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="#" class="icon maps" aria-label="Maps">
                    Maps<i class="fa-solid fa-location-dot"></i>
                </a>
            </div>
        </div>
    </div>
</section>