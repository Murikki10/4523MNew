<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['cust_id'])) {
    header("Location: login.php");
    exit();
}

require_once('../conn.php');

if (!isset($conn)) {
    die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

$catalog_query = "SELECT f.fid, f.fname, f.fdesc, f.fprice,
                         IFNULL(MIN(FLOOR(m.mqty / fm.pmqty)), 0) AS calculated_stock
                  FROM Furnitures f
                  LEFT JOIN FurnitureMaterials fm ON f.fid = fm.fid
                  LEFT JOIN Materials m ON fm.mid = m.mid
                  GROUP BY f.fid
                  ORDER BY f.fid ASC";
$catalog_result = mysqli_query($conn, $catalog_query);

$cart_count = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Living | Artisanal Home Collection</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="staff/staff_style.css">
    <link rel="stylesheet" href="cust_style.css">
</head>

<body class="pl-catalog-body">

    <nav>
        <a href="index.php" class="logo">Premium<span>Living</span></a>
        <div class="nav-links">
            <a href="index.php" style="color: #3498db; font-weight: bold;">Collection</a>
            <a href="cart.php">Cart <span class="cart-count" id="cartCount"><?php echo $cart_count; ?></span></a>
            <a href="dashboard.php"
                style="margin-left: 20px; font-weight: bold; color: #2c3e50; text-decoration: none;">👤 Hi,
                <?php echo htmlspecialchars($_SESSION['cust_name']); ?></a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="hero">
        <h1>Elevate Your Living Space</h1>
        <p>Explore our exclusive collection of sustainable and artisanal furniture, crafted for modern comfort.</p>
    </div>

    <div class="search-container">
        <span style="font-size: 20px;">🔍</span>
        <input type="text" id="pl-catalog-search" class="search-input"
            placeholder="Search for furniture (e.g. Sofa, Oak, Modern)...">
        <div style="height: 24px; width: 1px; background: #eee;"></div>
        <select id="pl-catalog-sort" class="filter-select">
            <option value="featured">Sort: Featured</option>
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
        </select>
    </div>

    <div class="container">
        <div class="product-grid" id="pl-product-grid">
            <?php
            if ($catalog_result && mysqli_num_rows($catalog_result) > 0) {
                while ($product = mysqli_fetch_assoc($catalog_result)) {
                    $fid = $product['fid'];
                    $fname = $product['fname'];
                    $fdesc = $product['fdesc'];
                    $fprice = floatval($product['fprice']);
                    $stock = intval($product['calculated_stock']);

                    $image_path = "../Sample Furniture Images/" . $fid . ".png";
                    if (!file_exists($image_path)) {
                        $image_path = "../Sample Furniture Images/1.png";
                    }

                    $is_out_of_stock = ($stock <= 0);

                    echo '<div class="product-card" 
                       data-fid="' . $fid . '" 
                       data-fname="' . htmlspecialchars(strtolower($fname)) . '" 
                       data-fdesc="' . htmlspecialchars(strtolower($fdesc)) . '" 
                       data-price="' . $fprice . '"' . ($is_out_of_stock ? ' style="opacity: 0.75;"' : '') . '>';

                    if ($is_out_of_stock) {
                        echo '<span class="badge badge-soldout">Sold Out</span>';
                    } elseif ($fid == 1 || $fid == 6) {
                        echo '<span class="badge badge-new">New Arrival</span>';
                    }

                    echo '<div class="img-container"' . ($is_out_of_stock ? ' style="filter: grayscale(0.7);"' : '') . '>';
                    echo '  <img src="' . $image_path . '" alt="' . htmlspecialchars($fname) . '">';
                    echo '</div>';

                    echo '<div class="product-info">';
                    echo '  <span class="category">Premium Collection</span>';
                    echo '  <div class="product-name">' . htmlspecialchars($fname) . '</div>';
                    echo '  <div class="product-price">$' . number_format($fprice, 2) . '</div>';

                    echo '  <div class="stock-info">';
                    if ($is_out_of_stock) {
                        echo '    <span style="color: #e74c3c; font-weight: bold;">Out of stock</span>';
                    } else {
                        echo '    &nbsp;';
                    }
                    echo '  </div>';

                    if ($is_out_of_stock) {
                        echo '  <a href="#" class="btn-action btn-out" onclick="return false;">Notify Me</a>';
                    } else {
                        echo '  <a href="detail.php?id=' . $fid . '" class="btn-action">View Details</a>';
                    }

                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div style="grid-column: 1/-1; text-align: center; color: #7f8c8d; padding: 50px;">No furniture products logged inside storage catalog yet.</div>';
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Premium Living Furniture. All rights reserved.</p>
    </footer>

    <script src="cust_script.js"></script>
</body>

</html>