/**
 * Utility functions for Mazi Coffee
 */

/**
 * Escapes HTML characters to prevent XSS.
 * @param {string} str - Input string
 * @returns {string} - Escaped string
 */
export function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

/**
 * Displays a simple modal with a message.
 * @param {string} message 
 */
export function showModal(message) {
    let modal = document.getElementById("modal");

    if (!modal) {
        modal = document.createElement("div");
        modal.id = "modal";

        modal.innerHTML = `
            <div class="modal-card">
                <div class="icon error-icon">✕</div>
                <h2 class="modal-title">Error</h2>
                <p class="modal-text" id="modal-msg"></p>
                <button class="modal-btn">Try Again</button>
            </div>
        `;

        document.body.appendChild(modal);

        /* ===== Overlay ===== */
        Object.assign(modal.style, {
            position: "fixed",
            inset: 0,
            display: "none",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 9999
        });

        /* ===== Card ===== */
        const card = modal.querySelector(".modal-card");
        Object.assign(card.style, {
            background: "#fff",
            borderRadius: "16px",
            padding: "36px 32px",
            width: "360px",
            maxWidth: "90%",
            textAlign: "center",
            boxShadow: "0 20px 40px rgba(0,0,0,0.25)",
            fontFamily: "Arial, sans-serif",

            /* Animation initial state */
            opacity: "0",
            transform: "scale(0.85)",
            transition: "opacity 0.8s ease, transform 0.8s cubic-bezier(0.22, 1, 0.36, 1)"
        });

        /* ===== Icon ===== */
        const icon = modal.querySelector(".icon");
        Object.assign(icon.style, {
            width: "72px",
            height: "72px",
            borderRadius: "50%",
            border: "8px solid #ff4d4d",
            color: "#ff4d4d",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            fontSize: "36px",
            margin: "0 auto 16px"
        });

        /* ===== Title ===== */
        const title = modal.querySelector(".modal-title");
        Object.assign(title.style, {
            color: "#ff4d4d",
            margin: "10px 0",
            fontSize: "26px",
            fontWeight: "700"
        });

        /* ===== Text ===== */
        const text = modal.querySelector(".modal-text");
        Object.assign(text.style, {
            color: "#777",
            fontSize: "15px",
            lineHeight: "1.5",
            marginBottom: "24px"
        });

        /* ===== Button ===== */
        const btn = modal.querySelector(".modal-btn");
        Object.assign(btn.style, {
            background: "#ff4d4d",
            color: "#fff",
            border: "none",
            borderRadius: "999px",
            padding: "12px 28px",
            fontSize: "16px",
            fontWeight: "600",
            cursor: "pointer",
            width: "100%",
            transition: "background-color 0.3s ease, transform 0.3s ease"
        });

        /* Hover animation */
        btn.addEventListener("mouseenter", () => {
            btn.style.backgroundColor = "#e63e3e";
            btn.style.transform = "scale(1.05)";
        });

        btn.addEventListener("mouseleave", () => {
            btn.style.backgroundColor = "#ff4d4d";
            btn.style.transform = "scale(1)";
        });

        /* Close modal */
        btn.addEventListener("click", () => {
            card.style.opacity = "0";
            card.style.transform = "scale(0.85)";
            setTimeout(() => {
                modal.style.display = "none";
            }, 300);
        });

        modal.addEventListener("click", e => {
            if (e.target === modal) {
                card.style.opacity = "0";
                card.style.transform = "scale(0.85)";
                setTimeout(() => {
                    modal.style.display = "none";
                }, 300);
            }
        });
    }

    const msgEl = document.getElementById("modal-msg");
    if (msgEl) {
        msgEl.textContent = message || "Something went wrong. Please try again.";
    }

    /* Show modal */
    modal.style.display = "flex";

    /* Trigger entrance animation */
    const card = modal.querySelector(".modal-card");
    requestAnimationFrame(() => {
        card.style.opacity = "1";
        card.style.transform = "scale(1)";
    });
}

/**
 * Displays a modal with custom HTML content.
 * @param {string} htmlContent
 */
export function showContentModal(htmlContent) {
    let modal = document.getElementById("content-modal");

    if (!modal) {
        modal = document.createElement("div");
        modal.id = "content-modal";

        // Create base structure
        modal.innerHTML = `
            <div class="modal-card" style="position: relative;">
                <button class="close-btn" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer; color: #555;">&times;</button>
                <div id="modal-content-body"></div>
            </div>
        `;

        document.body.appendChild(modal);

        // Overlay styles
        Object.assign(modal.style, {
            position: "fixed",
            inset: 0,
            display: "none",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 10000,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            backdropFilter: "blur(4px)"
        });

        // Card styles
        const card = modal.querySelector(".modal-card");
        Object.assign(card.style, {
            background: "#fff",
            borderRadius: "16px",
            padding: "20px",
            width: "500px",
            maxWidth: "95%",
            maxHeight: "90vh",
            overflowY: "auto",
            boxShadow: "0 25px 50px -12px rgba(0, 0, 0, 0.25)",
            opacity: "0",
            transform: "scale(0.95)",
            transition: "all 0.3s ease-out"
        });

        // Close behaviors
        const closeBtn = modal.querySelector(".close-btn");
        const closeModal = () => {
            card.style.opacity = "0";
            card.style.transform = "scale(0.95)";
            setTimeout(() => {
                modal.style.display = "none";
                modal.remove(); // Remove to ensure fresh state next time or keep if resizing is better
            }, 300);
        };

        closeBtn.addEventListener("click", closeModal);
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });
    }

    // Inject content
    const body = modal.querySelector("#modal-content-body");
    body.innerHTML = htmlContent;

    // Show
    modal.style.display = "flex";
    const card = modal.querySelector(".modal-card");
    requestAnimationFrame(() => {
        card.style.opacity = "1";
        card.style.transform = "scale(1)";
    });
}

