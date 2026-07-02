/* ==========================================================================
   Premium Living - Global Customer Portal Script Engine (cust_script.js)
   ========================================================================== */

/* --------------------------------------------------------------------------
   📌 MODULE 1: AUTHENTICATION PORTAL ENGINE (Login / Register)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    // Toggle view states between login and registration panels smoothly
    function switchForm(panelName) {
        const loginPanel = document.getElementById('loginForm');
        const registerPanel = document.getElementById('registerForm');
        if (!loginPanel || !registerPanel) return;

        if (panelName === 'register') {
            loginPanel.classList.remove('active');
            registerPanel.classList.add('active');
        } else {
            registerPanel.classList.remove('active');
            loginPanel.classList.add('active');
        }
    }

    // Client-side instant password mismatch interception guard
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

    // Automated float toast dismissal animation sequence controller
    function initToastController() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() { alert.classList.add('toast-show'); }, 100);
            setTimeout(function() {
                alert.classList.remove('toast-show');
                alert.classList.add('toast-hide');
                setTimeout(function() { alert.remove(); }, 400);
            }, 3000); 
        });
    }

    // Initialize module event tracking and programmatic event binding
    document.addEventListener("DOMContentLoaded", function() {
        initToastController();

        const registerForm = document.getElementById('pl-register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', validatePasswords);
        }

        // 🌐 Standard Event Listeners binding for the panel switching buttons
        const toRegisterBtn = document.getElementById('pl-trigger-to-register');
        if (toRegisterBtn) {
            toRegisterBtn.addEventListener('click', function() {
                switchForm('register');
            });
        }

        const toLoginBtn = document.getElementById('pl-trigger-to-login');
        if (toLoginBtn) {
            toLoginBtn.addEventListener('click', function() {
                switchForm('login');
            });
        }
    });
})();

/* --------------------------------------------------------------------------
   📌 MODULE 2: FRONT-END FURNITURE CATALOG HOME ENGINE (index.php)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    // Dynamic, no-reload search and multi-metric sorting catalog execution loop
    function filterAndSortCatalog() {
        const searchInput = document.getElementById('pl-catalog-search');
        const sortSelect = document.getElementById('pl-catalog-sort');
        const gridContainer = document.getElementById('pl-product-grid');
        
        if (!searchInput || !sortSelect || !gridContainer) return;

        const keyword = searchInput.value.toLowerCase().trim();
        const sortValue = sortSelect.value;
        const cards = Array.from(gridContainer.querySelectorAll('.product-card'));

        // Filter subset elements purely utilizing safe furniture names metadata attributes
        cards.forEach(card => {
            const fname = card.getAttribute('data-fname') || "";
            if (fname.includes(keyword)) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });

        // Perform programmatic array re-sorting
        cards.sort((cardA, cardB) => {
            if (sortValue === 'price-asc') {
                return Number(cardA.getAttribute('data-price')) - Number(cardB.getAttribute('data-price'));
            }
            if (sortValue === 'price-desc') {
                return Number(cardB.getAttribute('data-price')) - Number(cardA.getAttribute('data-price'));
            }
            return Number(cardA.getAttribute('data-fid')) - Number(cardB.getAttribute('data-fid'));
        });

        // Flush newly synchronized order tree structure to the layout grid UI wrapper
        cards.forEach(card => gridContainer.appendChild(card));
    }

    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('pl-catalog-search');
        const sortSelect = document.getElementById('pl-catalog-sort');

        if (searchInput) searchInput.addEventListener('keyup', filterAndSortCatalog);
        if (sortSelect) sortSelect.addEventListener('change', filterAndSortCatalog);
    });
})();


/* --------------------------------------------------------------------------
   📌 MODULE 3: FRONT-END PRODUCT DETAILS ENGINE (detail.php)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    let currentQty = 1;
    let maxAllowedStock = 1; 

    // Dynamic counter modifier with real-time hardware material maximum constraint validation
    function updateDetailQty(change) {
        const qtyValueEl = document.getElementById('qtyValue');
        if (!qtyValueEl) return;

        currentQty = Math.max(1, currentQty + change);
        if (currentQty > maxAllowedStock) {
            currentQty = maxAllowedStock;
            alert(`Notice: Factory capacity reached! We only have raw materials left to fulfill ${maxAllowedStock} units of this item.`);
        }
        
        qtyValueEl.innerText = currentQty;
    }

    // Persist cart array allocations efficiently inside client LocalStorage variables
    function triggerAddToCart() {
        const configDataset = document.getElementById('pl-detail-config-metadata');
        if (!configDataset) return;

        const product = {
            id: configDataset.getAttribute('data-fid'),
            name: configDataset.getAttribute('data-fname'),
            price: parseFloat(configDataset.getAttribute('data-price')),
            img: configDataset.getAttribute('data-img'),
            qty: currentQty
        };

        let cart = JSON.parse(localStorage.getItem('furniture_cart')) || [];
        const existingIndex = cart.findIndex(item => item.id === product.id);

        if (existingIndex > -1) {
            let newQty = cart[existingIndex].qty + product.qty;
            if (newQty > maxAllowedStock) {
                newQty = maxAllowedStock;
                alert(`Notice: Cart updated to maximum available limit (${maxAllowedStock} units) based on warehouse materials stock.`);
            }
            cart[existingIndex].qty = newQty;
        } else {
            cart.push(product);
        }

        localStorage.setItem('furniture_cart', JSON.stringify(cart));
        refreshGlobalCartBadge();
        
        // Render right-aligned success toast feedback alert modal
        const toast = document.getElementById('toast');
        if (toast) {
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }
    }

    // Synchronize current active badge data indicators inside navigation structures globally
    function refreshGlobalCartBadge() {
        const cartCountEl = document.getElementById('cartCount');
        if (!cartCountEl) return;
        
        let cart = JSON.parse(localStorage.getItem('furniture_cart')) || [];
        let totalPieces = 0;
        cart.forEach(item => { totalPieces += parseInt(item.qty); });
        cartCountEl.innerText = totalPieces;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const configDataset = document.getElementById('pl-detail-config-metadata');
        if (configDataset) {
            maxAllowedStock = parseInt(configDataset.getAttribute('data-max-stock')) || 1;
        }

        refreshGlobalCartBadge();

        const minusBtn = document.getElementById('pl-qty-minus');
        const plusBtn = document.getElementById('pl-qty-plus');
        const addCartBtn = document.getElementById('pl-add-cart-submit');

        if (minusBtn) minusBtn.addEventListener('click', function() { updateDetailQty(-1); });
        if (plusBtn) plusBtn.addEventListener('click', function() { updateDetailQty(1); });
        if (addCartBtn) addCartBtn.addEventListener('click', triggerAddToCart);
    });

    // Share utility reference across sibling module enclosures safely
    window.refreshGlobalCartBadge = refreshGlobalCartBadge;
})();


/* --------------------------------------------------------------------------
   📌 MODULE 4: FRONT-END SHOPPING CART & CHECKOUT ENGINE (cart.php)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    let cart = JSON.parse(localStorage.getItem('furniture_cart')) || [];

    // Parse and render localized transactional lists dynamic loops
    function renderCheckoutCart() {
        const listContainer = document.getElementById('cartList');
        const summaryContainer = document.getElementById('summaryDetails');
        if (!listContainer || !summaryContainer) return;

        if (cart.length === 0) {
            listContainer.innerHTML = "<p style='color:#999; padding: 20px 0;'>Your collection cart is currently empty.</p>";
            summaryContainer.innerHTML = "<p style='color:#999; font-size:13px;'>No items selected for evaluation.</p>";
            const payBtn = document.getElementById('pl-fake-submit-trigger');
            if (payBtn) payBtn.disabled = true;
            return;
        }

        let html = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            subtotal += item.price * item.qty;
            html += `
                <div class="cart-item">
                    <img src="${item.img}" class="item-img" onerror="this.src='../Sample Furniture Images/1.png';">
                    <div class="item-info">
                        <div style="font-weight:600; color:#2c3e50; font-size:16px;">${item.name}</div>
                        <div style="font-size:13px; color:#7f8c8d; margin-top:4px;">$${item.price.toFixed(2)}</div>
                    </div>
                    <div class="item-qty">
                        <button type="button" class="qty-btn" data-index="${index}" data-change="-1">-</button>
                        <span style="font-weight:600; min-width:20px; text-align:center;">${item.qty}</span>
                        <button type="button" class="qty-btn" data-index="${index}" data-change="1">+</button>
                    </div>
                    <div style="margin-left:30px; font-weight:600; color:#2c3e50; min-width:80px; text-align:right;">$${(item.price * item.qty).toFixed(2)}</div>
                </div>
            `;
        });

        listContainer.innerHTML = html;
        
        const deliveryFee = 150.00;
        const totalAmount = subtotal + deliveryFee;

        summaryContainer.innerHTML = `
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#666;"><span>Subtotal</span><span style="font-weight:600; color:#2c3e50;">$${subtotal.toFixed(2)}</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#666;"><span>Premium Delivery</span><span style="font-weight:600; color:#2c3e50;">$${deliveryFee.toFixed(2)}</span></div>
            <hr style="border:0; border-top:1px dashed #eee; margin: 15px 0;">
            <div style="display:flex; justify-content:space-between; font-size:20px; font-weight:bold; margin:15px 0; color:#2c3e50;"><span>Total Amount</span><span style="color:#27ae60;">$${totalAmount.toFixed(2)}</span></div>
        `;

        const hiddenTotalInput = document.getElementById('pl-hidden-total-amount');
        if (hiddenTotalInput) hiddenTotalInput.value = totalAmount.toFixed(2);

        bindCartItemButtons();
    }

    // Bind event tracking actions cleanly to real-time modifiers
    function bindCartItemButtons() {
        const listContainer = document.getElementById('cartList');
        if (!listContainer) return;

        const qtyButtons = listContainer.querySelectorAll('.qty-btn');
        qtyButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                const change = parseInt(this.getAttribute('data-change'));
                
                cart[index].qty += change;
                if (cart[index].qty <= 0) {
                    cart.splice(index, 1);
                }
                
                localStorage.setItem('furniture_cart', JSON.stringify(cart));
                renderCheckoutCart();
                
                if (typeof window.refreshGlobalCartBadge === 'function') {
                    window.refreshGlobalCartBadge();
                }
            });
        });
    }

    function triggerClientSuccessModal() {
        if (cart.length === 0) {
            alert("Your shopping cart is currently empty!");
            return false;
        }

        const dateInput = document.getElementById('finalDate');
        if (!dateInput || !dateInput.value) {
            alert("Please select your expected delivery date.");
            return false;
        }

        const successModal = document.getElementById('successModal');
        if (successModal) successModal.style.display = 'flex';
    }

    function executeFinalFormSubmit(targetDestination) {
        const redirectInput = document.getElementById('pl-redirect-target');
        const hiddenCartInput = document.getElementById('pl-hidden-cart-data');
        const checkoutForm = document.getElementById('pl-checkout-main-form');
        
        if (!redirectInput || !hiddenCartInput || !checkoutForm) return;

        redirectInput.value = targetDestination;

        hiddenCartInput.value = JSON.stringify(cart);
        
        localStorage.removeItem('furniture_cart');

        checkoutForm.submit();
    }

    // Implement Make-to-Order Custom Craftsmanship dynamic calendar thresholds guard
    function enforceMinCraftingLeadTime() {
        const dateInput = document.getElementById('finalDate');
        const noticeEl = document.getElementById('pl-delivery-notice');
        if (!dateInput || !noticeEl) return;

        const MIN_CRAFTING_DAYS = 7; 
        const today = new Date();
        const earliestDate = new Date(today);
        earliestDate.setDate(today.getDate() + MIN_CRAFTING_DAYS);

        const year = earliestDate.getFullYear();
        const month = String(earliestDate.getMonth() + 1).padStart(2, '0');
        const day = String(earliestDate.getDate()).padStart(2, '0');
        const formattedMinDate = `${year}-${month}-${day}`;

        dateInput.min = formattedMinDate;
        dateInput.value = formattedMinDate; 

        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const readableDate = earliestDate.toLocaleDateString('en-US', options);
        
        noticeEl.innerHTML = `✨ Artisanal Craftsmanship Notice: As each premium piece is meticulously handcrafted upon receiving your collection request, the earliest estimated completion and dispatch date is <strong>${readableDate}</strong>.`;
    }

    document.addEventListener("DOMContentLoaded", function() {
        renderCheckoutCart();
        enforceMinCraftingLeadTime();

        const fakeSubmitBtn = document.getElementById('pl-fake-submit-trigger');
        if (fakeSubmitBtn) {
            fakeSubmitBtn.addEventListener('click', triggerClientSuccessModal);
        }

        const modalGoDashBtn = document.getElementById('pl-modal-go-dash');
        if (modalGoDashBtn) {
            modalGoDashBtn.addEventListener('click', function() {
                executeFinalFormSubmit('dashboard.php');
            });
        }

        const modalGoShopBtn = document.getElementById('pl-modal-go-shop');
        if (modalGoShopBtn) {
            modalGoShopBtn.addEventListener('click', function() {
                executeFinalFormSubmit('index.php');
            });
        }
    });
})();

/* --------------------------------------------------------------------------
   📌 MODULE 5: FRONT-END CLIENT DASHBOARD ENGINE (dashboard.php)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    // Client-side instant sorting mechanism across dataset matrices
    function sortDashboardOrders() {
        const sortSelect = document.getElementById('orderSort');
        const tableBody = document.querySelector('#orderTable tbody');
        if (!sortSelect || !tableBody) return;

        const sortValue = sortSelect.value;
        const rows = Array.from(tableBody.querySelectorAll('tr'));

        rows.sort((rowA, rowB) => {
            if (sortValue.includes('date')) {
                const dateA = new Date(rowA.getAttribute('data-date'));
                const dateB = new Date(rowB.getAttribute('data-date'));
                return sortValue === 'date-desc' ? dateB - dateA : dateA - dateB;
            } else {
                const priceA = parseFloat(rowA.getAttribute('data-price'));
                const priceB = parseFloat(rowB.getAttribute('data-price'));
                return sortValue === 'price-desc' ? priceB - priceA : priceA - priceB;
            }
        });

        tableBody.innerHTML = "";
        rows.forEach(row => tableBody.appendChild(row));
    }

    // 🌐 Dynamic modal initialization parser mapping backend tokens to image_7bc0b6.png timeline terminology
    function triggerShowReceiptModal(btnElement) {
        const modal = document.getElementById('orderModal');
        if (!modal || !btnElement) return;

        const oid = btnElement.getAttribute('data-oid');
        const date = btnElement.getAttribute('data-date');
        const rawTotal = parseFloat(btnElement.getAttribute('data-total'));
        const status = btnElement.getAttribute('data-status'); // Expected values: Processing, Approved, Out for Delivery, Completed, Cancelled
        const address = btnElement.getAttribute('data-address');
        
        const rawItemsJson = btnElement.getAttribute('data-items-json');
        let itemsArray = [];
        try {
            itemsArray = JSON.parse(rawItemsJson) || [];
        } catch(e) { console.error("Receipt detail dataset parsing failed", e); }

        // Bind standard metadata strings to English labels
        document.getElementById('mID').innerText = `#ORD-${oid}`;
        document.getElementById('mDate').innerText = date;
        document.getElementById('mTotal').innerText = `$${rawTotal.toFixed(2)}`;
        document.getElementById('mReceiptAddress').innerText = address;

        const header = document.getElementById('modalHeader');
        const timeline = document.getElementById('modalTimeline');
        const cancelledNotice = document.getElementById('pl-cancelled-notice');
        const statusDisplay = document.getElementById('mStatusDisplay');
        
        // Reset component configurations to default templates
        if (header) header.className = 'modal-header-receipt';
        if (timeline) timeline.style.display = 'flex';
        if (cancelledNotice) cancelledNotice.style.display = 'none';

        // Wipe out all previous active styling lights from the pipeline steps (Ensures step4 is safe)
        const steps = ['step1', 'step2', 'step3', 'step4'];
        steps.forEach(s => {
            const el = document.getElementById(s);
            if (el) el.classList.remove('active');
        });

        // 🚀 100% Alignment with image_7bc0b6.png pipeline states mapping
        if (status === 'Processing') {
            if (statusDisplay) {
                statusDisplay.innerText = 'Processing';
                statusDisplay.style.color = '#ffffff';
            }
            if (header) header.classList.add('header-pending');
            const st1 = document.getElementById('step1');
            if (st1) st1.classList.add('active');
        } 
        else if (status === 'Approved') { 
            if (statusDisplay) {
                statusDisplay.innerText = 'Approved';
                statusDisplay.style.color = '#ffffff';
            }
            if (header) header.classList.add('header-progress');
            const st1 = document.getElementById('step1');
            const st2 = document.getElementById('step2');
            if (st1) st1.classList.add('active');
            if (st2) st2.classList.add('active');
        } 
        else if (status === 'Out for Delivery') { 
            if (statusDisplay) {
                statusDisplay.innerText = 'Out for Delivery';
                statusDisplay.style.color = '#ffffff';
            }
            if (header) header.classList.add('header-progress');
            const st1 = document.getElementById('step1');
            const st2 = document.getElementById('step2');
            const st3 = document.getElementById('step3');
            if (st1) st1.classList.add('active');
            if (st2) st2.classList.add('active');
            if (st3) st3.classList.add('active');
        } 
        else if (status === 'Completed') { 
            if (statusDisplay) {
                statusDisplay.innerText = 'Completed';
                statusDisplay.style.color = '#ffffff';
            }
            if (header) header.classList.add('header-delivered');
            steps.forEach(s => {
                const el = document.getElementById(s);
                if (el) el.classList.add('active');
            });
        } 
        else if (status === 'Cancelled') { 
            if (statusDisplay) {
                statusDisplay.innerText = 'Cancelled';
                statusDisplay.style.color = '#ffc107'; 
            }
            if (header) header.classList.add('header-cancelled');
            
            // Completely hide the progress timeline flow structure if the transaction is cancelled
            if (timeline) timeline.style.display = 'none';
            if (cancelledNotice) cancelledNotice.style.display = 'block';
        }

        // Dynamically compute and map nested order item line specifications rows
        const itemsContainer = document.getElementById('pl-modal-receipt-items-list');
        let itemsHtml = '';
        let calculatedSubtotal = 0;

        itemsArray.forEach(item => {
            const itemCost = parseFloat(item.price) * parseInt(item.qty);
            calculatedSubtotal += itemCost;
            
            itemsHtml += `
                <div class="item-row">
                    <img src="../Sample Furniture Images/${item.fid}.png" class="item-thumb" onerror="this.src='../Sample Furniture Images/1.png';">
                    <div style="flex:1; text-align:left;">
                        <div style="font-weight: 600; color:#2c3e50;">${item.name}</div>
                        <div style="font-size: 12px; color: #7f8c8d; margin-top:2px;">Quantity: ${item.qty} &nbsp;|&nbsp; Price: $${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div style="font-weight:600; color:#2c3e50;">$${itemCost.toFixed(2)}</div>
                </div>
            `;
        });
        if (itemsContainer) itemsContainer.innerHTML = itemsHtml;
        
        const subtotalEl = document.getElementById('mReceiptSubtotal');
        if (subtotalEl) subtotalEl.innerText = `$${calculatedSubtotal.toFixed(2)}`;

        modal.style.display = 'flex';
    }

    function hideReceiptModal() {
        const modal = document.getElementById('orderModal');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const sortSelect = document.getElementById('orderSort');
        if (sortSelect) sortSelect.addEventListener('change', sortDashboardOrders);

        // Bind table click events natively via target delegate bubble checks
        const tableBody = document.querySelector('#orderTable tbody');
        if (tableBody) {
            tableBody.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-manage')) {
                    triggerShowReceiptModal(e.target);
                }
            });
        }

        const closeBtn = document.getElementById('pl-close-receipt-modal-btn');
        const footerCloseBtn = document.getElementById('pl-footer-close-receipt-btn');
        if (closeBtn) closeBtn.addEventListener('click', hideReceiptModal);
        if (footerCloseBtn) footerCloseBtn.addEventListener('click', hideReceiptModal);

        window.addEventListener('click', function(e) {
            const modal = document.getElementById('orderModal');
            if (e.target === modal) hideReceiptModal();
        });
    });
})();

/* --------------------------------------------------------------------------
   📌 MODULE 6: FRONT-END PROFILE SETTINGS ENGINE (profile_edit.php)
   -------------------------------------------------------------------------- */
