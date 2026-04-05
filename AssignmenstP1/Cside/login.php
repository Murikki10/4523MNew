<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account | Premium Living</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
  :root {
    --primary-color: #2c3e50;
    --accent-color: #3498db;
    --text-muted: #7f8c8d;
    --border: #eee;
  }

  * { box-sizing: border-box; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
  
  body { 
    font-family: 'Inter', sans-serif; 
    margin: 0; 
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f4f7f6;
    background-image: linear-gradient(rgba(255,255,255,0.8), rgba(255,255,255,0.8)), 
                      url('https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&q=80&w=1500');
    background-size: cover;
    background-position: center;
    overflow: hidden;
  }

  .login-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(15px);
    width: 100%;
    max-width: 420px;
    padding: 50px 40px;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
  }

  /* 切換內容的容器 */
  .auth-form { display: none; animation: fadeIn 0.5s ease; }
  .auth-form.active { display: block; }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .logo { 
    font-family: 'Playfair Display', serif; font-size: 28px; font-weight: bold; 
    color: var(--primary-color); text-decoration: none; display: block; text-align: center; margin-bottom: 25px;
  }
  .logo span { color: var(--accent-color); }

  h2 { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--primary-color); margin: 0 0 10px; text-align: center; }
  p.subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 30px; text-align: center; }

  .form-group { text-align: left; margin-bottom: 18px; }
  .form-group label { display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 1px; }
  .form-group input { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none; }
  .form-group input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1); }

  .btn-primary { width: 100%; padding: 14px; background: var(--primary-color); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 10px; }
  .btn-primary:hover { background: var(--accent-color); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }

  .extra-links { margin-top: 25px; font-size: 13px; color: var(--text-muted); text-align: center; line-height: 1.8; }
  .extra-links a { color: var(--accent-color); text-decoration: none; font-weight: 600; cursor: pointer; }
  .extra-links a:hover { text-decoration: underline; }

  .back-to-login { display: inline-flex; align-items: center; gap: 5px; color: var(--text-muted); font-size: 13px; text-decoration: none; cursor: pointer; margin-bottom: 20px; }
  .back-to-login:hover { color: var(--primary-color); }
</style>
</head>
<body>

<div class="login-card">
  <a href="index.php" class="logo">Premium<span>Living</span></a>

  <div id="loginForm" class="auth-form active">
    <h2>Welcome Back</h2>
    <p class="subtitle">Enter your details to access your account</p>
    <form action="dashboard.php" method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" placeholder="name@example.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-primary">Sign In</button>
    </form>
    <div class="extra-links">
      <a onclick="showForm('forgotForm')">Forgot password?</a><br>
      Don't have an account? <a onclick="showForm('registerForm')">Create Account</a>
    </div>
  </div>

  <div id="registerForm" class="auth-form">
    <h2>Join Us</h2>
    <p class="subtitle">Create an account for a premium experience</p>
    <form action="dashboard.php" method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" placeholder="Alex Wong" required>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" placeholder="name@example.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" placeholder="Create a password" required>
      </div>
      <button type="submit" class="btn-primary">Create Account</button>
    </form>
    <div class="extra-links">
      Already have an account? <a onclick="showForm('loginForm')">Sign In</a>
    </div>
  </div>

  <div id="forgotForm" class="auth-form">
    <a onclick="showForm('loginForm')" class="back-to-login">← Back to Login</a>
    <h2>Reset Password</h2>
    <p class="subtitle">Enter your email and we'll send you instructions</p>
    <form onsubmit="event.preventDefault(); alert('Reset link sent to your email!'); showForm('loginForm');">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" placeholder="name@example.com" required>
      </div>
      <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>
  </div>
</div>

<script>
  function showForm(formId) {
    // 隱藏所有表單
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => form.classList.remove('active'));
    
    // 顯示目標表單
    const targetForm = document.getElementById(formId);
    targetForm.classList.add('active');
  }
</script>

</body>
</html>