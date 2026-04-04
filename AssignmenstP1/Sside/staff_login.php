<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login - Premium Living</title>
<style>
  /* --- Professional Login Layout --- */
  * { box-sizing: border-box; }
  body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    margin: 0; 
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  }

  .login-card {
    background: white;
    width: 100%;
    max-width: 420px;
    padding: 50px 40px;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    text-align: center;
  }

  .brand-logo {
    font-size: 32px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
  }
  .brand-logo span { color: #3498db; }
  
  .login-title {
    color: #95a5a6;
    font-size: 14px;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 35px;
  }

  .input-group {
    text-align: left;
    margin-bottom: 25px;
  }
  .input-group label {
    display: block;
    font-size: 12px;
    font-weight: bold;
    color: #34495e;
    margin-bottom: 8px;
  }
  .input-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #f1f4f8;
    border-radius: 8px;
    font-size: 15px;
    background: #f8f9fa;
    transition: 0.3s;
  }
  .input-group input:focus {
    border-color: #3498db;
    background: white;
    outline: none;
  }

  .btn-login {
    width: 100%;
    padding: 14px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
  }
  .btn-login:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
  }

  .footer-links {
    margin-top: 30px;
    font-size: 13px;
    color: #bdc3c7;
  }
  .footer-links a {
    color: #3498db;
    text-decoration: none;
  }
</style>
</head>
<body>

<div class="login-card">
  <div class="brand-logo">Premium <span>Living</span></div>
  <div class="login-title">Internal Staff Portal</div>

  <!-- Form for UI Demo: Clicking button triggers redirection directly -->
  <form onsubmit="redirectToDashboard(event)">
    <div class="input-group">
      <label>STAFF ID / EMAIL</label>
      <input type="text" placeholder="Enter your ID" value="admin@premium.com" required>
    </div>

    <div class="input-group">
      <label>PASSWORD</label>
      <input type="password" placeholder="Enter password" value="••••••••" required>
    </div>

    <button type="submit" class="btn-login">Sign In to Dashboard</button>
  </form>

  <div class="footer-links">
    <p>Forgot password? <a href="#">Contact IT Admin</a></p>
    <p><a href="../Cside/index.html">← Return to Main Website</a></p>
  </div>
</div>

<script>
  function redirectToDashboard(e) {
    e.preventDefault(); // Stop actual form submission
    
    // Smooth transition effect (optional)
    const btn = document.querySelector('.btn-login');
    btn.innerHTML = "Authenticating...";
    btn.style.opacity = "0.7";
    
    // Redirect after a short delay for "demo realism"
    setTimeout(() => {
      window.location.href = 'staff_index.php';
    }, 600);
  }
</script>

</body>
</html>