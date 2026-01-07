<?php
// views/pages/cart.php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>
<link rel="stylesheet" href="assets/css/cart.css?v=<?php echo time(); ?>">

<section class="cart-section">
    <div class="cart-container">
        <h2 class="section-title">Your Cart</h2>
        
        <div id="cart-content" class="cart-content">
            <div class="loading-cart">Loading cart...</div>
        </div>
    </div>
</section>

<script>
    (function() {
        const cartContent = document.getElementById('cart-content');

        async function loadCart() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_cart');
                
                // Assuming we can use the same endpoint but routed via main.js or direct fetch if we knew the path.
                // Since we are inside the view loaded by main.js, we can likely use a relative path or the known controller path if routed.
                // However, our system uses specific controller handlers.
                // We added ?page=cart_handler in index.php to route to CartController.
                
                const res = await fetch('?page=cart_handler&action=get_cart', { 
                    method: 'POST', 
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("JSON Parse Error:", e, "Response:", text);
                    cartContent.innerHTML = `<p class="error-msg">Server Error: Invalid JSON response. <br><small>${text.substring(0, 200)}</small></p>`;
                    return;
                }

                if (data.success) {
                    // Logic fix: sendSuccess merges data into the top level object.
                    // So data.items and data.total are direct properties of 'data'.
                    // data.data is undefined unless we wrapped it in 'data' key explicitly.
                    // Based on CartController usage: sendSuccess(['items' => ..., 'total' => ...])
                    // Correct usage is: renderCart(data);
                    renderCart(data); 
                } else {
                    cartContent.innerHTML = `<p class="error-msg">${data.message}</p>`;
                }
            } catch (err) {
                console.error(err);
                cartContent.innerHTML = `<p class="error-msg">Failed to load cart: ${err.message}</p>`;
            }
        }

        function renderCart(data) {
            const { items, total } = data;

            if (!items || items.length === 0) {
                cartContent.innerHTML = `
                    <div class="empty-cart">
                        <i class="fa-solid fa-cart-arrow-down"></i>
                        <p>Your cart is empty.</p>
                        <a href="?page=menu" class="nav-link btn-secondary" data-page="menu">Browse Menu</a>
                    </div>
                `;
                return;
            }

            let html = `
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            items.forEach(item => {
                html += `
                    <tr>
                        <td class="cart-product-info">
                            <img src="${item.image_path}" alt="${item.name}" class="cart-thumb">
                            <span>${item.name}</span>
                        </td>
                        <td>${parseFloat(item.price).toFixed(2)} RON</td>
                        <td>
                            <div class="qty-control">
                                <button class="qty-btn minus" data-id="${item.id}">-</button>
                                <span class="qty-val">${item.quantity}</span>
                                <button class="qty-btn plus" data-id="${item.id}">+</button>
                            </div>
                        </td>
                        <td>${parseFloat(item.subtotal).toFixed(2)} RON</td>
                        <td>
                            <button class="remove-btn" data-id="${item.id}"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span class="total-price">${parseFloat(total).toFixed(2)} RON</span>
                    </div>
                    <div class="cart-actions">
                        <a href="?page=menu" class="nav-link btn-secondary" data-page="menu">Continue Shopping</a>
                        <button class="btn-primary checkout-btn">Checkout</button>
                    </div>
                </div>
            `;

            cartContent.innerHTML = html;
            attachCartListeners();
        }



        function attachCartListeners() {
            cartContent.querySelectorAll('.qty-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const id = e.target.closest('button').dataset.id;
                    const isPlus = e.target.classList.contains('plus');
                    const currentQty = parseInt(e.target.parentElement.querySelector('.qty-val').innerText);
                    const newQty = isPlus ? currentQty + 1 : currentQty - 1;

                    if (newQty < 1) return; 

                    await updateCartItem(id, newQty);
                });
            });

            cartContent.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const id = e.target.closest('button').dataset.id;
                    await removeCartItem(id);
                });
            });
            
             cartContent.querySelectorAll('.checkout-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                   document.getElementById('checkout-modal').style.display = 'flex';
                });
            });
        }

        async function updateCartItem(id, qty) {
            const formData = new FormData();
            formData.append('action', 'update_quantity');
            formData.append('product_id', id);
            formData.append('quantity', qty);

            const res = await fetch('?page=cart_handler&action=update_quantity', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                loadCart(); 
                updateCartBadge(data.data.total_items);
            }
        }

        async function removeCartItem(id) {
             const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', id);
            
            const res = await fetch('?page=cart_handler&action=remove', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                loadCart();
                updateCartBadge(data.data.total_items);
            }
        }
        
        function updateCartBadge(count) {
             const badge = document.querySelector('.cart-badge'); 
             if(badge) {
                 badge.innerText = count;
                 badge.style.display = count > 0 ? 'flex' : 'none';
             }
        }
        
        // Modal Logic
        const modal = document.getElementById('checkout-modal');
        const closeBtn = document.querySelector('.close-modal');
        const checkoutForm = document.getElementById('checkout-form');

        closeBtn.onclick = () => modal.style.display = 'none';
        window.onclick = (event) => {
            if (event.target == modal) modal.style.display = 'none';
        }

        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const pickupTime = document.getElementById('pickup-time').value;
            
            if(!pickupTime) {
                alert("Please select a pickup time.");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'checkout');
            formData.append('pickup_time', pickupTime);
            
            try {
                const res = await fetch('?page=cart_handler&action=checkout', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                // Robust error handling for non-JSON responses
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                     console.error("Server Error:", text);
                     alert("Server error during checkout. See console.");
                     return;
                }

                if (data.success) {
                    alert("Order Placed Successfully! Order ID: " + data.data.order_id);
                    location.reload(); // Refresh to empty cart
                } else {
                    alert("Checkout Failed: " + data.message);
                }
            } catch (err) {
                console.error(err);
                alert("Network error.");
            }
        });

        loadCart();
    })();
</script>

<!-- Checkout Modal -->
<div id="checkout-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Checkout - Pickup Time</h2>
        <p>Please select when you would like to pick up your order.</p>
        <form id="checkout-form">
            <div class="form-group">
                <label for="pickup-time">Pickup Time:</label>
                <!-- Set min timestamp via JS later if needed, current implementation allows all but backend validates -->
                <input type="datetime-local" id="pickup-time" name="pickup_time" required class="form-control">
                <small>Must be at least 15 minutes from now.</small>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Confirm Order</button>
        </form>
    </div>
</div>

<style>
/* Modal Styles */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.6); 
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fff;
    margin: auto;
    padding: 30px;
    border: 1px solid #888;
    width: 90%;
    max-width: 500px;
    border-radius: 12px;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    top: 50%;
    transform: translateY(-50%);
}

.close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close-modal:hover,
.close-modal:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.modal-content h2 {
    color: #2a0e02;
    margin-bottom: 1rem;
}
</style>
