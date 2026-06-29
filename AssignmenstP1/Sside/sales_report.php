<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Report - Premium Living Staff</title>
<style>
  /* --- Shared Layout (Consistent with your current progress) --- */
  * { box-sizing: border-box; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; background-color: #f4f7f6; min-height: 100vh; }
  
  .sidebar { width: 260px; background: #2c3e50; color: white; display: flex; flex-direction: column; position: fixed; height: 100%; }
  .sidebar .logo { font-size: 22px; text-align: center; font-weight: bold; padding: 30px 20px; background: #1a252f; border-bottom: 1px solid #34495e; }
  .sidebar .logo small { display: block; font-size: 14px; color: #1abc9c; margin-top: 5px; }
  .sidebar ul.navbar { list-style: none; padding: 0; margin: 0; }
  .sidebar ul.navbar li a { display: block; padding: 18px 25px; color: #ecf0f1; text-decoration: none; border-bottom: 1px solid #34495e; transition: 0.3s; }
  .sidebar ul.navbar li a:hover, .sidebar ul.navbar li a.active { background: #3498db; padding-left: 30px; }
  
  .content { margin-left: 260px; flex: 1; padding: 40px; }
  h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }

  /* --- Report Specific UI --- */
  .stats-grid { display: flex; gap: 20px; margin-bottom: 30px; }
  .stat-card { background: white; padding: 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #3498db; }
  .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
  .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: bold; color: #2c3e50; }

  .report-table-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
  
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  table th, table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
  table th { background: #f8f9fa; color: #2c3e50; font-weight: bold; font-size: 13px; }
  
  .prod-img-mini { width: 60px; height: 60px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd; }
  .revenue { color: #27ae60; font-weight: bold; font-size: 16px; }
  .total-row { background: #f1f9ff; font-weight: bold; }

  /* Print Styles */
  @media print {
    .sidebar { display: none; }
    .content { margin-left: 0; padding: 0; }
    .btn-print { display: none; }
  }

  .btn-print { background: #34495e; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; float: right; margin-bottom: 20px; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo">Premium Living<small>Staff Portal</small></div>
  <ul class="navbar">
    <li><a href="staff_index.php">📊 Dashboard</a></li>
    <li><a href="manage_orders.php">📦 Manage Orders</a></li>
    <li><a href="manage_furniture.php">🛋️ Manage Furniture</a></li>
    <li><a href="manage_materials.php">🪵 Manage Materials</a></li>
    <li><a href="sales_report.php" class="active">📈 Sales Report</a></li>
  </ul>
</div>

<div class="content">
  <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>
  <h2>Sales Statistics Report</h2>

  <!-- Quick Overview Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <h3>Total Revenue</h3>
      <p>$12,450.00</p>
    </div>
    <div class="stat-card">
      <h3>Items Sold</h3>
      <p>42 Units</p>
    </div>
    <div class="stat-card" style="border-top-color: #2ecc71;">
      <h3>Top Product</h3>
      <p>Dining Chair</p>
    </div>
  </div>

  <!-- Detailed Report Table -->
  <div class="report-table-card">
    <h4 style="margin-top:0; color:#34495e;">Product Sales Breakdown</h4>
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Furniture Image</th>
          <th>Furniture Name</th>
          <th>Total Sold (Qty)</th>
          <th>Total Sales Amount ($)</th>
        </tr>
      </thead>
      <tbody>
        <!-- Data Example 1 -->
        <tr>
          <td>#1001</td>
          <td><img src="../Sample Furniture Images/1.png" class="prod-img-mini"></td>
          <td><strong>Oak Dining Chair</strong></td>
          <td>12</td>
          <td class="revenue">$5,400.00</td>
        </tr>
        <!-- Data Example 2 -->
        <tr>
          <td>#1002</td>
          <td><img src="../Sample Furniture Images/2.png" class="prod-img-mini"></td>
          <td><strong>Large Dining Table</strong></td>
          <td>2</td>
          <td class="revenue">$5,000.00</td>
        </tr>
        <!-- Data Example 3 -->
        <tr>
          <td>#1005</td>
          <td><img src="../Sample Furniture Images/3.png" class="prod-img-mini"></td>
          <td><strong>3-Seater Fabric Sofa</strong></td>
          <td>1</td>
          <td class="revenue">$3,800.00</td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="total-row">
          <td colspan="3" style="text-align: right;">GRAND TOTAL:</td>
          <td>15 Units</td>
          <td class="revenue" style="font-size: 20px;">$14,200.00</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

</body>
</html>