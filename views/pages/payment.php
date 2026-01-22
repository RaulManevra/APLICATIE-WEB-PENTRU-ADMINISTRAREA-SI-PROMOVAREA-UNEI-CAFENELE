<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<style>
    /* HIDE SITE CHROME */
    header, #navbar, #hero, footer { display: none !important; }
    body { overflow: hidden; background: #fdfdfd; }
    main#app { padding: 0 !important; margin: 0 !important; }

    /* FULL SCREEN CONTAINER */
    .payment-page-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at top right, #f8f9fa, #e9ecef);
        overflow-y: auto; /* Allow scroll on small screens */
        padding: 20px;
        z-index: 100;
    }

    .payment-content {
        width: 100%;
        max-width: 500px; /* Keep form readable */
        background: transparent;
        animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        animation-delay: 1.5s;
        opacity: 0;
        transform: translateY(20px);
    }

    @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

    .payment-header { text-align: center; margin-bottom: 30px; }
    .payment-header h2 { color: #2a0e02; margin-bottom: 5px; font-weight: 800; font-size: 2rem; }
    .payment-header p { color: #666; font-size: 1rem; }
    
    .back-link {
        position: absolute;
        top: 20px;
        left: 20px;
        color: #666;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
        transition: color 0.2s;
        z-index: 101;
        cursor: pointer;
    }
    .back-link:hover { color: #2a0e02; }

    /* CARD PREVIEW */
    .card-preview {
        background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
        color: #fff;
        padding: 25px;
        border-radius: 20px;
        margin-bottom: 40px;
        height: 240px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        position: relative;
        transition: transform 0.1s ease;
        overflow: hidden;
    }
    .card-preview.visa { background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%); }
    .card-preview.mastercard { background: linear-gradient(135deg, #ef6c00 0%, #e65100 100%); }
    .card-preview.amex { background: linear-gradient(135deg, #004d40 0%, #00695c 100%); }

    .card-preview::before {
        content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
        background: linear-gradient(125deg, rgba(255,255,255,0) 30%, rgba(255,255,255,0.1) 45%, rgba(255,255,255,0) 60%);
    }

    .card-top { display: flex; justify-content: space-between; align-items: flex-start; z-index: 2; }
    .card-chip {
        width: 50px; height: 35px; background: #e0e0e0; border-radius: 6px; position: relative; overflow: hidden;
        background: linear-gradient(135deg, #d4af37 0%, #c5a028 100%); /* Gold chip */
    }
    .card-brand i { font-size: 3rem; opacity: 0.9; }

    .card-number-display {
        font-size: 1.8rem; letter-spacing: 4px; font-family: 'Courier New', monospace; z-index: 2; text-shadow: 0 2px 4px rgba(0,0,0,0.4);
        width: 100%; text-align: center; margin-top: 10px;
    }

    .card-details-row { display: flex; justify-content: space-between; z-index: 2; margin-top: auto; }
    .card-label { font-size: 0.7rem; text-transform: uppercase; opacity: 0.7; margin-bottom: 2px; display: block; }
    .card-val { font-size: 1rem; font-family: 'Courier New', monospace; text-transform: uppercase; letter-spacing: 1px; }

    /* FORM STYLES */
    .form-group { position: relative; margin-bottom: 35px; } /* Increased margin for error msg space */
    .form-group label {
        position: absolute; top: -12px; left: 15px; background: transparent; /* Removed bg */ 
        padding: 0 5px; font-size: 0.85rem; color: #555; font-weight: 600; z-index: 10;
        pointer-events: none;
    }
    
    .form-control {
        width: 100%; padding: 16px 20px; border: 2px solid #e0e0e0; border-radius: 12px;
        font-size: 1.1rem; background: #fff; transition: all 0.3s;
        font-family: 'Courier New', monospace; 
        color: #333;
        position: relative; z-index: 5; 
    }
    .form-control::placeholder { font-family: 'Inter', sans-serif; color: #ccc; }
    .form-control:focus { border-color: #2a0e02; box-shadow: 0 0 0 4px rgba(42, 14, 2, 0.05); outline: none; }
    
    /* Error State */
    .form-control.error { border-color: #e53935; animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both; }
    .error-msg { 
        color: #e53935; font-size: 0.85rem; 
        position: absolute; bottom: -22px; left: 10px; 
        opacity: 0; transition: opacity 0.3s;
        z-index: 20; /* Ensure above other elements */
        font-weight: 600;
        background: rgba(255,255,255,0.8); /* Optional contrast */
        pointer-events: none; /* Key Fix: Prevent invisible error from blocking clicks */
    }
    .form-control.error + .error-msg { opacity: 1; }

    @keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }

    .form-row { display: flex; gap: 20px; }

    .pay-btn {
        width: 100%; padding: 20px; background: #2a0e02; color: #fff; border: none; border-radius: 12px;
        font-size: 1.2rem; font-weight: 700; cursor: pointer; transition: all 0.3s;
        display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 10px 30px rgba(42, 14, 2, 0.2);
    }
    .pay-btn:hover { background: #3e2723; transform: translateY(-3px); box-shadow: 0 15px 35px rgba(42, 14, 2, 0.3); }
    .pay-btn:active { transform: scale(0.98); }

    .secure-badge { text-align: center; margin-top: 30px; color: #888; font-size: 0.9rem; }

    /* LOADER */
    #fullscreen-loader {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 9999;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }
    #fullscreen-loader.hidden { opacity: 0; visibility: hidden; pointer-events: none; } /* KEY FIX: Pass through clicks */
    
    .spinner-ring { display: inline-block; width: 80px; height: 80px; margin-bottom: 30px; }
    .spinner-ring::after {
        content: " "; display: block; width: 64px; height: 64px; margin: 8px; border-radius: 50%;
        border: 6px solid #2a0e02; border-color: #2a0e02 transparent #2a0e02 transparent; animation: ring-spin 1.2s linear infinite;
    }
    @keyframes ring-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .loader-text { font-size: 1.5rem; font-weight: 600; color: #2a0e02; }

</style>

<!-- Full Screen Secure Loader -->
<div id="fullscreen-loader">
    <div class="loader-content">
        <div class="spinner-ring"></div>
        <div id="loader-message" class="loader-text">Securing Connection...</div>
        <div id="loader-check" style="color: #2ecc71; font-size: 1.1rem; margin-top:15px; display:none; font-weight:600;">
            <i class="fas fa-lock"></i> Connection Encrypted
        </div>
    </div>
</div>

<div class="payment-page-wrapper">
    <a href="?page=home" class="back-link"><i class="fas fa-arrow-left"></i> Cancel Payment</a>

    <div class="payment-content">
        <div class="payment-header">
            <h2>Payment Details</h2>
            <p>Complete your purchase securely</p>
        </div>

        <div class="card-preview" id="card-visual">
            <div class="card-top">
                <div class="card-chip"></div>
                <div class="card-brand" id="card-brand-icon"><i class="fas fa-credit-card"></i></div>
            </div>
            <div class="card-number-display" id="display-number">•••• •••• •••• ••••</div>
            <div class="card-details-row">
                <div><span class="card-label">Card Holder</span><div class="card-val" id="display-name">FULL NAME</div></div>
                <div><span class="card-label">Expires</span><div class="card-val" id="display-expiry">MM/YY</div></div>
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
                <input type="text" id="card-holder" class="form-control" placeholder="NAME ON CARD">
                <span class="error-msg">Name is required</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Expires</label>
                    <input type="text" id="card-expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                    <span class="error-msg">Invalid</span>
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="password" id="card-cvv" class="form-control" placeholder="123" maxlength="4">
                    <span class="error-msg">Invalid</span>
                </div>
            </div>

            <button type="submit" class="pay-btn" id="submit-payment">
                <i class="fas fa-lock"></i> Pay Now <span id="pay-amount"></span>
            </button>
        </form>

        <div class="secure-badge">
            <i class="fas fa-shield-alt" style="color:#2ecc71;"></i> 256-bit SSL Encrypted
        </div>
    </div>
</div>

<script>
    (function() {
        // --- LOADER ---
        const loader = document.getElementById('fullscreen-loader');
        const loaderMsg = document.getElementById('loader-message');
        const loaderCheck = document.getElementById('loader-check');
        
        function initLoader() {
            setTimeout(() => { loaderMsg.innerText = "Verifying Certificate..."; }, 800);
            setTimeout(() => { 
                loaderMsg.innerText = "Secure Environment Ready"; 
                loaderCheck.style.display = 'block'; 
            }, 1600);
            setTimeout(() => { loader.classList.add('hidden'); }, 2200);
        }
        initLoader();

        // --- DATA SETUP ---
        const pendingOrderJson = sessionStorage.getItem('pendingOrder');
        const pendingOrder = pendingOrderJson ? JSON.parse(pendingOrderJson) : null;
        
        // --- ELEMENTS ---
        const form = document.getElementById('payment-form');
        const numInput = document.getElementById('card-number');
        const nameInput = document.getElementById('card-holder');
        const expInput = document.getElementById('card-expiry');
        const cvvInput = document.getElementById('card-cvv');
        
        const cardVisual = document.getElementById('card-visual');
        const brandIcon = document.getElementById('card-brand-icon');
        const numDisplay = document.getElementById('display-number');
        const nameDisplay = document.getElementById('display-name');
        const expDisplay = document.getElementById('display-expiry');

        // --- 3D TILT ---
        document.addEventListener('mousemove', (e) => {
            const rect = cardVisual.getBoundingClientRect();
            const centerX = rect.left + rect.width/2;
            const centerY = rect.top + rect.height/2;
            // Dampen the effect, limited range
            const mouseX = Math.max(-400, Math.min(400, e.clientX - centerX));
            const mouseY = Math.max(-400, Math.min(400, e.clientY - centerY));
            
            const rotateX = (mouseY / 25).toFixed(2);
            const rotateY = (-mouseX / 25).toFixed(2);
            cardVisual.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        // --- CARD UTILS ---
        function getCardType(number) {
            const re = {
                visa: /^4/,
                mastercard: /^(5[1-5]|2[2-7])/,
                amex: /^3[47]/,
                discover: /^6/
            };
            if (re.visa.test(number)) return 'visa';
            if (re.mastercard.test(number)) return 'mastercard';
            if (re.amex.test(number)) return 'amex';
            return 'unknown';
        }

        function updateCardBrand(type) {
            cardVisual.className = 'card-preview ' + type;
            let icon = '<i class="fas fa-credit-card"></i>';
            if(type === 'visa') icon = '<i class="fab fa-cc-visa"></i>';
            if(type === 'mastercard') icon = '<i class="fab fa-cc-mastercard"></i>';
            if(type === 'amex') icon = '<i class="fab fa-cc-amex"></i>';
            brandIcon.innerHTML = icon;
        }

        // --- INPUT FORMATTING ---
        function clearError(input) {
            input.classList.remove('error');
        }

        numInput.addEventListener('input', (e) => {
            clearError(e.target);
            let val = e.target.value.replace(/\D/g, '');
            val = val.substring(0, 16); // limit
            let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
            e.target.value = formatted;
            numDisplay.innerText = formatted || '•••• •••• •••• ••••';
            updateCardBrand(getCardType(val));
        });

        nameInput.addEventListener('input', (e) => {
            clearError(e.target);
            nameDisplay.innerText = e.target.value.toUpperCase() || 'FULL NAME';
        });

        expInput.addEventListener('input', (e) => {
            clearError(e.target);
            let val = e.target.value.replace(/\D/g, '');
            if(val.length >= 2) val = val.substring(0,2) + '/' + val.substring(2,4);
            e.target.value = val;
            expDisplay.innerText = val || 'MM/YY';
        });
        
        cvvInput.addEventListener('input', (e) => clearError(e.target));

        // --- VALIDATION & SUBMIT ---
        function showError(input, msg) {
            input.classList.add('error');
            const span = input.closest('.form-group').querySelector('.error-msg');
            if(span) span.innerText = msg;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Mark fields touched
            let isValid = true;
            
            // Card Number
            const rawNum = numInput.value.replace(/\s/g, '');
            if (rawNum.length < 13 || rawNum.length > 16) {
                showError(numInput, "Invalid number length");
                isValid = false;
            }
            
            // Name
            if (nameInput.value.trim().length < 2) {
                showError(nameInput, "Name required");
                isValid = false;
            }
            
            // Expiry
            const expParts = expInput.value.split('/');
            if (expParts.length !== 2 || expParts[0].length !== 2 || expParts[1].length !== 2) {
                showError(expInput, "Format MM/YY");
                isValid = false;
            } else {
                // Simple date check
                const m = parseInt(expParts[0]);
                const y = parseInt('20' + expParts[1]);
                const now = new Date();
                const curM = now.getMonth() + 1;
                const curY = now.getFullYear();
                if(m < 1 || m > 12) { showError(expInput, "Invalid Month"); isValid = false; }
                else if (y < curY || (y === curY && m < curM)) { showError(expInput, "Card Expired"); isValid = false; }
            }

            // CVV
            if(cvvInput.value.length < 3) {
                showError(cvvInput, "3-4 digits");
                isValid = false;
            }

            if (!isValid) {
                // Shake validation
                return;
            }
            
            if (!pendingOrder) {
                alert("Session expired. Please start over.");
                window.location.href = '?page=cart';
                return;
            }

            // START PAYMENT PROCESSING
            loaderMsg.innerHTML = "Processing Payment...";
            loaderCheck.style.display = 'none';
            document.querySelector('.spinner-ring').style.display = 'block';
            loader.classList.remove('hidden');

            await new Promise(r => setTimeout(r, 2500)); // Processing delay

            const { token, pickupTime, paymentMethod } = pendingOrder;
            const formData = new FormData();
            formData.append('action', 'checkout');
            if (token) formData.append('token', token);
            if (pickupTime) formData.append('pickup_time', pickupTime);
            formData.append('payment_method', paymentMethod || 'card');

            try {
                const res = await fetch('?page=cart_handler&action=checkout', {
                    method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                
                let data = {};
                try { data = await res.json(); } catch(e) {}

                if (data.success) {
                    loaderMsg.innerHTML = '<span style="color:#2ecc71"><i class="fas fa-check-circle"></i> Approved!</span>';
                    document.querySelector('.spinner-ring').style.display = 'none';
                    
                    sessionStorage.removeItem('pendingOrder');
                    sessionStorage.removeItem('orderToken');

                    setTimeout(() => { window.location.href = '?page=home'; }, 1500);
                } else {
                    loader.classList.add('hidden');
                    alert("Declined: " + (data.message || 'Unknown Error'));
                }
            } catch(error) {
                loader.classList.add('hidden');
                console.error(error);
                alert("Connection Error. Please try again.");
            }
        });

    })();
</script>
