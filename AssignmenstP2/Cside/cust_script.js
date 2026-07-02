/* ==========================================================================
   Premium Living - Global Customer Portal Script Engine (cust_script.js)
   ========================================================================== */

/* --------------------------------------------------------------------------
   📌 MODULE 1: AUTHENTICATION PORTAL ENGINE (Login / Register)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    // 翻轉切換登入/註冊表單
    function switchForm(panelName) {
        const loginPanel = document.getElementById('login-panel');
        const registerPanel = document.getElementById('register-panel');
        if (!loginPanel || !registerPanel) return;

        if (panelName === 'register') {
            loginPanel.style.display = 'none';
            registerPanel.style.display = 'block';
        } else {
            loginPanel.style.display = 'block';
            registerPanel.style.display = 'none';
        }
    }

    // 前端密碼即時雙重驗證
    function validatePasswords(e) {
        const pass = document.getElementById('reg-password').value;
        const confirmPass = document.getElementById('reg-confirm-password').value;
        
        if (pass !== confirmPass) {
            alert("Error: Passwords do not match! Please check your fields.");
            e.preventDefault(); 
            return false;
        }
        return true;
    }

    // Toast 動態控制自動消散
    function initToastController() {
        const alerts = document.querySelectorAll('.pl-auth-wrapper .alert');
        alerts.forEach(function(alert) {
            setTimeout(function() { alert.classList.add('toast-show'); }, 100);
            setTimeout(function() {
                alert.classList.remove('toast-show');
                alert.classList.add('toast-hide');
                setTimeout(function() { alert.remove(); }, 400);
            }, 3000); 
        });
    }

    // 監聽並安全綁定
    document.addEventListener("DOMContentLoaded", function() {
        initToastController();

        const toRegisterBtn = document.getElementById('pl-to-register-btn');
        if (toRegisterBtn) {
            toRegisterBtn.addEventListener('click', function() { switchForm('register'); });
        }

        const toLoginBtn = document.getElementById('pl-to-login-btn');
        if (toLoginBtn) {
            toLoginBtn.addEventListener('click', function() { switchForm('login'); });
        }

        const registerForm = document.getElementById('pl-register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', validatePasswords);
        }
    });
})();


/* --------------------------------------------------------------------------
   📌 MODULE 2: FUTURE FURNITURE CATALOG ENGINE
   -------------------------------------------------------------------------- */
// 你之後新頁面（例如商品目錄）嘅 JS 邏輯，可以直接在下面開啟新閉包續寫：
// (function() { ... })();