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
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="error-box">
                    <span class="error-icon">⛔</span>
                    <div class="error-text">
                        <strong>Error:</strong> <span id="modal-msg"></span>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // CSS Styles for execution info
        Object.assign(modal.style, {
            position: "fixed",
            top: 0, left: 0,
            width: "100%", height: "100%",
            background: "rgba(0,0,0,0.4)",
            display: "none",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 9999
        });
        const content = modal.querySelector(".modal-content");
        Object.assign(content.style, {
            background: "#f8d7da",
            border: "1px solid #f5aaaa",
            padding: "14px 18px",
            borderRadius: "8px",
            minWidth: "320px",
            maxWidth: "420px",
            display: "flex",
            flexDirection: "column",
            position: "relative",
            color: "#842029",
            fontFamily: "Arial, sans-serif",
            fontWeight: "bold",
        });
        const errorBox = content.querySelector(".error-box");
        Object.assign(errorBox.style, {
            display: "flex",
            flexDirection: "row",
            alignItems: "center",
            gap: "10px"
        });
        const icon = content.querySelector(".error-icon");
        Object.assign(icon.style, {
            fontSize: "22px",
        });
        const closeBtn = modal.querySelector(".close");
        Object.assign(closeBtn.style, {
            position: "absolute",
            right: "10px",
            top: "8px",
            cursor: "pointer",
            fontSize: "20px",
            color: "#842029",
            opacity: 0.7
        });

        // Event Listeners
        modal.addEventListener("click", e => {
            if (e.target === modal) modal.style.display = "none";
        });
        closeBtn.addEventListener("click", () => modal.style.display = "none");
    }

    const msgEl = document.getElementById("modal-msg");
    if (msgEl) {
        msgEl.innerHTML = ""; // Clear previous content
        msgEl.innerText = message;
    }
    modal.style.display = "flex";
}

/**
 * Displays a modal with custom HTML content.
 * @param {string} html 
 */
export function showContentModal(html) {
    let modal = document.getElementById("modal");
    if (!modal) {
        // Initialize modal if not exists (reuse showModal logic basically)
        showModal("");
        modal = document.getElementById("modal");
    }

    const msgEl = document.getElementById("modal-msg");
    if (msgEl) {
        msgEl.innerHTML = html;
    }
    modal.style.display = "flex";
}
