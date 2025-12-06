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
                <p id="modal-msg"></p>
            </div>`;
        document.body.appendChild(modal);

        // CSS Styles for execution info
        Object.assign(modal.style, {
            position: "fixed", top: 0, left: 0, width: "100%", height: "100%",
            background: "rgba(0,0,0,0.5)", display: "none", alignItems: "center",
            justifyContent: "center", zIndex: 9999
        });
        const content = modal.querySelector(".modal-content");
        Object.assign(content.style, {
            background: "#fff", padding: "20px", borderRadius: "8px",
            minWidth: "300px", textAlign: "center", position: "relative"
        });
        const closeBtn = modal.querySelector(".close");
        Object.assign(closeBtn.style, {
            position: "absolute", top: "5px", right: "10px",
            cursor: "pointer", fontSize: "20px"
        });

        // Event Listeners
        modal.addEventListener("click", e => {
            if (e.target === modal) modal.style.display = "none";
        });
        closeBtn.addEventListener("click", () => modal.style.display = "none");
    }

    const msgEl = document.getElementById("modal-msg");
    if (msgEl) msgEl.innerText = message;
    modal.style.display = "flex";
}
