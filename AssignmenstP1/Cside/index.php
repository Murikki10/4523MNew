<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Premium Living | Artisanal Home Collection</title>
<!-- Google Fonts for a Premium Look -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
  :root {
    --primary-color: #2c3e50;
    --accent-color: #3498db;
    --text-muted: #7f8c8d;
    --bg-light: #f8f9fa;
    --danger: #e74c3c;
    --success: #27ae60;
  }

  * { box-sizing: border-box; transition: all 0.3s ease; }
  body { font-family: 'Inter', sans-serif; margin: 0; background-color: #fff; color: #333; line-height: 1.6; }

  /* --- Navigation --- */
  nav { 
    background: rgba(255, 255, 255, 0.95); 
    padding: 15px 8%; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    border-bottom: 1px solid #eee; 
    position: sticky; 
    top: 0; 
    z-index: 1000;
    backdrop-filter: blur(10px);
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: bold; color: var(--primary-color); letter-spacing: -1px; }
  .logo span { color: var(--accent-color); }
  .nav-links a { text-decoration: none; color: #555; margin-left: 30px; font-size: 14px; font-weight: 500; }
  .nav-links a:hover { color: var(--accent-color); }

  /* --- Hero Section --- */
  .hero { 
    padding: 100px 8% 60px; 
    text-align: center; 
    background: radial-gradient(circle at top right, #fdfbfb 0%, #ebedee 100%); 
  }
  .hero h1 { font-family: 'Playfair Display', serif; font-size: 48px; margin-bottom: 15px; color: var(--primary-color); }
  .hero p { max-width: 600px; margin: 0 auto 30px; color: var(--text-muted); font-size: 18px; }

  /* --- Search & Filter Bar --- */
  .search-container { 
    max-width: 1200px; 
    margin: -35px auto 40px; 
    background: white; 
    padding: 20px 30px; 
    border-radius: 50px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    display: flex;
    gap: 20px;
    align-items: center;
  }
  .search-input { flex: 1; border: none; outline: none; font-size: 16px; padding: 5px; }
  .filter-select { border: none; outline: none; font-weight: 600; color: var(--primary-color); cursor: pointer; }

  /* --- Product Grid --- */
  .container { max-width: 1400px; margin: 0 auto; padding: 0 8% 80px; }
  .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 40px; }

  .product-card { 
    background: white; 
    border-radius: 12px; 
    overflow: hidden; 
    border: 1px solid #f0f0f0; 
    position: relative;
  }
  .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
  
  .img-container { height: 320px; overflow: hidden; background: #f4f4f4; position: relative; }
  .img-container img { width: 100%; height: 100%; object-fit: cover; }
  .product-card:hover .img-container img { transform: scale(1.08); }

  /* Badges */
  .badge { position: absolute; top: 15px; left: 15px; padding: 6px 14px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; z-index: 10; }
  .badge-new { background: var(--accent-color); color: white; }
  .badge-soldout { background: var(--danger); color: white; right: 15px; left: auto; }

  .product-info { padding: 25px; text-align: center; }
  .category { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; display: block; }
  .product-name { font-family: 'Playfair Display', serif; font-size: 22px; margin-bottom: 10px; color: var(--primary-color); }
  .product-price { font-size: 18px; color: var(--success); font-weight: 600; margin-bottom: 15px; }
  
  .stock-info { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 5px; }
  .stock-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); }

  .btn-action { 
    display: inline-block; 
    width: 100%; 
    padding: 15px; 
    background: var(--primary-color); 
    color: white; 
    text-decoration: none; 
    border-radius: 8px; 
    font-weight: 600;
    letter-spacing: 1px;
  }
  .btn-action:hover { background: var(--accent-color); }
  .btn-out { background: #eee; color: #aaa; cursor: not-allowed; }

  /* --- Footer --- */
  footer { background: #f8f9fa; padding: 60px 8%; border-top: 1px solid #eee; text-align: center; color: var(--text-muted); font-size: 14px; }
</style>
</head>
<body>

<nav>
  <div class="logo">Premium<span>Living</span></div>
  <div class="nav-links">
    <a href="index.html">Collection</a>
    <a href="order_history.html">Order History</a>
    <a href="profile.html">My Account</a>
    <a href="login.html">Logout</a>
  </div>
</nav>

<div class="hero">
  <h1>Elevate Your Living Space</h1>
  <p>Explore our exclusive collection of sustainable and artisanal furniture, crafted for modern comfort.</p>
</div>

<!-- Dynamic Search Bar -->
<div class="search-container">
  <span style="font-size: 20px;">🔍</span>
  <input type="text" class="search-input" placeholder="Search for furniture (e.g. Sofa, Oak, Modern)...">
  <div style="height: 24px; width: 1px; background: #eee;"></div>
  <select class="filter-select" onchange="alert('Sorting changed!')">
    <option>Sort: Featured</option>
    <option>Price: Low to High</option>
    <option>Price: High to Low</option>
  </select>
</div>

<div class="container">
  <div class="product-grid">
    <!-- Item 1 -->
    <div class="product-card">
      <span class="badge badge-new">New Arrival</span>
      <div class="img-container">
        <img src="../Sample Furniture Images/1.png" alt="Furniture">
      </div>
      <div class="product-info">
        <span class="category">Dining</span>
        <div class="product-name">Oak Dining Chair</div>
        <div class="product-price">$450.00</div>
        <div class="stock-info">
          <div class="stock-dot"></div> 12 units available
        </div>
        <a href="detail.php?id=1" class="btn-action">View Details</a>
      </div>
    </div>

    <!-- Item 2: Sold Out Example -->
    <div class="product-card" style="opacity: 0.8;">
      <span class="badge badge-soldout">Sold Out</span>
      <div class="img-container" style="filter: grayscale(0.5);">
        <img src="../Sample Furniture Images/2.png" alt="Furniture">
      </div>
      <div class="product-info">
        <span class="category">Living Room</span>
        <div class="product-name">Marble Coffee Table</div>
        <div class="product-price">$1,200.00</div>
        <div class="stock-info" style="color: var(--danger);">
          Out of stock
        </div>
        <a href="#" class="btn-action btn-out" onclick="return false;">Notify Me</a>
      </div>
    </div>

    <!-- Item 3 -->
    <div class="product-card">
      <div class="img-container">
        <img src="../Sample Furniture Images/3.png" alt="Furniture">
      </div>
      <div class="product-info">
        <span class="category">Bedroom</span>
        <div class="product-name">Minimalist Sofa Bed</div>
        <div class="product-price">$3,800.00</div>
        <div class="stock-info">
          <div class="stock-dot" style="background: orange;"></div> 3 units left - Low Stock
        </div>
        <a href="detail.php?id=3" class="btn-action">View Details</a>
      </div>
    </div>
  </div>
</div>

<footer>
  <p>&copy; 2026 Premium Living Furniture. All rights reserved.</p>
</footer>

</body>
</html>