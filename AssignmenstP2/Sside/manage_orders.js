/* ==========================================================================
   Premium Living Staff Portal - Advanced ERP Orders Management Engine
   (全功能交叉聯動：Omni 搜尋框、新增獨立 Status 欄位篩選、原本多條件排序、彈窗引擎)
   ========================================================================== */

let currentOrderItems = [];
let editModeActive = false;

// --------------------------------------------------------------------------
// SECTION 1: 🌐 Omni Search Box + NEW Status Filter + Existing Sort Logic
// --------------------------------------------------------------------------
function filterAndSortOrders() {
    const searchKeyword = document.getElementById('order-search-input').value.toLowerCase().trim();
    // 🚀 抓取新加入的「Filter by Status」下拉選單數值
    const statusFilter = document.getElementById('order-status-filter').value;
    // 抓取原本的「Sort Order By」排序選單數值
    const sortValue = document.getElementById('order-sort-select').value;
    const tableBody = document.querySelector('table tbody');
    
    if (!tableBody) return;

    const rows = Array.from(tableBody.querySelectorAll('.order-data-row'));
    const noResultsRow = tableBody.querySelector('.no-results-row');
    if (noResultsRow) noResultsRow.remove();

    let visibleCount = 0;

    // 1. 雙重交叉過濾：Omni Search 聯動 Status Filter 篩選
    rows.forEach(row => {
        const orderId = row.getAttribute('data-oid') || "";
        const formattedId = "ord-" + String(orderId).padStart(4, '0');
        const customerName = row.getAttribute('data-cname') || "";
        const customerTel = row.getAttribute('data-ctel') || "";
        const furnitureItems = row.getAttribute('data-furnitures') || "";
        const rowStatus = row.getAttribute('data-ostatus') || ""; // 訂單本身的真實狀態

        // 條件 A：檢查是否匹配 Omni 搜尋關鍵字
        const matchesKeyword = (
            orderId.includes(searchKeyword) || 
            formattedId.includes(searchKeyword) ||
            customerName.includes(searchKeyword) || 
            customerTel.includes(searchKeyword) || 
            furnitureItems.includes(searchKeyword)
        );

        // 條件 B：檢查是否符合新狀態選單篩選（如果是 "all" 則免篩選）
        const matchesStatus = (statusFilter === 'all' || rowStatus === statusFilter);

        // 🚀 兩大條件同時成立，該列才顯示
        if (matchesKeyword && matchesStatus) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });

    // 2. 原本的 Short 排序算法 (100% 完整保留)
    rows.sort((rowA, rowB) => {
        if (sortValue === 'date-desc') return Number(rowB.getAttribute('data-timestamp')) - Number(rowA.getAttribute('data-timestamp'));
        if (sortValue === 'date-asc') return Number(rowA.getAttribute('data-timestamp')) - Number(rowB.getAttribute('data-timestamp'));
        if (sortValue === 'amount-desc') return Number(rowB.getAttribute('data-amount')) - Number(rowA.getAttribute('data-amount'));
        if (sortValue === 'amount-asc') return Number(rowA.getAttribute('data-amount')) - Number(rowB.getAttribute('data-amount'));

        if (sortValue === 'status-asc' || sortValue === 'status-desc') {
            let statusA = Number(rowA.getAttribute('data-ostatus'));
            let statusB = Number(rowB.getAttribute('data-ostatus'));

            if (sortValue === 'status-asc') {
                let weightA = statusA === 0 ? 99 : statusA;
                let weightB = statusB === 0 ? 99 : statusB;
                return weightA - weightB;
            }
            if (sortValue === 'status-desc') {
                let weightA = statusA === 0 ? -1 : statusA;
                let weightB = statusB === 0 ? -1 : statusB;
                return weightB - weightA;
            }
        }
        return 0;
    });

    // 3. 將最終結果重掛回 DOM 表格
    rows.forEach(row => tableBody.appendChild(row));

    if (visibleCount === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'no-results-row';
        emptyRow.innerHTML = `<td colspan="100%">No orders matching current filters found inside logs.</td>`;
        tableBody.appendChild(emptyRow);
    }
}

