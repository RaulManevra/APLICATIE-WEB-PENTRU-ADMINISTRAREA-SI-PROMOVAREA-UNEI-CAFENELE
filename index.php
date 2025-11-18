<?php
    
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mazi Coffee</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <div id="hero"></div>

    <main id="app"></main>

    <script>
    document.addEventListener("DOMContentLoaded", () => {

        const app = document.getElementById("app");
        const hero = document.getElementById("hero");

        function updateHero(page) {
            if (page === "home") {
                hero.innerHTML = `
                    <div class="content-wrapper">
                        <div class="bgimg">
                            <div class="text">Welcome to Mazi Coffee</div>
                        </div>
                    </div>
                `;
            } else {
                hero.innerHTML = "";
            }
        }

        function loadPage(page, pushState = true) {
            fetch(`include/${page}.php`)
                .then(res => res.text())
                .then(html => {
                    app.innerHTML = html;
                    updateHero(page);

                    if (pushState) {
                        history.pushState({page}, "", `?page=${page}`);
                    }
                })
                .catch(() => {
                    app.innerHTML = "<h2>404 Page not found</h2>";
                });
        }

        document.addEventListener("click", e => {
            if (e.target.classList.contains("nav-link")) {
                e.preventDefault();
                const page = e.target.dataset.page;
                loadPage(page);
            }
        });

        window.addEventListener("popstate", e => {
            if (e.state && e.state.page) {
                loadPage(e.state.page, false);
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        loadPage(urlParams.get("page") || "home", false);
    });
    </script>
<script src="script.js"></script>
</body>
</html>