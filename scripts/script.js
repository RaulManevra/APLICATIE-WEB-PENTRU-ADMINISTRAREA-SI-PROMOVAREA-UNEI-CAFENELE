function setActiveLink(page) {
    document.querySelectorAll(".nav-link").forEach(link => {
        if (link.dataset.page === page) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });
}
function loadPage(page, pushState = true) {
    fetch(`include/${page}.php`)
        .then(res => res.text())
        .then(html => {
            app.innerHTML = html;
            updateHero(page);
            setActiveLink(page);

            if (pushState) {
                history.pushState({page}, "", `?page=${page}`);
            }
        })
        .catch(() => {
            app.innerHTML = "<h2>404 Page not found</h2>";
        });
}