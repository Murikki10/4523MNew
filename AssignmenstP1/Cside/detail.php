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
    }

    * { box-sizing: border-box; transition: all 0.2s ease; }
    body { font-family: 'Inter', sans-serif; margin: 0; color: #333; background: #fff; line-height: 1.6; }

    /* --- Navigation --- */
    nav { 
        padding: 20px 8%; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom: 1px solid var(--border);
        background: #fff;
    }
    .logo { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--primary); text-decoration: none; }
    .nav-links a { text-decoration: none; color: #666; margin-left: 25px; font-size: 14px; font-weight: 500; }
    .nav-links a:hover { color: var(--accent); }

    /* --- Main Container --- */
    .container { 
        max-width: 1300px; 
        margin: 60px auto; 
        padding: 0 5%; 
        display: grid;
        grid-template-columns: 1.2fr 1fr; /* 左大右小佈局 */
        gap: 80px;
    }

    /* --- Left: Image Section --- */
    .image-gallery { position: sticky; top: 100px; height: fit-content; }
    .main-image-wrapper {
        background: var(--bg-light);
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / 1;
    }
    .main-image-wrapper img { width: 100%; height: 100%; object-fit: cover; }

    /* --- Right: Product Content --- */
    .breadcrumb { font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); margin-bottom: 15px; font-weight: 600; }
    .product-title { font-family: 'Playfair Display', serif; font-size: 48px; margin: 0 0 15px; color: var(--primary); }
    .price-tag { font-size: 32px; font-weight: 600; color: var(--success); margin-bottom: 30px; }
    .description { font-size: 16px; color: #7f8c8d; margin-bottom: 40px; border-bottom: 1px solid var(--border); padding-bottom: 30px; }

    /* --- Material Requirement Section --- */
    .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 15px; color: var(--primary); }
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-bottom: 40px;
    }
    .material-item {
        background: var(--bg-light);
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        border: 1px solid var(--border);
    }
    .material-item strong { display: block; font-size: 18px; color: var(--primary); }
    .material-item span { font-size: 12px; color: var(--text-muted); }

    /* --- Order Form Card --- */
    .order-card {
        background: #fff;
        border: 1px solid var(--border);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 8px; color: #34495e; }
    .form-group input { 
        width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; 
        background: #fcfcfc; font-size: 14px;
    }
    .form-group input:focus { border-color: var(--accent); outline: none; background: #fff; }

    .btn-submit {
        width: 100%; padding: 18px; background: var(--primary); color: white; border: none;
        border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer;
        margin-top: 10px;
    }
    .btn-submit:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }

    /* Responsive */
    @media (max-width: 992px) {
        .container { grid-template-columns: 1fr; }
        .image-gallery { position: static; }
    }
</style>
</head>
<body>

<?php
// PHP logic to simulate dynamic data
$id = isset($_GET['id']) ? $_GET['id'] : '1';

// Default data
$name = "Oak Dining Chair";
$price = "450.00";
$img = "../Sample Furniture Images/1.png";
$cat = "Dining Room";
$desc = "This artisanal chair combines ergonomic design with the natural beauty of sustainable oak wood. Perfect for any modern dining space.";
$materials = [
    ["name" => "Oak Wood", "qty" => "4 pcs"],
    ["name" => "Steel Bolts", "qty" => "8 pcs"]
];

// Mock database logic
if ($id == '2') {
    $name = "Marble Coffee Table";
    $price = "1,200.00";
    $img = "../Sample Furniture Images/2.png";
    $cat = "Living Room";
    $desc = "A luxurious centerpiece featuring a polished marble top and a sleek geometric metal base.";
    $materials = [
        ["name" => "Marble Slab", "qty" => "1 pc"],
        ["name" => "Metal Frame", "qty" => "1 unit"]
    ];
} else if ($id == '3') {
    $name = "Nordic Fabric Sofa";
    $price = "3,800.00";
    $img = "../Sample Furniture Images/3.png";
    $cat = "Living Room";
    $desc = "Upholstered in premium breathable fabric, our Nordic sofa offers unparalleled comfort for your home.";
    $materials = [
        ["name" => "Fabric", "qty" => "12 meters"],
        ["name" => "Foam", "qty" => "4 blocks"],
        ["name" => "Pine Frame", "qty" => "1 unit"]
    ];
}
?>

<nav>
    <a href="index.php" class="logo">Premium Living</a>
    <div class="nav-links">
        <a href="index.php">Catalog</a>
        <a href="order_history.php">My Orders</a>
    </div>
</nav>

<div class="container">
    <div class="image-gallery">
        <div class="main-image-wrapper">
            <img src="<?php echo $img; ?>" alt="Product Image">
        </div>
    </div>

    <div class="product-details">
        <div class="breadcrumb"><?php echo $cat; ?> Collection</div>
        <h1 class="product-title"><?php echo $name; ?></h1>
        <div class="price-tag">$<?php echo $price; ?></div>
        
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

        <div class="order-card">
            <div class="section-title" style="margin-bottom: 20px;">Place Your Order</div>
            <form onsubmit="alert('Success! Order placed and materials reserved.'); return false;">
                <div class="form-row">
                    <div class="form-group">
                        <label>QUANTITY</label>
                        <input type="number" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>DELIVERY DATE</label>
                        <input type="date" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>SHIPPING ADDRESS</label>
                    <input type="text" placeholder="Enter full delivery address" required>
                </div>
                <button type="submit" class="btn-submit">Add to My Order List</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>