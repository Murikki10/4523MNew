<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Premium Living</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
  :root {
    --primary-color: #2c3e50;
    --accent-color: #3498db;
    --text-muted: #7f8c8d;
    --bg-light: #f8f9fa;
    --danger: #e74c3c;
    --success: #27ae60;
    --border: #eee;
  }

  * { box-sizing: border-box; transition: all 0.3s ease; }
  body { font-family: 'Inter', sans-serif; margin: 0; background-color: #fff; color: #333; line-height: 1.6; }

  /* --- 統一後的 Navigation (配合新風格) --- */
  nav { 
    padding: 20px 8%; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    border-bottom: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    position: sticky; 
    top: 0; 
    z-index: 1000;
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--primary-color); text-decoration: none; }
  .logo span { color: var(--accent-color); }
  .nav-links { display: flex; align-items: center; }
  .nav-links a { text-decoration: none; color: #666; margin-left: 25px; font-size: 14px; font-weight: 500; }
  .nav-links a:hover { color: var(--accent-color); }
  .nav-links a.logout-btn { color: var(--danger); }
  .cart-count { background: var(--accent-color); color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px; }

  /* --- 內容容器佈局 --- */
  .container { 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 40px 8%; 
  }

  .dashboard-header { margin-bottom: 30px; }
  .dashboard-header h1 { font-family: 'Playfair Display', serif; font-size: 32px; color: var(--primary-color); margin: 0; }
  
  .function-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
  .stat-card { 
    background: white; padding: 30px; border-radius: 16px; border: 1px solid var(--border);
    text-align: left; cursor: pointer; position: relative; text-decoration: none; color: inherit; display: block;
  }
  .stat-card:hover { transform: translateY(-5px); border-color: var(--accent-color); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
  .stat-card h3 { margin: 0; font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
  .stat-card p { margin: 10px 0 0; font-size: 18px; font-weight: 600; color: var(--primary-color); }
  .stat-card .icon { position: absolute; right: 20px; top: 20px; font-size: 22px; opacity: 0.15; }
  .edit-tag { font-size: 11px; color: var(--accent-color); font-weight: 600; display: block; margin-top: 5px; }

  .section-title { display: flex; align-items: center; margin: 50px 0 25px; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: var(--text-muted); }
  .section-title::after { content: ""; flex: 1; height: 1px; background: var(--border); margin-left: 20px; }

  .order-container { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
  table { width: 100%; border-collapse: collapse; }
  th { background: var(--bg-light); padding: 18px; text-align: left; font-size: 12px; color: var(--text-muted); }
  td { padding: 20px 18px; border-bottom: 1px solid var(--border); font-size: 14px; }
  .status-pill { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #fff8e1; color: #f57f17; }
  .btn-manage { background: white; border: 1px solid var(--accent-color); color: var(--accent-color); padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
  .btn-manage:hover { background: var(--accent-color); color: white; }

  /* --- Modal 樣式 --- */
  .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(4px); }
  .modal-box { background: white; width: 95%; max-width: 700px; max-height: 90vh; border-radius: 24px; position: relative; animation: modalFade 0.3s ease; display: flex; flex-direction: column; overflow: hidden; }
  @keyframes modalFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  .modal-header-receipt { background: var(--primary-color); color: white; padding: 40px 30px; text-align: center; position: relative; }
  .close-btn { position: absolute; top: 20px; right: 20px; font-size: 24px; cursor: pointer; color: rgba(255,255,255,0.5); border: none; background: none; }
  .modal-body { padding: 30px; overflow-y: auto; flex: 1; }
  .receipt-section { margin-bottom: 25px; }
  .receipt-section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px; letter-spacing: 1.5px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  .info-item label { display: block; font-size: 10px; color: #999; text-transform: uppercase; margin-bottom: 4px; }
  .info-item span { font-size: 14px; font-weight: 600; color: var(--primary-color); }
  .item-row { display: flex; align-items: center; gap: 15px; padding: 15px; background: #fafafa; border-radius: 12px; margin-bottom: 10px; }
  .item-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
  .fee-details { margin-top: 15px; padding: 15px; background: #fcfcfc; border-radius: 12px; border: 1px solid #f0f0f0; }
  .fee-line { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; color: #666; }
  .total-main { border-top: 2px dashed #eee; margin-top: 15px; padding-top: 15px; font-size: 20px; font-weight: 800; color: var(--primary-color); display: flex; justify-content: space-between; }
  .note-box { background: #f0f7ff; border: 1px solid #d0e7ff; padding: 12px; border-radius: 10px; cursor: pointer; }
  .note-preview { font-size: 13px; color: #444; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .note-full { display: none; font-size: 13px; color: #444; margin-top: 5px; white-space: normal; line-height: 1.5; }
  .timeline { display: flex; justify-content: space-between; margin-bottom: 35px; position: relative; padding: 0 10px; }
  .timeline::before { content: ""; position: absolute; top: 15px; left: 40px; right: 40px; height: 2px; background: #eee; z-index: 1; }
  .t-step { position: relative; z-index: 2; text-align: center; width: 25%; }
  .t-dot { width: 30px; height: 30px; background: white; border: 2px solid #eee; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
  .t-step.active .t-dot { border-color: var(--accent-color); background: var(--accent-color); color: white; }
  .t-label { font-size: 10px; font-weight: bold; color: var(--text-muted); text-transform: uppercase; }
  .modal-footer { padding: 20px 30px; background: #fcfcfc; border-top: 1px solid #eee; display: flex; gap: 15px; }
</style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">Premium<span>Living</span></a>
    <div class="nav-links">
        <a href="index.php">Collection</a>
        <a href="cart.php">Cart <span class="cart-count" id="cartCount">0</span></a>
        <a href="dashboard.php">Account</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<div class="container">
  <div class="dashboard-header">
    <h1>Account Dashboard</h1>
    <p style="color: var(--text-muted);">Welcome back, Alex. Manage your artisanal orders and profile.</p>
  </div>

  <div class="function-grid">
    <div class="stat-card">
      <span class="icon">🏆</span>
      <h3>Member Status</h3>
      <p>Gold Collector</p>
      <span class="edit-tag" style="color: var(--success);">Verified Member</span>
    </div>
    <a href="profile_edit.php" class="stat-card">
      <span class="icon">👤</span>
      <h3>Personal Profile</h3>
      <p>Alex Wong</p>
      <span class="edit-tag">Click to Edit Info →</span>
    </a>
    <a href="profile_edit.php#address" class="stat-card">
      <span class="icon">📍</span>
      <h3>Shipping Address</h3>
      <p>Tsim Sha Tsui, HK</p>
      <span class="edit-tag">Manage Address →</span>
    </a>
  </div>

  <div class="section-title">Order History</div>

  <div class="order-container">
    <table>
      <thead>
        <tr><th>Order #</th><th>Items</th><th>Delivery Date</th><th>Total</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>#ORD-99201</strong></td>
          <td>Oak Dining Chair (x2)...</td>
          <td>2026-04-12</td>
          <td>$1,050.00</td>
          <td><span class="status-pill">In Progress</span></td>
          <td><button class="btn-manage" onclick="showOrder('#ORD-99201', '2026-04-12')">View Detailed Receipt</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="orderModal">
  <div class="modal-box">
    <div class="modal-header-receipt">
      <button class="close-btn" onclick="hideOrder()">&times;</button>
      <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 3px; opacity: 0.7;">Official Receipt</div>
      <h2 id="mID" style="margin: 10px 0; font-family: 'Playfair Display', serif; font-size: 28px;">#ORD-99201</h2>
      <div style="font-size: 13px;">Customer ID: <strong>USR-8821</strong> | Status: <span style="font-weight: bold; color: #fff;">PAID</span></div>
    </div>
    
    <div class="modal-body">
      <div class="timeline">
        <div class="t-step active"><div class="t-dot">✓</div><div class="t-label">Placed</div></div>
        <div class="t-step active"><div class="t-dot">2</div><div class="t-label">Material</div></div>
        <div class="t-step"><div class="t-dot">3</div><div class="t-label">Production</div></div>
        <div class="t-step"><div class="t-dot">4</div><div class="t-label">Delivery</div></div>
      </div>

      <div class="receipt-section">
        <div class="receipt-section-title">Order Note / Special Request</div>
        <div class="note-box" onclick="toggleNote()">
          <div class="note-preview" id="notePreview">Note: Please call me 30 mins before arrival. Handle the wood...</div>
          <div class="note-full" id="noteFull">Please call me 30 mins before arrival. Handle the wood with extra care as the floor was recently waxed. Please avoid late evening delivery.</div>
          <div style="font-size: 10px; color: var(--accent-color); margin-top: 5px; text-align: right;">(Click to expand)</div>
        </div>
      </div>

      <div class="receipt-section">
        <div class="receipt-section-title">Delivery Details</div>
        <div class="info-grid">
          <div class="info-item"><label>Recipient</label><span>Alex Wong</span></div>
          <div class="info-item"><label>Phone</label><span>+852 9876 5432</span></div>
          <div class="info-item" style="grid-column: span 2;"><label>Address</label><span>Flat A, 12/F, Nathan Luxury Tower, TST, HK</span></div>
          <div class="info-item"><label>Delivery Est.</label><span id="mDate">2026-04-12</span></div>
          <div class="info-item"><label>Order Date</label><span>2026-03-28</span></div>
        </div>
      </div>

      <div class="receipt-section">
        <div class="receipt-section-title">Items & Pricing Details</div>
        <div class="item-row">
          <img src="../Sample Furniture Images/1.png" class="item-thumb">
          <div style="flex:1;">
            <div style="font-weight: 600;">Oak Dining Chair (ID: FUR-001)</div>
            <div style="font-size: 12px; color: var(--text-muted);">Qty: 2 x $450.00</div>
          </div>
          <div style="font-weight: 600;">$900.00</div>
        </div>

        <div class="fee-details">
          <div class="fee-line"><span>Subtotal (Items)</span><span>$900.00</span></div>
          <div class="fee-line"><span>Delivery & Handling</span><span>$150.00</span></div>
          <div class="fee-line" style="color: var(--danger);"><span>Promotion Discount</span><span>-$0.00</span></div>
          <div class="total-main">
            <span>TOTAL PAID</span>
            <span id="mTotal">$1,050.00</span>
          </div>
        </div>
      </div>
      </div>

    <div class="modal-footer">
      <button style="background:white; color:var(--danger); border:1px solid var(--danger); padding:12px; border-radius:8px; cursor:pointer;" onclick="cancelLogic()">Request Cancellation</button>
      <button style="flex:1; background:var(--accent-color); color:white; border:none; padding:12px; border-radius:8px; cursor:pointer;" onclick="hideOrder()">Close Receipt</button>
    </div>
  </div>
</div>

<script>
  function showOrder(id, date) {
    document.getElementById('mID').innerText = id;
    document.getElementById('mDate').innerText = date;
    document.getElementById('orderModal').style.display = 'flex';
  }
  function hideOrder() { 
    document.getElementById('orderModal').style.display = 'none';
    document.getElementById('noteFull').style.display = 'none';
    document.getElementById('notePreview').style.display = 'block';
  }
  
  function toggleNote() {
    const full = document.getElementById('noteFull');
    const prev = document.getElementById('notePreview');
    if (full.style.display === 'none' || full.style.display === '') {
      full.style.display = 'block';
      prev.style.display = 'none';
    } else {
      full.style.display = 'none';
      prev.style.display = 'block';
    }
  }

  function cancelLogic() {
    const deliveryStr = document.getElementById('mDate').innerText;
    const deliveryDate = new Date(deliveryStr);
    const today = new Date();
    const diffDays = Math.ceil((deliveryDate - today) / (1000 * 60 * 60 * 24));
    if (diffDays >= 2) {
      if(confirm("Confirm cancellation of " + document.getElementById('mID').innerText + "?")) {
        alert("Cancellation request sent successfully.");
        hideOrder();
      }
    } else {
      alert("Policy: Cannot cancel within 48 hours of delivery.");
    }
  }

  window.onclick = function(event) {
    if (event.target == document.getElementById('orderModal')) hideOrder();
  }
</script>

</body>
</html>