<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Home - Premium Living Furniture</title>
<style>
  * { box-sizing: border-box; }
  body { 
    font-family: 'Segoe UI', Arial, sans-serif; 
    margin: 0; 
    display: flex; 
    flex-direction: column;
    background-color: #f0f2f5; 
    min-height: 100vh;
  }

  .top-bar {
    background: #ffffff;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
  }

  .logo-section { font-weight: bold; font-size: 20px; color: #2c3e50; }
  .user-info { display: flex; align-items: center; gap: 20px; }
  .user-name { color: #34495e; font-weight: 500; }
  .btn-logout { 
    background: #e74c3c; color: white; text-decoration: none; 
    padding: 8px 16px; border-radius: 5px; font-size: 14px; transition: 0.3s;
  }
  .btn-logout:hover { background: #c0392b; }

  .main-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
  }

  .welcome-msg { text-align: center; margin-bottom: 40px; }
  .welcome-msg h1 { color: #2c3e50; margin-bottom: 10px; }
  .welcome-msg p { color: #7f8c8d; }

  .function-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    max-width: 900px;
    width: 100%;
  }

  .func-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #e1e4e8;
    text-align: center;
  }

  .func-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-color: #3498db;
  }

  .func-card .icon { font-size: 40px; margin-bottom: 15px; }
  .func-card h3 { margin: 10px 0; color: #2c3e50; }
  .func-card p { font-size: 14px; color: #7f8c8d; margin: 0; }

  @media (max-width: 600px) {
    .function-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="top-bar">
  <div class="logo-section">🛋️ Premium Living Staff</div>
  <div class="user-info">
    <span class="user-name">👤 Welcome, <strong>Admin1</strong></span>
    <a href="staff_login.php" class="btn-logout">Logout</a>
  </div>
</div>

<div class="main-container">
  <div class="welcome-msg">
    <h1>Centralized Management System</h1>
    <p>Please select a management function below.</p>
  </div>

  <div class="function-grid">
    <a href="manage_orders.php" class="func-card">
      <div class="icon">📦</div>
      <h3>Manage Orders</h3>
      <p>Approve/Reject orders and update delivery status.</p>
    </a>

    <a href="manage_furniture.php" class="func-card">
      <div class="icon">🛋️</div>
      <h3>Furniture Inventory</h3>
      <p>Add, update or delete furniture products.</p>
    </a>

    <a href="manage_materials.php" class="func-card">
      <div class="icon">🪵</div>
      <h3>Material Control</h3>
      <p>Monitor and restock material quantities.</p>
    </a>

    <a href="sales_report.php" class="func-card">
      <div class="icon">📊</div>
      <h3>Sales Report</h3>
      <p>View total sales amount and product statistics.</p>
    </a>
  </div>
</div>

</body>
</html>