(function() {
    "use strict";

    function openSettingsTab(evt, tabName) {
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove("active");
        }
        const menuItems = document.querySelectorAll(".sidebar-menu .menu-item");
        menuItems.forEach(item => item.classList.remove("active"));

        const targetTab = document.getElementById(tabName);
        if (targetTab) targetTab.classList.add("active");
        evt.currentTarget.classList.add("active");
        
        window.location.hash = tabName;
    }

    function validateSecurityForm(e) {
        const newPass = document.getElementById('pl-new-password').value;
        const confirmPass = document.getElementById('pl-confirm-password').value;

        if (newPass !== confirmPass) {
            alert("Error: New passwords do not match! Please verify your entry.");
            e.preventDefault();
            return false;
        }
        return true;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const menuItems = document.querySelectorAll(".sidebar-menu .menu-item[data-tab]");
        menuItems.forEach(item => {
            item.addEventListener("click", function(e) {
                const tabName = this.getAttribute("data-tab");
                openSettingsTab(e, tabName);
            });
        });

        const securityForm = document.getElementById('pl-security-form');
        if (securityForm) {
            securityForm.addEventListener('submit', validateSecurityForm);
        }

        if (window.location.hash) {
            const hash = window.location.hash.replace('#', '');
            const activeBtn = document.querySelector(`.sidebar-menu .menu-item[data-tab="${hash}"]`);
            if (activeBtn) {
                activeBtn.click();
            }
        }
    });
})();