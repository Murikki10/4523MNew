<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings | Premium Living</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
  :root {
    --primary-color: #2c3e50;
    --accent-color: #3498db;
    --text-muted: #7f8c8d;
    --bg-light: #f8f9fa;
    --border: #ececec;
    --danger: #e74c3c;
    --success: #27ae60;
  }

  * { box-sizing: border-box; transition: all 0.2s ease; }
  body { font-family: 'Inter', sans-serif; margin: 0; background-color: #fcfcfc; color: #333; overflow-y: scroll; }

  /* --- Navigation --- */
  nav.top-nav { 
    background: #fff; padding: 15px 8%; display: flex; justify-content: space-between; 
    align-items: center; border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 1000;
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: bold; color: var(--primary-color); text-decoration: none; }
  .logo span { color: var(--accent-color); }

  /* --- Layout Wrapper --- */
  .main-wrapper { 
    max-width: 1200px; margin: 40px auto; padding: 0 5%; 
    display: flex; gap: 30px; 
  }

  /* --- Sidebar --- */
  .sidebar { width: 280px; flex-shrink: 0; background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 20px; height: fit-content; }
  .sidebar-header { padding: 0 10px 20px; border-bottom: 1px solid var(--border); margin-bottom: 15px; }
  .sidebar-header h3 { margin: 0; font-size: 18px; color: var(--primary-color); }
  
  .menu-item { 
    display: flex; align-items: center; padding: 12px 18px; text-decoration: none; 
    color: #555; font-size: 14px; border-radius: 12px; margin-bottom: 8px; gap: 12px; cursor: pointer;
  }
  .menu-item:hover { background: var(--bg-light); color: var(--accent-color); }
  .menu-item.active { background: var(--accent-color); color: white; font-weight: 600; }
  .menu-item i { font-style: normal; font-size: 18px; width: 24px; text-align: center; }

  /* --- Content Card (Tab Pages) --- */
  .tab-content { display: none; width: 100%; }
  .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

  .content-card { 
    background: #fff; border: 1px solid var(--border); border-radius: 20px; padding: 40px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.02);
  }
  .content-header { margin-bottom: 35px; }
  .content-header h2 { font-family: 'Playfair Display', serif; font-size: 32px; margin: 0; color: var(--primary-color); }
  .content-header p { color: var(--text-muted); font-size: 14px; margin-top: 10px; }

  /* --- Form Layout --- */
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
  .form-group.full { grid-column: span 2; }
  label { display: block; font-size: 11px; font-weight: 700; color: var(--primary-color); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
  input, select { width: 100%; padding: 14px 16px; border: 1px solid var(--border); border-radius: 10px; font-size: 15px; background: #fbfbfb; }
  input:focus { border-color: var(--accent-color); background: #fff; outline: none; }

  .btn-save { background: var(--primary-color); color: white; border: none; padding: 16px 45px; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 20px; }
  .btn-save:hover { background: var(--accent-color); transform: translateY(-2px); }

  /* Responsive */
  @media (max-width: 850px) {
    .main-wrapper { flex-direction: column; }
    .sidebar { width: 100%; }
  }
</style>
</head>
<body>

<nav class="top-nav">
  <a href="index.php" class="logo">Premium<span>Living</span></a>
  <div style="font-size: 14px; color: var(--text-muted);">Signed in as <strong>Alex Wong</strong></div>
</nav>

<div class="main-wrapper">
  <aside class="sidebar">
    <div class="sidebar-header">
      <h3>Account Settings</h3>
    </div>
    <nav class="sidebar-menu">
      <div class="menu-item active" onclick="openTab(event, 'profile')"><i>👤</i> Personal Info</div>
      <div class="menu-item" onclick="openTab(event, 'address')"><i>📍</i> Shipping Address</div>
      <div class="menu-item" onclick="openTab(event, 'security')"><i>🔒</i> Password & Security</div>
      <a href="dashboard.php" class="menu-item" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;"><i>📦</i> Back to DashBoard</a>
    </nav>
  </aside>

  <div style="flex-grow: 1;">
    
    <div id="profile" class="tab-content active">
      <div class="content-card">
        <div class="content-header">
          <h2>Personal Information</h2>
          <p>Manage your basic account details and language preferences.</p>
        </div>
        <form onsubmit="alert('Profile Updated!'); return false;">
          <div class="form-grid">
            <div class="form-group"><label>First Name</label><input type="text" value="Alex"></div>
            <div class="form-group"><label>Last Name</label><input type="text" value="Wong"></div>
            <div class="form-group full"><label>Email Address</label><input type="email" value="alex.wong@example.com"></div>
            <div class="form-group"><label>Phone</label><input type="tel" value="+852 9876 5432"></div>
            <div class="form-group"><label>Language</label><select><option>English</option><option>Traditional Chinese</option></select></div>
          </div>
          <button type="submit" class="btn-save">Save Profile</button>
        </form>
      </div>
    </div>

    <div id="address" class="tab-content">
      <div class="content-card">
        <div class="content-header">
          <h2>Shipping Address</h2>
          <p>Your primary delivery location for all furniture orders.</p>
        </div>
        <form onsubmit="alert('Address Updated!'); return false;">
          <div class="form-grid">
            <div class="form-group full"><label>Street Address</label><input type="text" value="Flat A, 12/F, Nathan Luxury Tower"></div>
            <div class="form-group"><label>District</label><input type="text" value="Tsim Sha Tsui"></div>
            <div class="form-group"><label>City / Region</label><input type="text" value="Kowloon" readonly style="background:#eee;"></div>
          </div>
          <button type="submit" class="btn-save">Update Address</button>
        </form>
      </div>
    </div>

    <div id="security" class="tab-content">
      <div class="content-card">
        <div class="content-header">
          <h2>Security & Password</h2>
          <p>Ensure your account stays secure with a strong password.</p>
        </div>
        <form onsubmit="alert('Password Changed!'); return false;">
          <div class="form-grid" style="grid-template-columns: 1fr;">
            <div class="form-group"><label>Current Password</label><input type="password" placeholder="••••••••"></div>
            <div class="form-group"><label>New Password</label><input type="password" placeholder="Enter new password"></div>
            <div class="form-group"><label>Confirm New Password</label><input type="password" placeholder="Repeat new password"></div>
          </div>
          <button type="submit" class="btn-save" style="background: var(--accent-color);">Update Password</button>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
  function openTab(evt, tabName) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
      tabContents[i].classList.remove("active");
    }
    const menuItems = document.getElementsByClassName("menu-item");
    for (let i = 0; i < menuItems.length; i++) {
      menuItems[i].classList.remove("active");
    }

    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
  }

  window.onload = function() {
    if(window.location.hash === '#address') {
      const addressBtn = document.querySelector('[onclick*="address"]');
      if(addressBtn) addressBtn.click();
    }
  }
</script>

</body>
</html>