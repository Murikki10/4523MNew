<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product Details | Premium Living</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #2c3e50;
        --accent: #3498db;
        --success: #27ae60;
        --bg-light: #f8f9fa;
        --border: #ececec;
        --text-muted: #7f8c8d;
    }

    * { box-sizing: border-box; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    body { font-family: 'Inter', sans-serif; margin: 0; color: #333; background: #fff; line-height: 1.6; }

    /* --- Navigation --- */
    nav { 
        padding: 20px 8%; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom: 1px solid var(--border);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        position: sticky; top: 0; z-index: 1000;
    }
    .logo { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--primary); text-decoration: none; }
    .logo span { color: var(--accent); }
    .nav-links a { text-decoration: none; color: #666; margin-left: 25px; font-size: 14px; font-weight: 500; }
    .cart-count { background: var(--accent); color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px; }

    /* --- Main Layout --- */
    .container { 
        max-width: 1300px; 
        margin: 60px auto; 
        padding: 0 8%; 
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 80px;
    }

    /* --- Left: Image Section --- */
    .image-gallery { position: sticky; top: 120px; height: fit-content; }
    .main-image-wrapper {
        background: var(--bg-light);
        border-radius: 30px;
        overflow: hidden;
        aspect-ratio: 1 / 1;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
    }
    .main-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }

    /* --- Right: Product Info --- */
    .breadcrumb { font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: var(--accent); margin-bottom: 10px; font-weight: 700; }
    .product-title { font-family: 'Playfair Display', serif; font-size: 48px; margin: 0 0 10px; color: var(--primary); }
    .stock-status { display: inline-block; padding: 4px 12px; background: #e8f5e9; color: var(--success); border-radius: 50px; font-size: 12px; font-weight: 600; margin-bottom: 20px; }
    .price-tag { font-size: 36px; font-weight: 300; color: #444; margin-bottom: 30px; }
    .description { font-size: 16px; color: var(--text-muted); margin-bottom: 40px; border-bottom: 1px solid #f0f0f0; padding-bottom: 30px; }

    /* --- Materials (Crafting Requirements) --- */
    .section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px; color: var(--primary); }
    .materials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 15px; margin-bottom: 40px; }
    .material-item { background: #fff; padding: 18px; border-radius: 16px; text-align: center; border: 1px solid var(--border); box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .material-item strong { display: block; font-size: 18px; color: var(--primary); margin-bottom: 4px; }
    .material-item span { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

    /* --- Add to Cart Card --- */
    .action-card { background: #fff; border: 1px solid var(--border); padding: 40px; border-radius: 24px; box-shadow: 0 15px 50px rgba(0,0,0,0.04); }
    .qty-selector { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
    .qty-btn { width: 45px; height: 45px; border-radius: 12px; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 18px; }
    .qty-btn:hover { border-color: var(--accent); color: var(--accent); }
    #qtyValue { width: 50px; text-align: center; font-weight: 600; font-size: 18px; }

    .btn-cart {
        width: 100%; padding: 22px; background: var(--primary); color: white; border: none;
        border-radius: 16px; font-weight: 600; font-size: 17px; cursor: pointer;
        display: flex; justify-content: center; align-items: center; gap: 10px;
    }
    .btn-cart:hover { background: var(--accent); transform: translateY(-3px); box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3); }
    .btn-cart svg { width: 20px; height: 20px; fill: currentColor; }

    /* Success Message Overlay */
    #toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: var(--primary); color: white; padding: 15px 30px; border-radius: 50px; display: none; z-index: 2000; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }

    @media (max-width: 992px) {
        .container { grid-template-columns: 1fr; }
        .image-gallery { position: static; }
    }
</style>
</head>
<body>

<?php
// PHP logic 
$id = isset($_GET['id']) ? $_GET['id'] : '1';
$name = "Oak Dining Chair"; $price = 450.00; $img = "../Sample Furniture Images/1.png"; $cat = "Dining Room";
$desc = "Handcrafted from solid European Oak, this chair is a testament to timeless design and structural integrity. Each piece is hand-finished with organic oils.";
$materials = [["name" => "Solid Oak", "qty" => "4 units"], ["name" => "Steel Bolts", "qty" => "8 pcs"]];

if ($id == '2') {
    $name = "Marble Coffee Table"; $price = 1200.00; $img = "../Sample Furniture Images/2.png"; $cat = "Living Room";
    $desc = "A minimalist masterpiece featuring a Carrara marble slab atop a precision-welded steel frame.";
    $materials = [["name" => "Marble Slab", "qty" => "1 pc"], ["name" => "Metal Base", "qty" => "1 unit"]];
}
?>

<nav>
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div class="nav-links">
        <a href="index.php">Collection</a>
        <a href="cart.php">Cart <span class="cart-count" id="cartCount">0</span></a>
        <a href="dashboard.php">Account</a>
        <a href="logout.php" style="color: #e74c3c; margin-left: 20px; opacity: 0.8;">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="image-gallery">
        <div class="main-image-wrapper">
            <img src="<?php echo $img; ?>" id="mainImage">
        </div>
    </div>

    <div class="product-info">
        <div class="breadcrumb"><?php echo $cat; ?> Collection</div>
        <h1 class="product-title"><?php echo $name; ?></h1>
        <div class="stock-status">● Artisanal Stock Available</div>
        <div class="price-tag">$<?php echo number_format($price, 2); ?></div>
        
        <p class="description"><?php echo $desc; ?></p>

        <div class="section-title">Crafting Requirements</div>
        <div class="materials-grid">
            <?php foreach ($materials as $m): ?>
            <div class="material-item">
                <strong><?php echo $m['qty']; ?></strong>
                <span><?php echo $m['name']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="action-card">
            <div class="section-title" style="margin-bottom: 15px;">Selection</div>
            <div class="qty-selector">
                <button class="qty-btn" onclick="updateQty(-1)">-</button>
                <div id="qtyValue">1</div>
                <button class="qty-btn" onclick="updateQty(1)">+</button>
            </div>

            <button class="btn-cart" onclick="addToCart()">
                <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                Add to Cart
            </button>
        </div>
    </div>
</div>

<div id="toast">Item added to your collection!</div>

<script>
    let currentQty = 1;

    function updateQty(change) {
        currentQty = Math.max(1, currentQty + change);
        document.getElementById('qtyValue').innerText = currentQty;
    }

    function addToCart() {
        const item = {
            id: "<?php echo $id; ?>",
            name: "<?php echo $name; ?>",
            price: <?php echo $price; ?>,
            qty: currentQty,
            img: "<?php echo $img; ?>"
        };

        let cart = JSON.parse(localStorage.getItem('furniture_cart')) || [];
        const index = cart.findIndex(i => i.id === item.id);
        if (index > -1) {
            cart[index].qty += item.qty;
        } else {
            cart.push(item);
        }

        localStorage.setItem('furniture_cart', JSON.stringify(cart));
        updateCartBadge();
        
        // Show Toast
        const toast = document.getElementById('toast');
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 3000);
    }

    function updateCartBadge() {
        let cart = JSON.parse(localStorage.getItem('furniture_cart')) || [];
        document.getElementById('cartCount').innerText = cart.length;
    }

    updateCartBadge();
</script>

</body>
</html>