// --------------------------------------------------------------------------
// SECTION 2: Advanced Dialog Modal & Data Aggregation Renderer
// --------------------------------------------------------------------------
function handleRowClick(rowElement) {
    const orderData = {
        oid: rowElement.getAttribute('data-oid'),
        odate: rowElement.getAttribute('data-odate'),
        cname: rowElement.getAttribute('data-cname'),
        ctel: rowElement.getAttribute('data-ctel'), 
        odeliveraddress: rowElement.getAttribute('data-caddr'), 
        ddate: rowElement.getAttribute('data-ddate'),
        ostatus: rowElement.getAttribute('data-ostatus'),
        items: JSON.parse(rowElement.getAttribute('data-items'))
    };
    openOrderModal(orderData);
}

function openOrderModal(order) {
    document.getElementById('modal-hidden-oid').value = order.oid;
    document.getElementById('modal-oid').innerText = '#ORD-' + String(order.oid).padStart(4, '0');
    document.getElementById('modal-cname').innerText = order.cname.toUpperCase();
    document.getElementById('modal-odate').innerText = order.odate;
    document.getElementById('modal-ctel').innerText = order.ctel;
    document.getElementById('modal-caddr').innerText = order.odeliveraddress;
    document.getElementById('modal-ddate').innerText = order.ddate;
    document.getElementById('modal-status-select').value = order.ostatus;

    const currentStatus = parseInt(order.ostatus);
    document.querySelectorAll('.timeline-step').forEach(step => step.className = 'timeline-step');
    
    if (currentStatus === 0) {
        document.getElementById('step-0').classList.add('active');
    } else {
        const statuses = [2, 3, 4, 5];
        statuses.forEach(st => {
            const el = document.getElementById('step-' + st);
            if (!el) return;
            if (st < currentStatus) el.classList.add('completed');
            if (st === currentStatus) el.classList.add('active');
        });
    }

    currentOrderItems = Array.isArray(order.items) ? JSON.parse(JSON.stringify(order.items)) : [];
    editModeActive = false;

    const btn = document.getElementById("edit-items-btn");
    if (btn) { btn.innerText = "Edit Items"; btn.style.background = "#34495e"; }

    renderModalItems();
    document.getElementById('orderDetailModal').style.display = 'block';
}

function closeOrderModal() {
    document.getElementById('orderDetailModal').style.display = 'none';
}

