// ===== GLOBAL REFERENCES =====
const app = document.getElementById("app");
const hero = document.getElementById("hero");

// ===== HERO SECTION UPDATE =====
function updateHero(page) {
    if (page === "home") {
        hero.innerHTML = `
            <div class="content-wrapper">
                <div class="bgimg">
                    <div class="text">Welcome to Mazi Coffee </div>
                </div>
            </div>
        `;
    } else {
        hero.innerHTML = "";
    }
}

// ===== NAV LINK ACTIVE STATE =====
function setActiveLink(page) {
    document.querySelectorAll(".nav-link").forEach(link => {
        link.classList.toggle("active", link.dataset.page === page);
    });
}

// ===== LOAD PAGE VIA AJAX =====
function loadPage(page, pushState = true) {
    return new Promise((resolve, reject) => {
        // ===== PLACEHOLDER: Start page transition / spinner animation here =====
        // e.g., fade out current content or show loading spinner

        fetch(`include/${page}.php`)
            .then(res => res.text())
            .then(html => {
                app.innerHTML = html;
                updateHero(page);
                setActiveLink(page);

                if (pushState) {
                    history.pushState({ page }, "", `?page=${page}`);
                }

                // ===== PLACEHOLDER: End page transition / fade-in animation here =====
                // e.g., fade in new content, hide spinner

                resolve(); // resolves when page is fully loaded and updated
            })
            .catch(() => {
                app.innerHTML = "<h2>404 Page not found</h2>";
                reject(); // reject on failure
            });
    });
}

// ===== MODAL FUNCTION =====
function showModal(message) {
    let modal = document.getElementById("modal");
    if (!modal) {
        modal = document.createElement("div");
        modal.id = "modal";
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <p id="modal-msg"></p>
            </div>`;
        document.body.appendChild(modal);
        modal.querySelector(".close").onclick = () => modal.style.display = "none";

        modal.addEventListener("click", e => {
        if (e.target === modal) modal.style.display = "none";
        });

        // Basic modal styling (adjust in CSS)
        Object.assign(modal.style, {
            position: "fixed",
            top: 0,
            left: 0,
            width: "100%",
            height: "100%",
            background: "rgba(0,0,0,0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 9999,
            display: "none"
        });
        Object.assign(modal.querySelector(".modal-content").style, {
            background: "#fff",
            padding: "20px",
            borderRadius: "8px",
            minWidth: "300px",
            textAlign: "center",
            position: "relative"
        });
        Object.assign(modal.querySelector(".close").style, {
            position: "absolute",
            top: "5px",
            right: "10px",
            cursor: "pointer",
            fontSize: "20px"
        });
    }

    document.getElementById("modal-msg").innerText = message;
    modal.style.display = "flex";
}

// ===== HANDLE FORM SUBMISSION VIA AJAX =====
document.addEventListener("submit", async e => {
    const form = e.target;

    if (form.matches("form[action*='register_handler.php'], form[action*='login_handler.php']")) {
        e.preventDefault();

        const formData = new FormData(form);
        const action = form.getAttribute("action");

        try {
            const res = await fetch(action, { method: "POST", body: formData });
            const data = await res.json();

            if (data.success) {
                loadPage(data.redirect);
            } else {
                showModal(data.message);
            }
        } catch (err) {
            console.error(err);
            showModal("An error occurred. Please try again.");
        }
    }
});

// ===== HANDLE NAVIGATION CLICK =====
document.addEventListener("click", async e => {
    if (e.target.classList.contains("nav-link")) {
        e.preventDefault();
        const page = e.target.dataset.page;

        if (page === "logout") {
            try {
                const res = await fetch('include/logout.php');
                const data = await res.json();
                if (data.success) {
                    // ===== PLACEHOLDER: Optional animation before redirect =====
                    await loadPage(data.redirect);
                    // ===== PLACEHOLDER: Optional animation after redirect =====
                }
            } catch (err) {
                showModal("Logout failed. Try again.");
            }
        } else {
            // You can await this if you want chained animations
            await loadPage(page);
        }
    }
});

// ===== HANDLE BACK/FORWARD BUTTONS =====
window.addEventListener("popstate", e => {
    if (e.state && e.state.page) {
        loadPage(e.state.page, false);
    }
});

// ===== INITIAL PAGE LOAD =====
const urlParams = new URLSearchParams(window.location.search);
loadPage(urlParams.get("page") || "home", false);
