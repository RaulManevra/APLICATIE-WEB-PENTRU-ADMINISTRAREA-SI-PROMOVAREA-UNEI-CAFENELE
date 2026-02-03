<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<!-- CAFE THEME DEPENDENCIES -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

<style>
    /* CAFE THEME VARS */
    :root {
        --coffee-dark: #2c1810;
        --coffee-med: #4e342e;
        --coffee-light: #8d6e63;
        --cream: #f5f0e1;
        --paper: #fffdf5;
        --accent: #d4af37; /* Gold */
        --success: #2e7d32;
        --error: #c62828;
        --shadow: rgba(44, 24, 16, 0.15);
    }

    /* GLOBAL OVERRIDES */
    /* Fix: Use classes .header, .navbar instead of tags/ids which might not match header.php */
    .header, .navbar, #navbar, #hero, footer, .toggle_btn, .dropdown_menu { display: none !important; }
    
    html, body { 
        margin: 0; padding: 0; width: 100%; height: 100%; 
        overflow: hidden !important; /* Force no scroll */
        background: #fdfbf7; 
        font-family: 'Outfit', sans-serif;
        color: var(--coffee-dark);
    }
    main#app { 
        padding: 0 !important; margin: 0 !important; width: 100%; height: 100%;
        display: block !important; 
    }

    /* LAYOUT CONTAINER */
    .payment-layout {
        display: flex;
        width: 100vw;
        height: 100vh; /* Strict full height */
        overflow-x: hidden;
        overflow-y: auto; /* Internal scrolling if needed */
        position: fixed; top: 0; left: 0; z-index: 10;
        background: #fdfbf7;
    }

    /* --- LEFT: RECEIPT SIDE --- */
    .receipt-section {
        flex: 1;
        background: radial-gradient(circle at center, #fbf8f1, #eaddcf);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
        position: relative;
    }
    
    .receipt-paper {
        background: var(--paper);
        width: 100%;
        max-width: 380px;
        padding: 30px;
        box-shadow: 0 10px 30px var(--shadow), 0 1px 3px rgba(0,0,0,0.05);
        position: relative;
        transform: rotate(-1deg);
        transition: transform 0.3s ease;
    }
    .receipt-paper:hover { transform: rotate(0deg) scale(1.02); }
    
    .receipt-paper::before {
        content: ''; position: absolute; top: -10px; left: 0; width: 100%; height: 20px;
        background: radial-gradient(circle, transparent 70%, var(--paper) 70%);
        background-size: 20px 20px;
        background-position: 5px 10px;
    }

    .shop-brand { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 20px; margin-bottom: 20px; }
    .shop-brand h1 { 
        font-family: 'Playfair Display', serif; font-size: 2rem; margin: 0; color: var(--coffee-dark); 
        letter-spacing: -0.5px;
    }
    .shop-brand p { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: var(--coffee-light); margin-top: 5px; }

    .receipt-items { min-height: 100px; margin-bottom: 20px; font-size: 0.95rem; }
    .receipt-row { display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px dotted #eee; padding-bottom: 4px; }
    .receipt-row span:first-child { color: #555; }
    .receipt-row span:last-child { font-weight: 600; color: var(--coffee-dark); font-family: 'Courier New', monospace; }

    .receipt-total { 
        border-top: 2px dashed var(--coffee-dark); padding-top: 15px; margin-top: 10px; 
        display: flex; justify-content: space-between; align-items: flex-end;
    }
    .total-label { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
    .total-amount { font-size: 2rem; font-family: 'Playfair Display', serif; font-weight: 700; color: var(--coffee-dark); }

    /* --- RIGHT: PAYMENT SIDE --- */
    .payment-section {
        flex: 1.2;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
        box-shadow: -10px 0 40px rgba(0,0,0,0.03);
        z-index: 10;
        position: relative;
    }
    
    .payment-container { width: 100%; max-width: 480px; }
    
    .payment-header { margin-bottom: 40px; text-align: left; }
    .payment-header h2 { 
        font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--coffee-dark); margin: 0 0 10px 0; 
    }
    .payment-header p { color: var(--coffee-light); font-size: 1.1rem; }

    .back-link {
        position: absolute; top: 40px; right: 40px;
        color: var(--coffee-light); text-decoration: none; font-weight: 500; font-size: 0.9rem;
        display: flex; align-items: center; gap: 8px; transition: color 0.2s;
    }
    .back-link:hover { color: var(--error); }

    /* CARD UPDATES */
    .card-perspective { perspective: 1000px; width: 100%; height: 240px; margin-bottom: 40px; }
    .card-inner { position: relative; width: 100%; height: 100%; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); transform-style: preserve-3d; }
    .card-perspective.flipped .card-inner { transform: rotateY(180deg); }
    
    .card-face {
        position: absolute; inset: 0; backface-visibility: hidden;
        border-radius: 16px; padding: 25px;
        background: linear-gradient(135deg, #2c1810 0%, #4e342e 100%); /* Coffee Gradient */
        color: #fff; display: flex; flex-direction: column; justify-content: space-between;
        box-shadow: 0 20px 40px -10px rgba(44, 24, 16, 0.5);
    }
    .card-face::after {
        content: ''; position: absolute; inset: 0; 
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1h2v2H1V1zm4 4h2v2H5V5zm4 4h2v2H9V9zm4 4h2v2h-2v-2zm4 4h2v2h-2v-2z' fill='%23ffffff' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.5; pointer-events: none;
    }
    
    .card-back { transform: rotateY(180deg); padding: 0; justify-content: flex-start; }
    
    /* Branding Colors */
    .visa .card-face { background: linear-gradient(135deg, #1A237E, #0D47A1); }
    .mastercard .card-face { background: linear-gradient(135deg, #E65100, #EF6C00); }
    .amex .card-face { background: linear-gradient(135deg, #004d40 0%, #00695c 100%); }
    .discover .card-face { background: linear-gradient(135deg, #f57f17 0%, #ff6f00 100%); }
    .diners .card-face { background: linear-gradient(135deg, #263238 0%, #37474f 100%); }
    .jcb .card-face { background: linear-gradient(135deg, #b71c1c 0%, #0d47a1 100%); }
    
    /* Card Text Updates */
    .card-chip { background: linear-gradient(135deg, #FFD700, #B8860B); border-radius: 6px; width: 45px; height: 32px; }
    .card-number-display { font-family: 'Courier New', monospace; font-size: 1.6rem; letter-spacing: 3px; text-shadow: 0 2px 2px rgba(0,0,0,0.3); margin-top: 10px; }
    .card-label { font-size: 0.65rem; text-transform: uppercase; color: rgba(255,255,255,0.7); letter-spacing: 1px; margin-bottom: 2px; }
    .card-val { font-size: 1rem; font-family: 'Courier New', monospace; text-shadow: 0 1px 1px rgba(0,0,0,0.5); }

    /* FORM STYLING */
    .form-group { margin-bottom: 25px; position: relative; }
    .form-group label {
        display: block; font-size: 0.8rem; color: var(--coffee-light); margin-bottom: 6px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .form-control {
        width: 100%; border: none; border-bottom: 2px solid #ddd; background: transparent; 
        border-radius: 0; padding: 12px 0; font-family: 'Outfit', sans-serif; font-size: 1.1rem; color: var(--coffee-dark);
        transition: border-color 0.3s;
    }
    .form-control:focus { outline: none; border-color: var(--coffee-dark); }
    .form-control::placeholder { color: #ccc; font-weight: 300; }
    
    /* Validation Errors */
    .form-control.error { border-color: var(--error); }
    .error-msg { position: absolute; right: 0; bottom: -20px; font-size: 0.75rem; color: var(--error); opacity: 0; transition: opacity 0.3s; }
    .form-control.error ~ .error-msg { opacity: 1; }

    .pay-btn {
        width: 100%; background: var(--coffee-dark); color: #fff; padding: 18px; border: none; border-radius: 8px;
        font-size: 1.1rem; font-weight: 600; letter-spacing: 1px; cursor: pointer; margin-top: 20px;
        box-shadow: 0 8px 20px rgba(44, 24, 16, 0.3); transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px;
    }
    .pay-btn:hover { background: var(--coffee-med); transform: translateY(-2px); box-shadow: 0 12px 25px rgba(44, 24, 16, 0.4); }
    
    /* Loader Overrides */
    #fullscreen-loader { 
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        margin: 0; padding: 0;
        background: #fdfbf7; /* Opaque to hide everything behind */
        z-index: 2147483647;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }
    #fullscreen-loader.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
    
    /* Loader Icons */
    .loader-icon-container { font-size: 4rem; color: var(--coffee-dark); margin-bottom: 20px; height: 80px; display: flex; align-items: center; justify-content: center; }
    
    /* Animations */
    @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 0.5; } 100% { transform: scale(1.2); opacity: 0; } }
    @keyframes scan-line { 0% { clip-path: inset(0 0 100% 0); } 50% { clip-path: inset(0 0 0 0); } 100% { clip-path: inset(100% 0 0 0); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    @keyframes spin-slow { 100% { transform: rotate(360deg); } }

    .anim-pulse { animation: pulse-ring 2s infinite; }
    .anim-float { animation: float 2s ease-in-out infinite; }
    .anim-spin { animation: spin-slow 3s linear infinite; }
    
    .status-check { color: var(--success); font-size: 5rem; animation: float 3s ease-in-out infinite; }
    
    .spinner-ring { display: none; } /* Hide old spinner */
    #status-modal {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(44, 24, 16, 0.9); backdrop-filter: blur(5px);
        z-index: 2147483647; /* Max Int Z-Index */
        display: none; align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.3s;
    }
    /* ... existing ... */

    /* UPDATED DELAY IN JS SCRIPT SECTION IS HANDLED BY REPLACING THE FUNCTION BELOW */
    #status-modal.visible { opacity: 1; }
    .status-content {
        background: #fff; padding: 40px; border-radius: 12px; width: 90%; max-width: 400px;
        text-align: center; transform: scale(0.9); transition: transform 0.3s;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    #status-modal.visible .status-content { transform: scale(1); }
    
    .status-icon { font-size: 4rem; margin-bottom: 20px; }
    .status-icon.success { color: #2ecc71; }
    .status-icon.error { color: #e74c3c; }
    
    .status-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 10px; color: #2c1810; }
    .status-msg { font-size: 1rem; color: #555; margin-bottom: 30px; line-height: 1.5; }
    
    #status-btn {
        background: #2c1810; color: #fff; border: none; padding: 12px 30px; 
        font-family: 'Outfit', sans-serif; font-weight: 600; border-radius: 6px; cursor: pointer;
        transition: background 0.2s;
    }
    #status-btn:hover { background: #4e342e; }
    
    /* Responsiveness */
    @media (max-width: 900px) {
        .payment-layout { flex-direction: column-reverse; overflow-y: auto; }
        .receipt-section { border-top: 1px solid #ddd; padding: 30px 20px; }
        .payment-section { padding: 40px 20px; box-shadow: none; min-height: auto; }
        .back-link { position: relative; top: 0; right: 0; margin-bottom: 20px; justify-content: flex-end; }
        .card-perspective { height: 200px; }
        .card-number-display { font-size: 1.3rem; }
    }
</style>

<!-- STATUS MODAL -->
<div id="status-modal">
    <div class="status-content">
        <div class="status-icon" id="status-icon"><i class="fas fa-check-circle"></i></div>
        <div class="status-title" id="status-title">Success</div>
        <div class="status-msg" id="status-msg">Your order has been placed.</div>
        <button id="status-btn">Continue</button>
    </div>
</div>

<!-- Full Screen Secure Loader -->
<div id="fullscreen-loader">
    <div class="loader-content">
        <div id="loader-icon-area" class="loader-icon-container">
            <!-- Dynamic Icon Here -->
            <i class="fas fa-satellite-dish anim-pulse"></i>
        </div>
        <div id="loader-message" class="loader-text" style="font-family:'Playfair Display',serif;">Initializing Secure Channel...</div>
    </div>
</div>

<div class="payment-layout">
    <!-- LEFT: RECEIPT -->
    <div class="receipt-section">
        <div class="receipt-paper">
            <div class="shop-brand">
                <h1>Mazi Coffee</h1>
                <p>Est. 2024</p>
            </div>
            <div class="receipt-items" id="receipt-items-container">
                <!-- Items injected by JS -->
                <div style="text-align:center; color:#999; padding:20px;">Fetching Order...</div>
            </div>
            <div class="receipt-total">
                <span class="total-label">Total Due</span>
                <span class="total-amount" id="receipt-total-display">--.--</span>
            </div>
            <div style="text-align:center; margin-top:20px; font-size:0.7rem; color:#aaa;">
                Tax ID: RO-12345678<br>Thank you for visiting!
            </div>
        </div>
    </div>

    <!-- RIGHT: PAYMENT -->
    <div class="payment-section">
        <div class="payment-container">
            <a href="?page=home" class="back-link">
                <i class="fas fa-chevron-left"></i> Return to Menu
            </a>

            <div class="payment-header">
                <h2>Secure Checkout</h2>
                <p>Please enter your payment details below.</p>
            </div>

            <!-- Credit Card Visual -->
            <div class="card-perspective" id="card-container">
                <div class="card-inner" id="card-inner">
                    <!-- FRONT -->
                    <div class="card-face card-front">
                        <div class="card-top">
                            <div class="card-chip"></div>
                            <div class="card-brand" id="card-brand-icon"><i class="fas fa-credit-card"></i></div>
                        </div>
                        <div class="card-number-display" id="display-number">•••• •••• •••• ••••</div>
                        <div class="card-details-row">
                            <div><span class="card-label">Holder Name</span><div class="card-val" id="display-name">YOUR NAME</div></div>
                            <div><span class="card-label">Expires</span><div class="card-val" id="display-expiry">MM/YY</div></div>
                        </div>
                    </div>
                    <!-- BACK -->
                    <div class="card-face card-back">
                        <div class="card-strip" style="background:#000; height:40px; margin-top:30px; width:100%;"></div>
                        <div style="padding: 0 25px; margin-top:20px; width:100%;">
                            <span class="card-label" style="text-align:right;">CVV / CVC</span>
                            <div class="cvv-display" id="display-cvv" style="background:#fff; color:#000; padding:8px; text-align:right; font-family:'Courier New', monospace; border-radius:4px;">•••</div>
                        </div>
                        <div style="margin-top:auto; padding:0 25px 25px; text-align:center; opacity:0.6; font-size:0.7rem;">
                            <i class="fas fa-lock"></i> Encrypted Transaction
                        </div>
                    </div>
                </div>
            </div>

            <form id="payment-form" novalidate>
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" id="card-number" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                    <span class="error-msg">Invalid Card Number</span>
                </div>

                <div class="form-group">
                    <label>Card Holder Name</label>
                    <input type="text" id="card-holder" class="form-control" placeholder="Jane Doe">
                    <span class="error-msg">Name is required</span>
                </div>

                <div class="form-row" style="display:flex; gap:20px;">
                    <div class="form-group" style="flex:1;">
                        <label>Expires</label>
                        <input type="text" id="card-expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                        <span class="error-msg">MM/YY</span>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>CVV</label>
                        <input type="password" id="card-cvv" class="form-control" placeholder="123" maxlength="4">
                        <span class="error-msg">3 digits</span>
                    </div>
                </div>

                <button type="submit" class="pay-btn" id="submit-payment">
                    Complete Order <i class="fas fa-arrow-right"></i>
                </button>
                
                <div style="text-align:center; margin-top:20px; color:#999; font-size:0.8rem; display:flex; justify-content:center; gap:10px;">
                    <i class="fas fa-shield-alt"></i> 256-bit SSL Secure
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        // --- PORTAL UI TO BODY ---
        // Moves overlays out of #app to ensure they are top-level
        const loader = document.getElementById('fullscreen-loader');
        const modal = document.getElementById('status-modal');
        if(loader && loader.parentElement !== document.body) document.body.appendChild(loader);
        if(modal && modal.parentElement !== document.body) document.body.appendChild(modal);

        // --- LOADER & STATE ---
        // --- LOADER & STATE ---
        const loaderMsg = document.getElementById('loader-message');
        const loaderIconArea = document.getElementById('loader-icon-area');
        
        // State
        let serverPublicKey = null;
        let sessionKey = null; // CryptoKey (AES)
        let exportedSessionKey = null; // ArrayBuffer (Raw AES key)

        async function updateLoader(msg, iconClass, delay = 0) {
            if(loader) loader.classList.remove('hidden');
            if(msg) loaderMsg.innerText = msg;
            
            if(loaderIconArea && iconClass) {
                loaderIconArea.innerHTML = `<i class="${iconClass}"></i>`;
            }
            if(delay) await new Promise(r => setTimeout(r, delay));
        }

        function showStatus(isSuccess, title, msg, callback) {
            if(modal) {
                modal.style.display = 'flex';
                // Force reflow
                void modal.offsetWidth;
                modal.classList.add('visible');
                
                const modalIcon = document.getElementById('status-icon');
                const modalTitle = document.getElementById('status-title');
                const modalMsg = document.getElementById('status-msg');
                const modalBtn = document.getElementById('status-btn');

                if(modalIcon) {
                    modalIcon.className = 'status-icon ' + (isSuccess ? 'success' : 'error');
                    modalIcon.innerHTML = isSuccess ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
                }
                if(modalTitle) modalTitle.innerText = title;
                if(modalMsg) modalMsg.innerText = msg;
                
                if(modalBtn) {
                    modalBtn.onclick = () => {
                        modal.classList.remove('visible');
                        setTimeout(() => { modal.style.display = 'none'; if(callback) callback(); }, 300);
                    };
                }
            } else {
                alert(title + ": " + msg); // Fallback
                if(callback) callback();
            }
        }

        async function initSecureChannel() {
            try {
                // 1. Fetch Server Public Key
                await updateLoader("Establishing Secure Channel...", "fas fa-satellite-dish anim-pulse", 800);
                
                const res = await fetch('?page=cart_handler&action=get_public_key', {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                
                let data;
                try { data = await res.json(); } catch (jsonErr) { throw new Error("Invalid Response"); }
                
                if(!data.success || !data.publicKey) throw new Error(data.message || "Key Exchange Failed");
                
                // Import RSA Key
                await updateLoader("Verifying Server Identity...", "fas fa-fingerprint anim-float", 800);
                serverPublicKey = await importRsaKey(data.publicKey);
                
                // 2. Generate Session Key (AES-GCM)
                sessionKey = await window.crypto.subtle.generateKey(
                    { name: "AES-GCM", length: 256 },
                    true,
                    ["encrypt", "decrypt"]
                );

                // Export Session Key for Transport
                exportedSessionKey = await window.crypto.subtle.exportKey("raw", sessionKey);
                
                // Show Success
                await updateLoader("Secure Connection Encrypted", "fas fa-lock status-check", 2000);
                
                // Hide Loader
                loader.classList.add('hidden');

            } catch (e) {
                console.error("Security Error:", e);
                loader.classList.add('hidden');
                showStatus(false, "Security Error", "Secure Channel Handshake Failed.\n" + (e.message || ""));
            }
        }

        // Helper: Import PEM Public Key
        function importRsaKey(pem) {
            const result = pem.replace(/-----BEGIN PUBLIC KEY-----/g, "").replace(/-----END PUBLIC KEY-----/g, "").replace(/\s/g, "");
            const binaryDerString = window.atob(result);
            const binaryDer = new Uint8Array(binaryDerString.length);
            for (let i = 0; i < binaryDerString.length; i++) { binaryDer[i] = binaryDerString.charCodeAt(i); }

            return window.crypto.subtle.importKey(
                "spki",
                binaryDer.buffer,
                { name: "RSA-OAEP", hash: "SHA-1" }, 
                false,
                ["encrypt"]
            );
        }

        // --- DATA SETUP & INIT ---
        const pendingOrderJson = sessionStorage.getItem('pendingOrder');
        const pendingOrder = pendingOrderJson ? JSON.parse(pendingOrderJson) : null;

        if (!pendingOrder) {
             updateLoader("Session Expired. Redirecting...", false, 0);
             setTimeout(() => { window.location.href = '?page=cart'; }, 2000);
             // Return not needed as we want to load UI anyway for effect, but it blocks
             // return;
        }

        // --- POPULATE RECEIPT ---
        function formatDate(isoStr) {
            if(!isoStr || !isoStr.includes('T')) return isoStr;
            try {
                const d = new Date(isoStr);
                const date = d.toLocaleDateString('ro-RO', { month: 'short', day: 'numeric' });
                const time = d.toLocaleTimeString('ro-RO', { hour: '2-digit', minute:'2-digit' });
                return `${date}, ${time}`;
            } catch(e) { return isoStr.replace('T', ' '); }
        }

        if (pendingOrder) {
            const receiptContainer = document.getElementById('receipt-items-container');
            const receiptTotal = document.getElementById('receipt-total-display');
            
            if (receiptContainer) {
                receiptContainer.innerHTML = '';
                const prettyTime = formatDate(pendingOrder.pickupTime);
                
                receiptContainer.innerHTML += `<div class="receipt-row"><span>Pickup Time</span><span>${prettyTime}</span></div>`;
                receiptContainer.innerHTML += `<div class="receipt-row" id="temp-total"><span>Order Total</span><span>...</span></div>`;
            }

            fetch('?page=cart_handler&action=get_cart')
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        if(receiptTotal) receiptTotal.innerText = d.total.toFixed(2) + ' LEI';
                        if(receiptContainer) {
                             const temp = document.getElementById('temp-total');
                             if(temp) temp.remove();
                             
                             let html = '';
                             if(d.items) {
                                 d.items.forEach(item => {
                                     html += `<div class="receipt-row">
                                        <span>${item.name} x${item.quantity}</span>
                                        <span>${(item.price * item.quantity).toFixed(2)}</span>
                                     </div>`;
                                 });
                             }
                             // Prepend items
                             receiptContainer.insertAdjacentHTML('afterbegin', html);
                        }
                    }
                });
        }

        // Start Handshake
        if(window.crypto && window.crypto.subtle) {
            initSecureChannel();
        } else {
            console.error("WebCrypto API missing");
            updateLoader("Secure Browser Required", false, 0);
            if(spinner) spinner.style.display = 'none';
        }

        // --- ELEMENTS ---
        const form = document.getElementById('payment-form');
        const numInput = document.getElementById('card-number');
        const nameInput = document.getElementById('card-holder');
        const expInput = document.getElementById('card-expiry');
        const cvvInput = document.getElementById('card-cvv');
        
        const cardContainer = document.getElementById('card-container');
        const cardInner = document.getElementById('card-inner');
        const brandIcon = document.getElementById('card-brand-icon');
        
        const numDisplay = document.getElementById('display-number');
        const nameDisplay = document.getElementById('display-name');
        const expDisplay = document.getElementById('display-expiry');
        const cvvDisplay = document.getElementById('display-cvv');

        // --- FLIP LOGIC ---
        if(cvvInput && cardContainer) {
            cvvInput.addEventListener('focus', () => { cardContainer.classList.add('flipped'); });
            cvvInput.addEventListener('blur', () => { cardContainer.classList.remove('flipped'); });
        }

        // --- CARD UTILS ---
        function getCardType(number) {
            const re = {
                visa: /^4/,
                mastercard: /^(5[1-5]|2[2-7])/,
                amex: /^3[47]/,
                discover: /^6(?:011|5)/,
                diners: /^3(?:0[0-5]|[68])/,
                jcb: /^(?:2131|1800|35)/
            };
            for(let key in re) { if(re[key].test(number)) return key; }
            return 'unknown';
        }

        function updateCardBrand(type) {
            if(!cardInner) return;
            cardInner.classList.remove('visa', 'mastercard', 'amex', 'discover', 'diners', 'jcb');
            if (type !== 'unknown') cardInner.classList.add(type);
            
            let icon = '<i class="fas fa-credit-card"></i>';
            if(type === 'visa') icon = '<i class="fab fa-cc-visa"></i>';
            if(type === 'mastercard') icon = '<i class="fab fa-cc-mastercard"></i>';
            if(type === 'amex') icon = '<i class="fab fa-cc-amex"></i>';
            if(type === 'discover') icon = '<i class="fab fa-cc-discover"></i>';
            if(type === 'diners') icon = '<i class="fab fa-cc-diners-club"></i>';
            if(type === 'jcb') icon = '<i class="fab fa-cc-jcb"></i>';
            if(brandIcon) brandIcon.innerHTML = icon;
        }

        // --- INPUT HANDLING ---
        function clearError(input) { 
            input.classList.remove('error');
            const grp = input.closest('.form-group');
            if(grp) {
                const span = grp.querySelector('.error-msg');
                if(span) span.style.opacity = '0';
            }
        }
        function showError(input, msg) {
            input.classList.add('error');
            const grp = input.closest('.form-group');
            if(grp) {
                const span = grp.querySelector('.error-msg');
                if(span) { span.innerText = msg; span.style.opacity = '1'; }
            }
        }

        if(numInput) {
            numInput.addEventListener('input', (e) => {
                clearError(e.target);
                let val = e.target.value.replace(/\D/g, '').substring(0, 16);
                e.target.value = val.match(/.{1,4}/g)?.join(' ') || val;
                if(numDisplay) numDisplay.innerText = e.target.value || '•••• •••• •••• ••••';
                updateCardBrand(getCardType(val));
            });
        }

        if(nameInput) {
            nameInput.addEventListener('input', (e) => {
                clearError(e.target);
                if(nameDisplay) nameDisplay.innerText = e.target.value.toUpperCase() || 'FULL NAME';
            });
        }

        if(expInput) {
            expInput.addEventListener('input', (e) => {
                clearError(e.target);
                let val = e.target.value.replace(/\D/g, '');
                if(val.length >= 2) val = val.substring(0,2) + '/' + val.substring(2,4); // Auto slash
                e.target.value = val;
                if(expDisplay) expDisplay.innerText = val || 'MM/YY';
            });
        }
        
        if(cvvInput) {
            cvvInput.addEventListener('input', (e) => {
                clearError(e.target);
                let val = e.target.value.replace(/\D/g, '').substring(0,4);
                e.target.value = val;
                if(cvvDisplay) cvvDisplay.innerText = val || '•••';
            });
        }

        // --- SUBMIT ---
        if(form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                let isValid = true;
                
                // 1. Number
                const rawNum = numInput.value.replace(/\s/g, '');
                if (rawNum.length < 13 || rawNum.length > 16) { showError(numInput, "Invalid number"); isValid = false; }
                
                // 2. Name
                if (nameInput.value.trim().length < 3) { showError(nameInput, "Full name required"); isValid = false; }
                
                // 3. Expiry Check
                const expVal = expInput.value;
                const expParts = expVal.split('/');
                let dateValid = false;
                if(expParts.length === 2) {
                    const m = parseInt(expParts[0], 10);
                    const y = parseInt(20 + expParts[1], 10); // Assume 20xx
                    const now = new Date();
                    const currentYear = now.getFullYear();
                    const currentMonth = now.getMonth() + 1;
                    
                    if(m >= 1 && m <= 12 && !isNaN(y)) {
                        if (y > currentYear || (y === currentYear && m >= currentMonth)) {
                            dateValid = true;
                        } else {
                            showError(expInput, "Card expired"); // Past date
                        }
                    } else {
                         showError(expInput, "Invalid month");
                    }
                } else {
                    showError(expInput, "Format MM/YY");
                }
                if(!dateValid && isValid) isValid = false; // logic flag

                // 4. CVV
                if (cvvInput.value.length < 3) { showError(cvvInput, "Invalid CVV"); isValid = false; }

                if (!isValid) return;

                // --- PROCEED ---
                if (!pendingOrder) {
                    showStatus(false, "Session Error", "Session expired. Redirecting...", () => window.location.href='?page=cart');
                    return;
                }
                if (!sessionKey || !serverPublicKey) {
                    showStatus(false, "Connection Error", "Secure handshake not completed. Reload page.");
                    return;
                }

                // UI Loading
                await updateLoader("Encrypting Payment Data...", "fas fa-key anim-float", 0);
                
                try {
                    // Encrypt Data
                    await new Promise(r => setTimeout(r, 800)); // Visual "Encrypting" delay
                    
                    const sensitiveData = JSON.stringify({
                        cardNumber: rawNum,
                        cardHolder: nameInput.value,
                        expiry: expInput.value,
                        cvv: cvvInput.value,
                        token: pendingOrder.token,
                        pickup_time: pendingOrder.pickupTime
                    });
                    
                    const iv = window.crypto.getRandomValues(new Uint8Array(12));
                    const encodedData = new TextEncoder().encode(sensitiveData);
                    const ciphertext = await window.crypto.subtle.encrypt({ name: "AES-GCM", iv: iv }, sessionKey, encodedData);
                    const encryptedSessionKey = await window.crypto.subtle.encrypt({ name: "RSA-OAEP" }, serverPublicKey, exportedSessionKey);

                     // Transport
                    function ab2b64(buf) { 
                        let b = ''; const u = new Uint8Array(buf); 
                        for(let i=0; i<u.byteLength; i++) b+=String.fromCharCode(u[i]); 
                        return window.btoa(b);
                    }
                    
                    const formData = new FormData();
                    formData.append('action', 'checkout');
                    formData.append('encrypted_key', ab2b64(encryptedSessionKey));
                    formData.append('encrypted_data', ab2b64(ciphertext));
                    formData.append('iv', ab2b64(iv.buffer));
                    formData.append('payment_method', 'card');

                    await updateLoader("Processing Transaction...", "fas fa-credit-card anim-pulse", 1000); // "Bank" delay

                    const res = await fetch('?page=cart_handler&action=checkout', {
                        method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                    
                    let data = {};
                    try { data = await res.json(); } catch(e) {}
                    
                    loader.classList.add('hidden'); // Hide loader

                    if (data.success) {
                        sessionStorage.removeItem('pendingOrder');
                        showStatus(true, "Payment Approved", "Order placed successfully!", () => {
                             window.location.href = '?page=home';
                        });
                    } else {
                        throw new Error(data.message || 'Declined');
                    }

                } catch(error) {
                    loader.classList.add('hidden');
                    console.error(error);
                    showStatus(false, "Transaction Failed", error.message);
                }
            });
        }
    })();
</script>
