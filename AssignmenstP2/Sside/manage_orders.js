/* ==========================================================================
   Premium Living Staff Portal - Orders Management Dynamic Controller Engine
   (整合功能：搜尋排序篩選、正確 ctel 欄位對應、彈窗內家具明細數量修改與總數計算)
   ========================================================================== */

// 內部全域變數，用作安全儲存當前彈窗內正在修改的家具清單與編輯狀態
let currentOrderItems = [];
let editModeActive = false;


// --------------------------------------------------------------------------
// SECTION 1: 🌐 Core Extension - Instant Search & Sort Engine (免重載篩選)
// --------------------------------------------------------------------------
function filterAndSortOrders() {
    const searchKeyword = document.getElementById('order-search-input').value.toLowerCase().trim();
    const sortValue = document.getElementById('order-sort-select').value;
    const tableBody = document.querySelector('table tbody');
    
    if (!tableBody) return;

    // 1. 獲取所有帶有數據的訂單行，並移除上一次留低的「找不到結果」提示行
    const rows = Array.from(tableBody.querySelectorAll('.order-data-row'));
    const noResultsRow = tableBody.querySelector('.no-results-row');
    if (noResultsRow) noResultsRow.remove();

    let visibleCount = 0;

    // 2. 執行 Search 搜尋模糊篩選
    rows.forEach(row => {
        const customerName = row.getAttribute('data-customer') || "";
        
        if (customerName.includes(searchKeyword)) {
            row.style.display = ""; // 顯示符合條件的行
            visibleCount++;
        } else {
            row.style.display = "none"; // 隱藏不符合條件的行
        }
    });

    // 3. 執行 Sort 排序演算法
    rows.sort((rowA, rowB) => {
        if (sortValue === 'date-desc') {
            return Number(rowB.getAttribute('data-timestamp')) - Number(rowA.getAttribute('data-timestamp'));
        }
        if (sortValue === 'date-asc') {
            return Number(rowA.getAttribute('data-timestamp')) - Number(rowB.getAttribute('data-timestamp'));
        }
        if (sortValue === 'amount-desc') {
            return Number(rowB.getAttribute('data-amount')) - Number(rowA.getAttribute('data-amount'));
        }
        if (sortValue === 'amount-asc') {
            return Number(rowA.getAttribute('data-amount')) - Number(rowB.getAttribute('data-amount'));
        }
        return 0;
    });

    // 4. 將排序完畢的 DOM 節點重新依序丟回 HTML 表格內
    rows.forEach(row => tableBody.appendChild(row));

    // 5. 如果搜尋結果是 0，動態插入一行漂亮的無結果提示
    if (visibleCount === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'no-results-row';
        emptyRow.innerHTML = `<td colspan="100%">No orders matching "${searchKeyword}" found.</td>`;
        tableBody.appendChild(emptyRow);
    }
}


// --------------------------------------------------------------------------
// SECTION 2: Core Order Details Modal Controller (彈窗資料對應與渲染)
// --------------------------------------------------------------------------

// 當職員點擊表格中某一列（Row）時觸發
function handleRowClick(rowElement) {
    const orderData = {
        oid: rowElement.getAttribute('data-oid'),
        cname: rowElement.getAttribute('data-cname'),
        // 🚀 關鍵配套修正：由 data-cphone 改為讀取符合你 DB 架構的 data-ctel
        ctel: rowElement.getAttribute('data-ctel'), 
        // 🚀 關鍵配套修正：讀取送貨地址 data-caddr
        odeliveraddress: rowElement.getAttribute('data-caddr'), 
        ostatus: rowElement.getAttribute('data-ostatus'),
        items: JSON.parse(rowElement.getAttribute('data-items'))
    };
    openOrderModal(orderData);
}

// 將抽出來的訂單數據塞入 Popup 彈窗表單的欄位中
function openOrderModal(order) {
    document.getElementById('modal-hidden-oid').value = order.oid;
    document.getElementById('modal-oid').innerText = '#' + String(order.oid).padStart(4, '0');
    document.getElementById('modal-cname').innerText = order.cname;
    document.getElementById('modal-ctel').innerText = order.ctel;
    document.getElementById('modal-caddr').innerText = order.odeliveraddress;
    document.getElementById('modal-status-select').value = order.ostatus;

    // 執行深度複製（Deep Copy），防止職員未儲存修改就直接破壞原始陣列數據
    currentOrderItems = Array.isArray(order.items) ? JSON.parse(JSON.stringify(order.items)) : [];
    editModeActive = false;

    const btn = document.getElementById("edit-items-btn");
    if (btn) {
        btn.innerText = "Edit Items";
        btn.style.background = "#34495e";
    }

    renderModalItems();
    document.getElementById('orderDetailModal').style.display = 'block';
}

