<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Furniture - Premium Living Staff</title>
<style>
  /* --- Basic Layout (Sidebar Style) --- */
  * { box-sizing: border-box; }
  body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; background-color: #f4f7f6; min-height: 100vh; }
  
  .sidebar { width: 260px; background: #2c3e50; color: white; display: flex; flex-direction: column; position: fixed; height: 100%; }
  .sidebar .logo { font-size: 22px; text-align: center; font-weight: bold; padding: 30px 20px; background: #1a252f; border-bottom: 1px solid #34495e; }
  .sidebar .logo small { display: block; font-size: 14px; color: #1abc9c; margin-top: 5px; }
  .sidebar ul.navbar { list-style: none; padding: 0; margin: 0; }
  .sidebar ul.navbar li a { display: block; padding: 18px 25px; color: #ecf0f1; text-decoration: none; border-bottom: 1px solid #34495e; transition: 0.3s; }
  .sidebar ul.navbar li a:hover, .sidebar ul.navbar li a.active { background: #3498db; padding-left: 30px; }
  .sidebar ul.navbar.bottom-nav { position: absolute; bottom: 0; width: 100%; }
  
  .content { margin-left: 260px; flex: 1; padding: 40px; }

  /* --- Page Specific UI --- */
  h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }
  .form-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 40px; }
  .form-group { margin-bottom: 20px; }
  .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #34495e; }
  .form-group input, .form-group textarea, .form-group select { 
    width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; font-size: 14px; 
  }
  .form-row { display: flex; gap: 20px; }
  .form-row .form-group { flex: 1; }
  
  .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: 0.3s; }
  .btn-primary { background: #3498db; color: white; }
  .btn-primary:hover { background: #2980b9; }
  .btn-success { background: #2ecc71; color: white; }
  .btn-danger { background: #e74c3c; color: white; padding: 6px 12px; font-size: 12px; }

  /* --- Modal Styles --- */
  .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
  .modal-content { background-color: white; margin: 8% auto; padding: 25px; border-radius: 8px; width: 60%; max-width: 700px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
  .modal-header { border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
  .close-btn { cursor: pointer; font-size: 24px; color: #aaa; }
  .close-btn:hover { color: black; }

  /* --- Selected Materials Display --- */
  #selected-materials-display { border: 1px dashed #bdc3c7; padding: 15px; border-radius: 4px; min-height: 60px; display: flex; flex-wrap: wrap; gap: 10px; background: #fafafa; }
  .material-tag { background: #3498db; color: white; padding: 5px 15px; border-radius: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px; }
  .material-tag .remove-tag { cursor: pointer; font-weight: bold; color: #fff; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 8px; }

  /* --- Table Styles --- */
  .table-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  table th, table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; }
  table th { background-color: #f8f9fa; color: #2c3e50; }
  .thumbnail { width: 50px; height: 50px; border-radius: 4px; object-fit: cover; }
  .price-tag { color: #27ae60; font-weight: bold; }
</style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
  <div class="logo">Premium Living<small>Staff Portal</small></div>
  <ul class="navbar">
    <li><a href="staff_index.php">📊 Dashboard</a></li>
    <li><a href="manage_orders.php">📦 Manage Orders</a></li>
    <li><a href="manage_furniture.php" class="active">🛋️ Manage Furniture</a></li>
    <li><a href="manage_materials.php">🪵 Manage Materials</a></li>
    <li><a href="sales_report.php">📈 Sales Report</a></li>
  </ul>
  <ul class="navbar bottom-nav">
    <li><a href="staff_login.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="content">
  <div class="form-card">
    <h2>Add New Furniture</h2>
    <form action="#" method="POST" onsubmit="alert('UI Demo: Successfully Added!'); return false;">
      <div class="form-row">
        <div class="form-group">
          <label>Furniture Name</label>
          <input type="text" placeholder="e.g. Modern Sofa" required>
        </div>
        <div class="form-group">
          <label>Price ($)</label>
          <input type="number" min="0" step="0.01" placeholder="0.00" required>
        </div>
      </div>
      
      <div class="form-group">
        <label>Description</label>
        <textarea rows="3" placeholder="Enter product description..."></textarea>
      </div>

      <div class="form-group">
        <label>Material Composition</label>
        <div id="selected-materials-display">
          <span style="color: #95a5a6; font-size: 14px;">No materials selected. Click the button below to configure...</span>
        </div>
        <button type="button" class="btn btn-success" style="margin-top: 10px;" onclick="openModal()">🔍 Select & Configure Materials</button>
      </div>

      <div class="form-group">
        <label>Product Image</label>
        <input type="file" accept="image/*">
      </div>

      <button type="submit" class="btn btn-primary">➕ Add to Catalog</button>
    </form>
  </div>

  <div class="table-card">
    <h2>Product Catalog</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Name</th>
          <th>Price</th>
          <th>Materials Used</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>#001</td>
          <td><img src="../Sample Furniture Images/1.png" class="thumbnail" alt="Oak Dining Chair"></td>
          <td>Oak Dining Chair</td>
          <td class="price-tag">$450.00</td>
          <td><small>Oak Wood Plank x 2</small></td>
          <td><button class="btn btn-danger" onclick="confirm('Are you sure?')">🗑️ Delete</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div id="materialModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Configure Furniture Materials</h3>
      <span class="close-btn" onclick="closeModal()">&times;</span>
    </div>
    <div style="max-height: 400px; overflow-y: auto;">
      <table style="width: 100%;">
        <thead style="position: sticky; top: 0; background: white;">
          <tr>
            <th>Select</th>
            <th>Material Name</th>
            <th>Required Qty</th>
            <th>Unit</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox" class="mat-check" value="1" data-name="Oak Wood Plank"></td>
            <td>Oak Wood Plank</td>
            <td><input type="number" class="mat-qty" placeholder="0" min="1" style="width: 70px;"></td>
            <td>pcs</td>
          </tr>
          <tr>
            <td><input type="checkbox" class="mat-check" value="2" data-name="Steel Tube"></td>
            <td>Steel Tube</td>
            <td><input type="number" class="mat-qty" placeholder="0" min="1" style="width: 70px;"></td>
            <td>meter</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div style="margin-top: 25px; text-align: right;">
      <button type="button" class="btn" onclick="closeModal()">Cancel</button>
      <button type="button" class="btn btn-primary" onclick="confirmSelection()">Confirm</button>
    </div>
  </div>
</div>

<!-- Link to external JS file -->
<script src="./script.js"></script>

</body>
</html>