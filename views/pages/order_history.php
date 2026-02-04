<?php
// SAFETY GATE
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
?>

<section class="order-history-section">
    <div class="container" style="padding: 100px 20px 40px;">
        <h2 class="section-title">Istoricul Comenzilor Tale</h2>
        
        <div id="order-history-container" class="order-history-list">
            <div class="loading-spinner">Se încarcă comenzile...</div>
        </div>
    </div>
</section>

<style>
    .order-history-list {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .order-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 20px;
        border-left: 5px solid #d4a373; /* Coffee color */
        transition: transform 0.2s;
    }

    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .order-id {
        font-weight: bold;
        color: #333;
    }

    .order-date {
        color: #777;
        font-size: 0.9em;
    }

    .order-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending { background: #fff3cd; color: #856404; }
    .status-preparing { background: #cce5ff; color: #004085; }
    .status-ready { background: #d4edda; color: #155724; }
    .status-completed { background: #e2e3e5; color: #383d41; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    .order-items {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.95em;
    }

    .item-name {
        color: #555;
    }

    .item-quantity {
        font-weight: bold;
        margin-right: 5px;
    }

    .order-footer {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .total-price {
        font-size: 1.2em;
        font-weight: 800;
        color: #2c3e50;
    }

    /* Page specific override for title underline */
    .order-history-section .section-title::after {
        width: 100%;
    }
    
    .loading-spinner {
        text-align: center;
        padding: 40px;
        color: #777;
    }
    
    .no-orders {
        text-align: center;
        padding: 40px;
        background: #f9f9f9;
        border-radius: 10px;
        color: #666;
    }
</style>

<script>
(function() {
    async function loadOrders() {
        const container = document.getElementById('order-history-container');
        try {
            const response = await fetch('index.php?page=order_handler&action=get_my_orders');
            const data = await response.json();

            if (!data.success) {
                container.innerHTML = `<div class="error-msg">Error: ${data.error || 'Failed to load orders'}</div>`;
                return;
            }

            const orders = data.orders;

            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="no-orders">
                        <i class="fa-solid fa-mug-hot" style="font-size: 3em; margin-bottom: 20px; color: #ddd;"></i>
                        <h3>Nu ai nicio comandă încă viața mea.</h3>
                        <p>Poftim la o cafea!</p>
                        <a href="?page=menu" class="btn" style="margin-top: 20px; display: inline-block; background: #d4a373; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Vezi Meniul</a>
                    </div>
                `;
                return;
            }

            container.innerHTML = orders.map((order, index) => {
                const orderNumber = orders.length - index;
                const date = new Date(order.created_at).toLocaleDateString('ro-RO', {
                    day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                const statusClass = `status-${order.status}`;
                const statusText = order.status.charAt(0).toUpperCase() + order.status.slice(1);

                const itemsHtml = order.items.map(item => {
                    const unitPrice = parseFloat(item.price_at_time);
                    const lineTotal = unitPrice * item.quantity;
                    
                    return `
                    <div class="order-item">
                        <span class="item-name">
                            <span class="item-quantity">${item.quantity}x</span> 
                            ${item.name}
                        </span>
                        <span class="item-price">
                            ${lineTotal.toFixed(2)} RON
                            <span style="font-size: 0.85em; color: #999; margin-left: 5px;">
                                (${item.quantity} x ${unitPrice.toFixed(2)} RON)
                            </span>
                        </span>
                    </div>
                `}).join('');

                return `
                    <div class="order-card animate-on-scroll">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Comanda #${orderNumber}</div>
                                <div class="order-date"><i class="fa-regular fa-clock"></i> ${date}</div>
                            </div>
                            <span class="order-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="order-items">
                            ${itemsHtml}
                        </div>
                        <div class="order-footer" style="display:flex; flex-direction:column; align-items:flex-end; padding-top:10px;">
                            ${parseInt(order.points_spent) > 0 ? `<div style="font-size:0.95em; color:#2e7d32; margin-bottom:2px;"><i class="fas fa-tag"></i> Loyalty Discount: <b>-${(parseInt(order.points_spent)/10).toFixed(2)} RON</b> (${order.points_spent} pts)</div>` : ''}
                            ${parseInt(order.points_earned) > 0 ? `<div style="font-size:0.9em; color:#d4af37; margin-bottom:5px;"><i class="fas fa-star"></i> Earned: +${order.points_earned} pts</div>` : ''}
                            <div class="total-price" style="font-size:1.3em;">Total: ${parseFloat(order.total_price).toFixed(2)} RON</div>
                        </div>
                    </div>
                `;
            }).join('');

        } catch (e) {
            console.error(e);
            container.innerHTML = '<div class="error-msg">A apărut o eroare la încărcarea istoricului.</div>';
        }
    }

    loadOrders();
})();
</script>
