<?php
// 1. 啟動 Session 機制核對客人安全權杖
session_start();

// 2. 安全守衛：客人如果未登入，絕對不准看商品明細，立刻強制送去登入頁
if (!isset($_SESSION['cust_id'])) {
    header("Location: login.php");
    exit();
}

// 3. 引入上層資料庫連線檔案
require_once('../conn.php');

if (!isset($conn)) {
    die("Fatal Error: Database connection variable '\$conn' is missing. Please check your db_conn.php!");
}

// 4. 讀取並防呆過濾 URL 傳遞過來的商品 ID 參數
$fid = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 🚀 智慧精算 SQL：精準抓取該商品，並一併算出該商品在當前實體原料存貨下「最多還能做幾件」
$product_query = "SELECT f.fid, f.fname, f.fdesc, f.fprice,
                         IFNULL(MIN(FLOOR(m.mqty / fm.pmqty)), 0) AS calculated_stock
                  FROM Furnitures f
                  LEFT JOIN FurnitureMaterials fm ON f.fid = fm.fid
                  LEFT JOIN Materials m ON fm.mid = m.mid
                  WHERE f.fid = ?
                  GROUP BY f.fid LIMIT 1";

$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $fid);
$stmt->execute();
$product_res = $stmt->get_result();

if (!$product_res || $product_res->num_rows == 0) {
    // 防呆：如果網址打錯了不存在的 ID，安全彈回首頁
    header("Location: index.php");
    exit();
}

$product = $product_res->fetch_assoc();
$fname = $product['fname'];
$fdesc = $product['fdesc'];
$fprice = floatval($product['fprice']);
$max_stock = intval($product['calculated_stock']);
$stmt->close();

// 🚀 動態配方 SQL：撈取該家具【真正綁定】的原料名稱與配方數量
$materials_query = "SELECT m.mname, fm.pmqty, m.munit 
                    FROM FurnitureMaterials fm
                    JOIN Materials m ON fm.mid = m.mid
                    WHERE fm.fid = ?";
$mat_stmt = $conn->prepare($materials_query);
$mat_stmt->bind_param("i", $fid);
$mat_stmt->execute();
$materials_result = $mat_stmt->get_result();

// 處理商品圖片路徑
$image_path = "../Sample Furniture Images/" . $fid . ".png";
if (!file_exists($image_path)) {
    $image_path = "../Sample Furniture Images/1.png"; // Fallback 佔位圖保護
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($fname); ?> | Details - Premium Living</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="staff/staff_style.css">
  <link rel="stylesheet" href="cust_style.css">
</head>
<body class="pl-detail-body">

<div id="pl-detail-config-metadata" 
     style="display:none;" 
     data-fid="<?php echo $fid; ?>" 
     data-fname="<?php echo htmlspecialchars($fname); ?>" 
     data-price="<?php echo $fprice; ?>" 
     data-img="<?php echo $image_path; ?>" 
     data-max-stock="<?php echo $max_stock; ?>">
</div>

<nav>
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div class="nav-links">
        <a href="index.php">Collection</a>
        <a href="cart.php">Cart <span class="cart-count" id="cartCount">0</span></a>
        <a href="dashboard.php" style="margin-left: 20px; font-weight: bold; color: #2c3e50; text-decoration: none;">👤 Hi, <?php echo htmlspecialchars($_SESSION['cust_name']); ?></a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="image-gallery">
        <div class="main-image-wrapper">
            <img src="<?php echo $image_path; ?>" id="mainImage" alt="Premium Furniture Asset">
        </div>
    </div>

    <div class="product-info">
        <div class="breadcrumb">Premium Handcrafted Collection</div>
        <h1 class="product-title"><?php echo htmlspecialchars($fname); ?></h1>
        
        <div class="stock-status">
            <?php echo ($max_stock > 0) ? '● Artisanal Stock Available' : '● Currently Out of Stock'; ?>
        </div>
        
        <div class="price-tag">$<?php echo number_format($fprice, 2); ?></div>
        
        <p class="description"><?php echo htmlspecialchars($fdesc); ?></p>

        <div class="section-title">Crafting Requirements</div>
        <div class="materials-grid">
            <?php 
            if ($materials_result && $materials_result->num_rows > 0) {
                while ($mat = $materials_result->fetch_assoc()) {
                    echo '<div class="material-item">';
                    // 1. 物料名字擺在上面，使用大字粗體 class (.mat-name)
                    echo '  <span class="mat-name">' . htmlspecialchars($mat['mname']) . '</span>';
                    // 2. 配方數量擺在下面，使用細字小字 class (.mat-qty)
                    echo '  <span class="mat-qty">' . $mat['pmqty'] . ' ' . htmlspecialchars($mat['munit']) . '</span>';
                    echo '</div>';
                }
            } else {
                echo '<p style="color:#7f8c8d; font-size:12px; grid-column:1/-1;">Custom boutique furniture formulation.</p>';
            }
            $mat_stmt->close();
            ?>
        </div>

        <div class="action-card">
            <div class="section-title" style="margin-bottom: 15px;">Selection Qty</div>
            <div class="qty-selector">
                <button type="button" class="qty-btn" id="pl-qty-minus">-</button>
                <div id="qtyValue">1</div>
                <button type="button" class="qty-btn" id="pl-qty-plus">+</button>
            </div>

            <?php if ($max_stock > 0): ?>
                <button type="button" class="btn-cart" id="pl-add-cart-submit">
                    <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Add to Collection Cart
                </button>
            <?php else: ?>
                <button type="button" class="btn-cart" style="background:#eee; color:#aaa; cursor:not-allowed; box-shadow:none; transform:none;" disabled>
                    Currently Unavailable
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="toast">✨ Item successfully added to your collection cart!</div>

<script src="cust_script.js"></script>
</body>
</html>