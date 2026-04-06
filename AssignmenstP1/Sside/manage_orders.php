<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Orders - Premium Living Staff</title>
<style>
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
  h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }

  .table-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
  table { width: 100%; border-collapse: collapse; }
  table th { text-align: left; padding: 12px 15px; background: #f8f9fa; color: #2c3e50; border-bottom: 2px solid #eee; }
  table td { padding: 15px; border-bottom: 1px solid #ecf0f1; cursor: pointer; }
  table tr:hover { background: #f1f9ff; }
  
  .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
  .status-1 { background: #e0f2fe; color: #0369a1; } 
  .status-2 { background: #dcfce7; color: #15803d; } 
  .status-3 { background: #fee2e2; color: #b91c1c; } 

  .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
  .order-card { background: white; width: 600px; margin: 5% auto; border-radius: 8px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.3); animation: fadeIn 0.3s; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
  
  .card-header { background: #2c3e50; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
  .card-body { padding: 30px; }

  .status-wrapper { display: flex; align-items: center; gap: 10px; background: #f8f9fa; padding: 10px 15px; border-radius: 8px; border: 1px solid #eee; }
  .status-wrapper select { border: none; background: transparent; font-weight: bold; color: #2c3e50; cursor: pointer; outline: none; }

  .detail-section { margin-bottom: 25px; }
  .detail-section h4 { color: #3498db; margin-bottom: 15px; border-left: 4px solid #3498db; padding-left: 10px; }
  .items-list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
  
  .item-row { background: #fff; border: 1px solid #edf2f7; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
  .item-main { display: flex; align-items: center; gap: 15px; }
  .item-img-small { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
  .item-info { flex: 1; }
  .item-name { display: block; font-weight: bold; color: #2c3e50; }
  .item-price-detail { font-size: 13px; color: #7f8c8d; }
  .item-subtotal { font-weight: bold; color: #2ecc71; width: 90px; text-align: right; }

  .item-edit-controls { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #eee; display: flex; justify-content: flex-end; gap: 10px; }
  .edit-qty { width: 65px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
  .btn-remove { background: #ff4757; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
  .btn-small-edit { font-size: 12px; padding: 6px 15px; background: #34495e; color: white; border-radius: 4px; border: none; cursor: pointer; }

  .card-footer { background: #f1f4f6; padding: 20px; text-align: right; }
  .btn { padding: 10px 25px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
  .btn-primary { background: #3498db; color: white; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo">Premium Living<small>Staff Portal</small></div>
  <ul class="navbar">
    <li><a href="staff_index.php">📊 Dashboard</a></li>
    <li><a href="manage_orders.php" class="active">📦 Manage Orders</a></li>
    <li><a href="manage_furniture.php">🛋️ Manage Furniture</a></li>
    <li><a href="manage_materials.php">🪵 Manage Materials</a></li>
    <li><a href="sales_report.php">📈 Sales Report</a></li>
  </ul>
  <ul class="navbar bottom-nav">
    <li><a href="staff_login.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="content">
  <h2>Manage Customer Orders</h2>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Order Date</th>
          <th>Customer Name</th>
          <th>Total</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr onclick="openOrderModal({
            oid: '#1001', 
            cname: 'Chan Tai Man', 
            ctel: '9123 4567', 
            caddr: '123 Nathan Rd, Mong Kok, Kowloon', 
            total: '$1350.00', 
            status: '1',
            items: [
                { fname: 'Oak Dining Chair', price: 450, qty: 2, fimg: '../Sample Furniture Images/1.png' },
                { fname: 'Small Side Table', price: 450, qty: 1, fimg: '../Sample Furniture Images/2.png' }
            ]
        })">
          <td><strong>#1001</strong></td>
          <td>2026-03-24</td>
          <td>Chan Tai Man</td>
          <td style="color:#27ae60; font-weight:bold;">$1350.00</td>
          <td><span class="badge status-1">Open</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div id="orderDetailModal" class="modal">
  <div class="order-card">
    <div class="card-header">
      <span>Order Details <strong id="modal-oid"></strong></span>
      <span style="cursor:pointer; font-size:24px;" onclick="closeOrderModal()">&times;</span>
    </div>
    <div class="card-body">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:25px;">
          <div>
              <h4 style="margin:0; color:#7f8c8d; font-size:12px; text-transform:uppercase;">Customer</h4>
              <strong id="modal-cname" style="font-size:18px;"></strong><br>
              <span id="modal-ctel" style="color:#7f8c8d; font-size:14px;"></span>
          </div>
          <div class="status-wrapper">
              <span style="font-size:12px; color:#95a5a6;">STATUS:</span>
              <select id="modal-status-select" onchange="updateStatusUI(this.value)">
                  <option value="1">🔵 OPEN</option>
                  <option value="2">🟢 APPROVED</option>
                  <option value="3">🔴 REJECTED</option>
              </select>
          </div>
      </div>
      <div class="detail-section">
          <div class="items-list-header">
              <h4>Order Items</h4>
              <button type="button" class="btn-small-edit" id="edit-items-btn" onclick="toggleEditMode()">Edit Items</button>
          </div>
          <div id="modal-items-container"></div>
      </div>
      <div class="detail-section" style="background:#f8f9fa; padding:15px; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
          <span style="font-weight:bold; color:#7f8c8d;">TOTAL AMOUNT</span>
          <span id="modal-total" style="font-size:24px; font-weight:bold; color:#27ae60;"></span>
      </div>
      <div class="detail-section">
          <h4>Shipping Address</h4>
          <p id="modal-caddr" style="font-size:14px; color:#2c3e50; background:#fff; border:1px solid #eee; padding:10px; border-radius:4px;"></p>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn" style="background:#eee;" onclick="closeOrderModal()">Cancel</button>
      <button class="btn btn-primary" onclick="alert('Order Saved!'); closeOrderModal();">Save All Changes</button>
    </div>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>