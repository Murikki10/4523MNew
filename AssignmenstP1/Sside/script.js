/* 
   Shared JavaScript for Premium Living Staff Portal 
   Handles Modal logic and Material Selection
*/

const modal = document.getElementById("materialModal");

// Open the Material Selection Modal
function openModal() {
    if (modal) {
        modal.style.display = "block";
    }
}

// Close the Material Selection Modal
function closeModal() {
    if (modal) {
        modal.style.display = "none";
    }
}

// Process selected materials and display them as tags
function confirmSelection() {
    const display = document.getElementById("selected-materials-display");
    const checkboxes = document.querySelectorAll(".mat-check");
    const quantities = document.querySelectorAll(".mat-qty");
    
    let html = "";
    let hasSelection = false;

    checkboxes.forEach((cb, index) => {
        if (cb.checked) {
            const qty = quantities[index].value || 0;
            const name = cb.getAttribute("data-name");
            
            // Generate tags with hidden inputs for PHP processing
            html += `
                <div class="material-tag">
                    <span>${name} (x${qty})</span>
                    <input type="hidden" name="mids[]" value="${cb.value}">
                    <input type="hidden" name="pmqtys[]" value="${qty}">
                    <span class="remove-tag" onclick="this.parentElement.remove()">✕</span>
                </div>
            `;
            hasSelection = true;
        }
    });

    if (display) {
        display.innerHTML = hasSelection ? html : '<span style="color: #95a5a6; font-size: 14px;">No materials selected...</span>';
    }
    closeModal();
}

// Close modal when clicking outside of the modal content
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

/* Shared JavaScript - Premium Living Staff Portal
   核心功能：處理訂單多商品顯示、編輯模式及狀態更新
*/

// --- 訂單詳情 Modal 邏輯 ---
const orderModal = document.getElementById("orderDetailModal");

function openOrderModal(orderData) {
    if (!orderModal) return;

    // 1. 填充基礎資訊 [cite: 293, 299, 300, 301]
    document.getElementById("modal-oid").innerText = orderData.oid;
    document.getElementById("modal-cname").innerText = orderData.cname;
    document.getElementById("modal-ctel").innerText = orderData.ctel;
    document.getElementById("modal-caddr").innerText = orderData.caddr;
    document.getElementById("modal-total").innerText = orderData.total;
    
    // 2. 設定狀態選擇器與 UI 顏色 [cite: 303]
    const statusSelect = document.getElementById("modal-status-select");
    statusSelect.value = orderData.status;
    updateStatusUI(orderData.status);

    // 3. 清空並動態生成商品列表 [cite: 315]
    const itemsContainer = document.getElementById("modal-items-container");
    itemsContainer.innerHTML = ""; 

    orderData.items.forEach((item) => {
        const itemRow = document.createElement("div");
        itemRow.className = "item-row";
        itemRow.innerHTML = `
            <div class="item-main">
                <img src="${item.fimg}" class="item-img-small" alt="Furniture">
                <div class="item-info">
                    <span class="item-name">${item.fname}</span>
                    <span class="item-price-detail">
                        $${item.price.toFixed(2)} x <span class="qty-text">${item.qty}</span>
                    </span>
                </div>
                <div class="item-subtotal">$${(item.price * item.qty).toFixed(2)}</div>
            </div>
            <div class="item-edit-controls" style="display:none;">
                <input type="number" class="edit-qty" value="${item.qty}" min="1" 
                       onchange="updateItemSubtotal(this, ${item.price})">
                <button class="btn-remove" onclick="removeItem(this)">Remove</button>
            </div>
        `;
        itemsContainer.appendChild(itemRow);
    });

    orderModal.style.display = "block";
}

// 切換編輯模式：顯示/隱藏輸入框與刪除按鈕 
function toggleEditMode() {
    const controls = document.querySelectorAll(".item-edit-controls");
    const qtyTexts = document.querySelectorAll(".qty-text");
    const btn = document.getElementById("edit-items-btn");
    
    const isEditing = btn.innerText === "Edit Items";
    
    controls.forEach(el => el.style.display = isEditing ? "flex" : "none");
    qtyTexts.forEach(el => el.style.display = isEditing ? "none" : "inline");
    
    btn.innerText = isEditing ? "Finish Editing" : "Edit Items";
    btn.style.background = isEditing ? "#27ae60" : "#34495e";
}

// 即時更新商品小計 [cite: 317]
function updateItemSubtotal(input, price) {
    const row = input.closest('.item-row');
    const subtotalEl = row.querySelector('.item-subtotal');
    subtotalEl.innerText = "$" + (input.value * price).toFixed(2);
}

// 移除商品
function removeItem(btn) {
    if(confirm("Confirm to remove this item from order?")) {
        btn.closest('.item-row').remove();
    }
}

// 根據狀態改變 Header 顏色 
function updateStatusUI(val) {
    const header = document.querySelector(".card-header");
    const colors = { "1": "#3498db", "2": "#27ae60", "3": "#e74c3c" };
    header.style.borderBottom = `4px solid ${colors[val] || "#2c3e50"}`;
}

function closeOrderModal() {
    orderModal.style.display = "none";
    // 關閉時重置編輯按鈕狀態
    const btn = document.getElementById("edit-items-btn");
    btn.innerText = "Edit Items";
    btn.style.background = "#34495e";
}

// 點擊 Modal 外部關閉
window.onclick = function(event) {
    if (event.target == orderModal) closeOrderModal();
}