// 關閉訂單詳情彈窗
function closeOrderModal() {
    document.getElementById('orderDetailModal').style.display = 'none';
}

// 動態在彈窗內 Loop 出目前訂單買咗啲咩家具，並渲染成 HTML
function renderModalItems() {
    const container = document.getElementById('modal-items-container');
    container.innerHTML = '';

    if (currentOrderItems.length === 0) {
        container.innerHTML = '<p style="color:#7f8c8d; font-size:14px; text-align:center; padding:10px;">No items inside this order.</p>';
        calculateModalTotal();
        return;
    }

    currentOrderItems.forEach((item, index) => {
        const subtotal = item.fprice * item.oqty;
        const displayStyle = editModeActive ? 'flex' : 'none';

        const rowHTML = `
            <div class="item-row" id="item-row-${index}" style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eee;">
                <input type="hidden" name="fids[]" value="${item.fid}">
                <div class="item-main" style="flex:1; display:flex; justify-content:space-between; align-items:center; padding-right:15px;">
                    <div class="item-info" style="display:flex; flex-direction:column;">
                        <span class="item-name" style="font-weight:bold; color:#2c3e50;">${item.fname}</span>
                        <span class="item-price-detail" style="font-size:12px; color:#7f8c8d;">$${parseFloat(item.fprice).toFixed(2)} each</span>
                    </div>
                    <div class="item-subtotal" id="subtotal-text-${index}" style="font-weight:bold; color:#2c3e50;">$${subtotal.toFixed(2)}</div>
                </div>
                <div class="item-edit-controls" id="edit-controls-${index}" style="display: ${displayStyle}; align-items:center; gap:8px;">
                    <label style="font-size:13px; color:#555;">Qty: </label>
                    <input type="number" class="edit-qty" name="oqtys[]" value="${item.oqty}" min="1" style="width:60px; padding:4px; text-align:center;" oninput="updateItemQty(${index}, this.value)">
                    <button type="button" class="btn-remove" style="background:#e74c3c; color:white; border:none; padding:4px 8px; border-radius:3px; cursor:pointer; font-size:12px;" onclick="removeItem(${index})">Remove</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHTML);
    });

    calculateModalTotal();
}

// 切換家具項目的「編輯 / 完成」狀態模式
function toggleEditMode() {
    const btn = document.getElementById("edit-items-btn");
    editModeActive = !editModeActive;

    currentOrderItems.forEach((item, index) => {
        const controls = document.getElementById(`edit-controls-${index}`);
        if (controls) {
            controls.style.display = editModeActive ? "flex" : "none";
        }
    });

    btn.innerText = editModeActive ? "Finish Editing" : "Edit Items";
    btn.style.background = editModeActive ? "#27ae60" : "#34495e";
}

// 當職員在彈窗內即時手動修改某個家具的數量（Qty）時觸發
function updateItemQty(index, val) {
    const quantity = parseInt(val);
    if (isNaN(quantity) || quantity <= 0) return;

    currentOrderItems[index].oqty = quantity;
    const subtotal = currentOrderItems[index].fprice * quantity;
    const subtotalText = document.getElementById(`subtotal-text-${index}`);
    if (subtotalText) subtotalText.innerText = '$' + subtotal.toFixed(2);

    calculateModalTotal();
}

// 從訂單中移除某樣家具項目
function removeItem(index) {
    if (confirm("Are you sure you want to remove this furniture product from this order?")) {
        currentOrderItems.splice(index, 1);
        renderModalItems();
        if (editModeActive) {
            editModeActive = false;
            toggleEditMode();
        }
    }
}

// 即時加總彈窗內所有家具小計，算出最新的總金額（Grand Total）
function calculateModalTotal() {
    let grandTotal = 0;
    currentOrderItems.forEach(item => {
        grandTotal += item.fprice * item.oqty;
    });
    const totalEl = document.getElementById('modal-total');
    if (totalEl) totalEl.innerText = '$' + grandTotal.toFixed(2);
}


// --------------------------------------------------------------------------
// SECTION 3: Global Window Click Interceptor (點擊背景關閉)
// --------------------------------------------------------------------------
window.onclick = function (event) {
    const modal = document.getElementById('orderDetailModal');
    if (event.target == modal) {
        closeOrderModal();
    }
}