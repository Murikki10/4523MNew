/* ==========================================================================
   Premium Living Staff Portal - Integrated Furniture & Material Modal Engine
   ========================================================================== */

// ---------------------------------------------------------
// SECTION 1: Slide-in Floating Toast Notifications (右下角滑入通知)
// ---------------------------------------------------------
document.addEventListener("DOMContentLoaded", function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        // 1. 延遲 100 毫秒觸發滑入，確保瀏覽器能順利渲染 transition 動畫
        setTimeout(function() {
            alert.classList.add('toast-show');
        }, 100);

        // 2. 停留 3000 毫秒 (3秒) 後自動執行滑出淡出
        setTimeout(function() {
            alert.classList.remove('toast-show');
            alert.classList.add('toast-hide');
            
            // 3. 當滑出動畫行完之後 (0.4秒後)，徹底將網頁元件拔除
            setTimeout(function() {
                alert.remove();
            }, 400);
        }, 3000); 
    });
});


// ---------------------------------------------------------
// SECTION 2: Material Selection Modal (For Adding Furniture)
// ---------------------------------------------------------

// Open the Material Selection Modal
function openModal() {
    const materialModal = document.getElementById("materialModal");
    if (materialModal) {
        materialModal.style.display = "block";
    }
}

// Close the Material Selection Modal
function closeModal() {
    const materialModal = document.getElementById("materialModal");
    if (materialModal) {
        materialModal.style.display = "none";
    }
}

// Process selected materials from the checklist and render tags
function confirmSelection() {
    const displayContainer = document.getElementById("selected-materials-display");
    const checkboxes = document.querySelectorAll(".mat-check");
    const quantities = document.querySelectorAll(".mat-qty");
    
    let renderedHtml = "";
    let hasSelection = false;

    checkboxes.forEach((cb, index) => {
        if (cb.checked) {
            const qty = quantities[index].value || 1;
            const name = cb.getAttribute("data-name");
            
            renderedHtml += `
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

    if (displayContainer) {
        displayContainer.innerHTML = hasSelection ? renderedHtml : '<span style="color: #95a5a6; font-size: 14px;">No materials selected. Click the button below to configure...</span>';
    }
    closeModal();
}


// ---------------------------------------------------------
// SECTION 3: Furniture Edit Details Popup Modal
// ---------------------------------------------------------

// Opens the Edit Furniture Modal and populates data safely
function triggerFurnitureEditModal(buttonElement) {
    const fid = buttonElement.getAttribute('data-fid');
    const name = buttonElement.getAttribute('data-fname');
    const price = buttonElement.getAttribute('data-fprice');
    const desc = buttonElement.getAttribute('data-fdesc');
    const rawMaterials = buttonElement.getAttribute('data-raw-materials');

    // Fill inputs inside edit popup container
    document.getElementById('edit-fid').value = fid;
    document.getElementById('edit-title-id').innerText = '(ID #' + String(fid).padStart(3, '0') + ')';
    document.getElementById('edit-fname').value = name;
    document.getElementById('edit-fprice').value = price;
    document.getElementById('edit-fdesc').value = desc;

    // Reset all checkboxes and quantities inside edit grid
    const checkboxes = document.querySelectorAll('.edit-mat-check');
    const quantities = document.querySelectorAll('.edit-mat-qty');
    checkboxes.forEach(cb => cb.checked = false);
    quantities.forEach(qtyInput => qtyInput.value = 1);

    // Auto-check and fill quantities based on database group concat string
    if (rawMaterials && rawMaterials.trim() !== "") {
        const materialPairs = rawMaterials.split(',');
        materialPairs.forEach(pair => {
            const parts = pair.split(':');
            const mid = parts[0];
            const qty = parts[1];

            const targetedCheckbox = document.getElementById('edit-cb-' + mid);
            if (targetedCheckbox) {
                targetedCheckbox.checked = true;
            }

            const targetedQtyInput = document.getElementById('edit-qty-' + mid);
            if (targetedQtyInput) {
                targetedQtyInput.value = qty;
            }
        });
    }

    // Display the edit modal window
    document.getElementById('editFurnitureModal').style.display = 'block';
}

// Closes the Edit Furniture Modal cleanly
function closeFurnitureEditModal() {
    const editModal = document.getElementById('editFurnitureModal');
    if (editModal) {
        editModal.style.display = 'none';
    }
}


// ---------------------------------------------------------
// SECTION 4: Global Window Click Interceptor (Background Close)
// ---------------------------------------------------------

// Safely handles background clicks for BOTH modals to prevent conflicts
window.onclick = function(event) {
    const materialModal = document.getElementById("materialModal");
    const editModal = document.getElementById("editFurnitureModal");

    if (event.target == materialModal) {
        closeModal();
    }
    if (event.target == editModal) {
        closeFurnitureEditModal();
    }
}