function renderModalItems() {
    const container = document.getElementById('modal-items-container');
    container.innerHTML = '';

    if (currentOrderItems.length === 0) {
        container.innerHTML = '<p style="color:#7f8c8d; font-size:14px; text-align:center; padding:10px;">No items inside this order.</p>';
        calculateModalTotal();
        return;
    }

    let totalMaterialsAccumulator = {};

    currentOrderItems.forEach((item, index) => {
        const itemQty = parseInt(item.oqty);
        const subtotal = item.fprice * itemQty;
        const displayStyle = editModeActive ? 'flex' : 'none';

        let recipeHtml = "";
        if (item.materials_recipe && item.materials_recipe.trim() !== "") {
            recipeHtml += `<div class="material-box"><strong>Formula Recipe Mapping (1 unit demands):</strong>`;
            
            const rawPairs = item.materials_recipe.split('||');
            rawPairs.forEach(pair => {
                const parts = pair.split(':');
                const mname = parts[0];
                const pmqty = parseInt(parts[1]);
                const mqtyStock = parseInt(parts[2]);
                const munit = parts[3];

                const totalNeededForThisItem = pmqty * itemQty;

                if (!totalMaterialsAccumulator[mname]) {
                    totalMaterialsAccumulator[mname] = { needed: 0, stock: mqtyStock, unit: munit };
                }
                totalMaterialsAccumulator[mname].needed += totalNeededForThisItem;

                const isShortage = mqtyStock < totalNeededForThisItem;
                const badgeClass = isShortage ? 'stock-badge stock-warn' : 'stock-badge stock-ok';
                const badgeText = isShortage ? `⚠️ Lack (In Stock: ${mqtyStock})` : `✓ In Stock (${mqtyStock})`;

                recipeHtml += `
                    <div class="material-item">
                        <span>• ${mname}: ${pmqty} ${munit} × ${itemQty} = <strong>${totalNeededForThisItem} ${munit}</strong></span>
                        <span class="${badgeClass}">${badgeText}</span>
                    </div>`;
            });
            recipeHtml += `</div>`;
        }

        const imagePath = `../Sample Furniture Images/${item.fid}.png`;

        const rowHTML = `
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:12px; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
                <div class="item-row" id="item-row-${index}" style="display:flex; justify-content:space-between; align-items:center;">
                    <input type="hidden" name="fids[]" value="${item.fid}">
                    <div style="display:flex; align-items:center; gap:12px; flex:1;">
                        <img src="${imagePath}" onerror="this.src='../Sample Furniture Images/1.png'" style="width:55px; height:55px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0;">
                        <div style="display:flex; flex-direction:column;">
                            <span style="font-weight:bold; color:#1e293b; font-size:14px;">${item.fname} <small style="color:#64748b; font-weight:normal;">(ID: #F${String(item.fid).padStart(3,'0')})</small></span>
                            <span style="font-size:12px; color:#64748b; margin-top:2px;">Unit Price: $${parseFloat(item.fprice).toFixed(2)} each</span>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:20px;">
                        <div id="subtotal-text-${index}" style="font-weight:bold; color:#0f172a; font-size:15px;">$${subtotal.toFixed(2)}</div>
                        <div class="item-edit-controls" id="edit-controls-${index}" style="display: ${displayStyle}; align-items:center; gap:6px;">
                            <input type="number" class="edit-qty" name="oqtys[]" value="${itemQty}" min="1" style="width:50px; padding:4px; text-align:center; border:1px solid #cbd5e1; border-radius:4px;" oninput="updateItemQty(${index}, this.value)">
                            <button type="button" style="background:#ef4444; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:11px;" onclick=\"removeItem(${index})\">Remove</button>
                        </div>
                    </div>
                </div>
                ${recipeHtml}
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHTML);
    });

    const summaryContainer = document.getElementById('modal-total-materials-summary');
    summaryContainer.innerHTML = '';
    
    const accumulatorKeys = Object.keys(totalMaterialsAccumulator);
    if(accumulatorKeys.length === 0) {
        summaryContainer.innerHTML = '<span style="color:#64748b;">No materials requested.</span>';
    } else {
        accumulatorKeys.forEach(mname => {
            const data = totalMaterialsAccumulator[mname];
            const isShortage = data.stock < data.needed;
            const styleColor = isShortage ? 'color: #ef4444; font-weight: bold;' : 'color: #1e293b;';
            const warnText = isShortage ? ` ❌ (Deficit: Lack ${data.needed - data.stock} ${data.unit}!)` : ' ✓';

            summaryContainer.innerHTML += `
                <div style="background:#fff; padding:8px; border-radius:4px; border:1px solid #e2e8f0; ${styleColor}">
                    <strong>${mname}</strong>: ${data.needed} ${data.unit} Required <br>
                    <small style="color:#64748b;">Available Current Stock: ${data.stock} ${data.unit}</small>${warnText}
                </div>`;
        });
    }

    calculateModalTotal();
}

function toggleEditMode() {
    const btn = document.getElementById("edit-items-btn");
    editModeActive = !editModeActive;

    currentOrderItems.forEach((item, index) => {
        const controls = document.getElementById(`edit-controls-${index}`);
        if (controls) controls.style.display = editModeActive ? "flex" : "none";
    });

    btn.innerText = editModeActive ? "Finish Editing" : "Edit Items";
    btn.style.background = editModeActive ? "#27ae60" : "#34495e";
}

function updateItemQty(index, val) {
    const quantity = parseInt(val);
    if (isNaN(quantity) || quantity <= 0) return;

    currentOrderItems[index].oqty = quantity;
    calculateModalTotal();
    renderModalItems();
}

function removeItem(index) {
    if (confirm("Are you sure you want to remove this furniture product from this order?")) {
        currentOrderItems.splice(index, 1);
        if (editModeActive) editModeActive = false;
        renderModalItems();
    }
}

function calculateModalTotal() {
    let grandTotal = 0;
    currentOrderItems.forEach(item => { grandTotal += item.fprice * parseInt(item.oqty); });
    
    const subtotalEl = document.getElementById('modal-subtotal-price');
    const totalEl = document.getElementById('modal-total');
    
    if (subtotalEl) subtotalEl.innerText = '$' + grandTotal.toFixed(2);
    if (totalEl) totalEl.innerText = '$' + grandTotal.toFixed(2);
}

window.onclick = function (event) {
    const modal = document.getElementById('orderDetailModal');
    if (event.target == modal) closeOrderModal();
}

// SECTION 3: Auto-Dismiss Toast Controller
document.addEventListener("DOMContentLoaded", function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() { alert.classList.add('toast-show'); }, 100);
        setTimeout(function() {
            alert.classList.remove('toast-show');
            alert.classList.add('toast-hide');
            setTimeout(function() { alert.remove(); }, 400);
        }, 3000); 
    });
});