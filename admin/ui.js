document.addEventListener('DOMContentLoaded', function () {
    // Search Functionality
    const searchInput = document.getElementById('gs-search-input');
    const cards = document.querySelectorAll('.gs-card');

    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            const query = e.target.value.toLowerCase();

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Auto-Save Functionality
    const toggles = document.querySelectorAll('.gs-plugin-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function () {
            const handle = this.getAttribute('data-handle');
            const isChecked = this.checked ? '1' : '0';
            const requiredHandle = this.getAttribute('data-requires');

            // Dependency Logic
            // 1. If Plugin B (dependent) is switched ON -> Plugin A (dependency) must turn ON.
            if (isChecked === '1' && requiredHandle) {
                const requiredToggle = document.querySelector(`.gs-plugin-toggle[data-handle="${requiredHandle}"]`);
                if (requiredToggle && !requiredToggle.checked) {
                    requiredToggle.checked = true;
                    // Trigger change to save the dependency's new state
                    requiredToggle.dispatchEvent(new Event('change'));
                }
            }

            // 2. If Plugin A (dependency) is switched OFF -> Plugin B (dependent) must turn OFF.
            if (isChecked === '0') {
                // Find all plugins that require THIS plugin
                const dependents = document.querySelectorAll(`.gs-plugin-toggle[data-requires="${handle}"]`);
                dependents.forEach(dependent => {
                    if (dependent.checked) {
                        dependent.checked = false;
                        // Trigger change to save the dependent's new state
                        dependent.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Optional: Add visual loading state (e.g., opacity)
            const slider = this.nextElementSibling;
            slider.style.opacity = '0.5';

            const formData = new FormData();
            formData.append('action', 'gsap_sl_save_setting');
            formData.append('nonce', gsapVal.nonce);
            formData.append('plugin_handle', handle);
            formData.append('state', isChecked);

            fetch(gsapVal.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    // Restore opacity on success
                    slider.style.opacity = '1';
                    if (!data.success) {
                        alert('Error saving setting.');
                        // Revert toggle if failed
                        this.checked = !this.checked;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    slider.style.opacity = '1';
                    alert('Network error.');
                    this.checked = !this.checked;
                });
        });
    });
});
