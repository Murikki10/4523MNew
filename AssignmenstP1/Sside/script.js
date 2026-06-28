/* Shared JavaScript for Premium Living Staff Portal 
   Handles Modal logic and Material Selection for Furniture Management
*/

const materialModal = document.getElementById("materialModal");

// Open the Material Selection Modal
function openModal() {
    if (materialModal) {
        materialModal.style.display = "block";
    }
}

// Close the Material Selection Modal
function closeModal() {
    if (materialModal) {
        materialModal.style.display = "none";
    }
}

// Process selected materials from the modal checklist and render them as dynamic form tags
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
            
            // Generate tags with hidden array inputs to allow seamless PHP back-end transaction storage
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

// Safely close modal when clicking anywhere outside of the modal window content container
window.onclick = function(event) {
    if (event.target == materialModal) {
        closeModal();
    }
}