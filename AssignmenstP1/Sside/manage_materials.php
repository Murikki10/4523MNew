<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Material Control - Premium Living Staff</title>
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

  /* --- Material Page Specific UI --- */
  .form-card, .table-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
  
  .form-row { display: flex; gap: 15px; align-items: flex-end; }
  .form-group { flex: 1; }
  .form-group label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 14px; color: #34495e; }
  .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; }

  .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: 0.3s; }
  .btn-primary { background: #3498db; color: white; }
  .btn-warning { background: #f39c12; color: white; font-size: 12px; padding: 5px 10px; }
  
  /* Status Indicator */
  .low-stock { color: #e74c3c; font-weight: bold; }
  .normal-stock { color: #27ae60; }

  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  table th, table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
  table th { background: #f8f9fa; color: #7f8c8d; font-size: 12px; text-transform: uppercase; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo">Premium Living<small>Staff Portal</small></div>
  <ul class="navbar">
    <li><a href="staff_index.php">📊 Dashboard</a></li>
    <li><a href="manage_orders.php">📦 Manage Orders</a></li>
    <li><a href="manage_furniture.php">🛋️ Manage Furniture</a></li>
    <li><a href="manage_materials.php" class="active">🪵 Manage Materials</a></li>
    <li><a href="sales_report.php">📈 Sales Report</a></li>
  </ul>
</div>

<div class="content">
  <h2>Material Inventory Control</h2>

  <!-- Add New Material Form -->
  <div class="form-card">
    <h4 style="margin-top:0;">Register New Material</h4>
    <form onsubmit="alert('UI Demo: Material Added!'); return false;">
      <div class="form-row">
        <div class="form-group">
          <label>Material Name</label>
          <input type="text" placeholder="e.g. High Density Foam" required>
        </div>
        <div class="form-group" style="flex: 0.5;">
          <label>Initial Quantity</label>
          <input type="number" value="0" min="0">
        </div>
        <div class="form-group" style="flex: 0.5;">
          <label>Unit</label>
          <select>
            <option>pcs</option>
            <option>meter</option>
            <option>block</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">➕ Register</button>
      </div>
    </form>
  </div>

  <!-- Materials List Table -->
  <div class="table-card">
    <h4 style="margin-top:0;">Current Stock Levels</h4>
    <table>
      <thead>
        <tr>
          <th>MID</th>
          <th>Material Name</th>
          <th>Physical Quantity</th>
          <th>Unit</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>#M001</td>
          <td><strong>Oak Wood Plank</strong></td>
          <td>500</td>
          <td>pcs</td>
          <td><span class="normal-stock">● Healthy</span></td>
          <td><button class="btn btn-warning">Restock</button></td>
        </tr>
        <tr>
          <td>#M004</td>
          <td><strong>High Density Foam</strong></td>
          <td class="low-stock">12</td>
          <td>block</td>
          <td><span class="low-stock">⚠️ Low Stock</span></td>
          <td><button class="btn btn-warning">Restock</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>