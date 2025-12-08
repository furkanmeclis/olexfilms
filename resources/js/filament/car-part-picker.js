/**
 * Car Part Picker Component
 * Handles SVG interaction and synchronization with tree view
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize car part picker functionality
    initializeCarPartPicker();
});

function initializeCarPartPicker() {
    const pickers = document.querySelectorAll('.car-part-picker');

    pickers.forEach(picker => {
        const svg = picker.querySelector('svg');
        if (!svg) return;

        // Sync checkbox state with SVG selection
        const checkboxes = picker.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const partId = this.value;
                const path = svg.querySelector(`path[id="${partId}"]`);
                if (path && window.Alpine) {
                    const component = Alpine.$data(picker);
                    if (component && component.updatePartColor) {
                        component.updatePartColor(partId, path);
                    }
                }
            });
        });
    });
}

// Export for use in other scripts if needed
window.CarPartPicker = {
    initialize: initializeCarPartPicker
};

