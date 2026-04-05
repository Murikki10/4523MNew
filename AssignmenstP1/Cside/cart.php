<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart | Premium Living</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f8f9fa; --border: #eee; }
        body { font-family: 'Inter', sans-serif; background: #fff; margin: 0; padding-bottom: 50px; }
        nav { padding: 20px 8%; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 5%; }
        h1 { font-family: 'Playfair Display', serif; font-size: 32px; color: var(--primary); }
        
        /* Cart List Style */
        .cart-item { display: flex; align-items: center; padding: 20px 0; border-bottom: 1px solid var(--border); }
        .item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; margin-right: 20px; }
        .item-info { flex: 1; }
        .item-qty { display: flex; align-items: center; gap: 10px; }
        .qty-btn { border: 1px solid #ddd; background: #fff; padding: 5px 10px; cursor: pointer; border-radius: 4px; }
        
        /* Checkout Card */
        .checkout-section { margin-top: 40px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }
        .form-card { background: var(--bg); padding: 30px; border-radius: 20px; }
        .summary-card { border: 1px solid var(--border); padding: 30px; border-radius: 20px; height: fit-content; }
        
        input, textarea { width: 100%; padding: 12px; margin-top: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-pay { width: 100%; padding: 18px; background: var(--primary); color: #fff; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 16px; }
        .btn-pay:hover { background: var(--accent); }
    </style>
</head>
<body>

<nav>
    <a href="index.php" style="text-decoration:none; color:var(--primary); font-weight:bold;">Premium<span>Living</span></a>
    <a href="index.php" style="font-size:14px; color:var(--accent);">Back to Catalog</a>
</nav>

<div class="container">
    <h1>Your Shopping Cart</h1>
    
    <div id="cartList">
        </div>

    <div class="checkout-section">
        <div class="form-card">
            <h3 style="margin-top:0;">Delivery Details</h3>
            <label>DELIVERY DATE</label>
            <input type="date" id="finalDate" required>
            
            <label>SHIPPING ADDRESS</label>
            <input type="text" value="Flat A, 12/F, Nathan Luxury Tower, TST" required>
            
            <label>ORDER NOTE</label>
            <textarea id="finalNote" rows="3" placeholder="Notes for all items..."></textarea>
        </div>

        <div class="summary-card">
            <h3 style="margin-top:0;">Order Summary</h3>
            <div id="summaryDetails">
                </div>
            <button class="btn-pay" onclick="processCheckout()">Confirm Order & Pay</button>
        </div>
    </div>
</div>

<script>
    let cart = JSON.parse(localStorage.getItem('furniture_cart')) || [];

    function renderCart() {
        const list = document.getElementById('cartList');
        const summary = document.getElementById('summaryDetails');
        if (cart.length === 0) {
            list.innerHTML = "<p style='color:#999;'>Your cart is empty.</p>";
            return;
        }

        let html = '';
        let subtotal = 0;
        cart.forEach((item, index) => {
            subtotal += item.price * item.qty;
            html += `
                <div class="cart-item">
                    <img src="${item.img}" class="item-img">
                    <div class="item-info">
                        <div style="font-weight:600;">${item.name}</div>
                        <div style="font-size:13px; color:#7f8c8d;">$${item.price.toFixed(2)}</div>
                    </div>
                    <div class="item-qty">
                        <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                        <span>${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                    <div style="margin-left:30px; font-weight:600;">$${(item.price * item.qty).toFixed(2)}</div>
                </div>
            `;
        });
        list.innerHTML = html;
        
        const delivery = 150;
        summary.innerHTML = `
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Subtotal</span><span>$${subtotal.toFixed(2)}</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Delivery</span><span>$${delivery.toFixed(2)}</span></div>
            <hr style="border:0; border-top:1px dashed #eee;">
            <div style="display:flex; justify-content:space-between; font-size:20px; font-weight:bold; margin:15px 0;"><span>Total</span><span>$${(subtotal + delivery).toFixed(2)}</span></div>
        `;
    }

    function updateQty(index, change) {
        cart[index].qty += change;
        if (cart[index].qty <= 0) cart.splice(index, 1);
        localStorage.setItem('furniture_cart', JSON.stringify(cart));
        renderCart();
    }

    function processCheckout() {
        if (cart.length === 0) return alert("Cart is empty!");
        const date = document.getElementById('finalDate').value;
        if (!date) return alert("Please select a delivery date.");

        alert("Order Success! All materials have been allocated from inventory.");
        localStorage.removeItem('furniture_cart');
        window.location.href = 'dashboard.php';
    }

    renderCart();
</script>

</body>
</html>