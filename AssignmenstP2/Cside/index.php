<?php
// 1. 開啟錯誤顯示（以便排查任何潛在的伺服器環境問題，確認運行穩定後可刪除）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. 啟動 Session 機制核對客人安全權杖
session_start();

// 3. 安全守衛：客人如果未登入，絕對不准偷看目錄，立刻強制送去登入頁
if (!isset($_SESSION['cust_id'])) {
    header("Location: login.php");
    exit();
}

// 4. 引入上層資料庫連線檔案 (與 staff 同層級的 conn.php)
require_once('../conn.php');

if (!isset($conn)) {
    die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

// 🚀 智慧精算 SQL 集群：
// 利用 MIN(FLOOR(Materials 存貨 / Recipe 配方量)) 精準運算出在目前原料儲備下，每款家具當前「最多還能售出多少件」
// 如果該家具根本還沒在 FurnitureMaterials 定義任何原料，則顯示 0 庫存（安全防護，防止收空單）
$catalog_query = "SELECT f.fid, f.fname, f.fdesc, f.fprice,
                         IFNULL(MIN(FLOOR(m.mqty / fm.pmqty)), 0) AS calculated_stock
                  FROM Furnitures f
                  LEFT JOIN FurnitureMaterials fm ON f.fid = fm.fid
                  LEFT JOIN Materials m ON fm.mid = m.mid
                  GROUP BY f.fid
                  ORDER BY f.fid ASC";
$catalog_result = mysqli_query($conn, $catalog_query);

// 🛒 暫存：購物車項目加總（留空供日後串接 cart.php 使用）
$cart_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Premium Living | Artisanal Home Collection</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="staff/staff_style.css">
  <link rel="stylesheet" href="cust_style.css">
</head>
<body class="pl-catalog-body">

<nav>
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div class="nav-links">
        <a href="index.php" style="color: #3498db; font-weight: bold;">Collection</a>
        <a href="cart.php">Cart <span class="cart-count" id="cartCount"><?php echo $cart_count; ?></span></a>
        <a href="dashboard.php" style="margin-left: 20px; font-weight: bold; color: #2c3e50; text-decoration: none;">👤 Hi, <?php echo htmlspecialchars($_SESSION['cust_name']); ?></a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<div class="hero">
  <h1>Elevate Your Living Space</h1>
  <p>Explore our exclusive collection of sustainable and artisanal furniture, crafted for modern comfort.</p>
</div>

<div class="search-container">
  <span style="font-size: 20px;">🔍</span>
  <input type="text" id="pl-catalog-search" class="search-input" placeholder="Search for furniture (e.g. Sofa, Oak, Modern)...">
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

            // 處理圖片路徑（自動判定本地家具圖片是否存在）
            $image_path = "../Sample Furniture Images/" . $fid . ".png";
            if (!file_exists($image_path)) {
                $image_path = "../Sample Furniture Images/1.png"; // Fallback 佔位圖保護
            }

            // 智慧判定是否因為原料用盡而斷貨
            $is_out_of_stock = ($stock <= 0);
            
            // 寫入 HTML5 data 屬性，供抽離的公用 JS 進行精準的高效免刷新搜尋排序
            echo '<div class="product-card" 
                       data-fid="' . $fid . '" 
                       data-fname="' . htmlspecialchars(strtolower($fname)) . '" 
                       data-fdesc="' . htmlspecialchars(strtolower($fdesc)) . '" 
                       data-price="' . $fprice . '"' . ($is_out_of_stock ? ' style="opacity: 0.75;"' : '') . '>';
            
            // Badges 徽章邏輯
            if ($is_out_of_stock) {
                echo '<span class="badge badge-soldout">Sold Out</span>';
            } elseif ($fid == 1 || $fid == 6) { 
                echo '<span class="badge badge-new">New Arrival</span>';
            }

            // 圖片容器區塊（斷貨會自動啟動灰階濾鏡）
            echo '<div class="img-container"' . ($is_out_of_stock ? ' style="filter: grayscale(0.7);"' : '') . '>';
            echo '  <img src="' . $image_path . '" alt="' . htmlspecialchars($fname) . '">';
            echo '</div>';

            // 高級精品卡片排版資訊
            echo '<div class="product-info">';
            echo '  <span class="category">Premium Collection</span>';
            echo '  <div class="product-name">' . htmlspecialchars($fname) . '</div>';
            echo '  <div class="product-price">$' . number_format($fprice, 2) . '</div>';
            
            // 🌐 核心修正：有貨直接乾淨留白不顯示任何文字數字，只在真正斷貨時提示紅字
            echo '  <div class="stock-info">';
            if ($is_out_of_stock) {
                echo '    <span style="color: #e74c3c; font-weight: bold;">Out of stock</span>';
            } else {
                echo '    &nbsp;'; 
            }
            echo '  </div>';

            // 動作按鈕：有貨可點擊 View Details，斷貨轉為 Notify Me
            if ($is_out_of_stock) {
                echo '  <a href="#" class="btn-action btn-out" onclick="return false;">Notify Me</a>';
            } else {
                echo '  <a href="detail.php?id=' . $fid . '" class="btn-action">View Details</a>';
            }

            echo '</div>'; // product-info 結束
            echo '</div>'; // product-card 結束
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