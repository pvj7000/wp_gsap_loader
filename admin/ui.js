document.addEventListener('DOMContentLoaded', function () {
    // Search Functionality
    const searchInput = document.getElementById('gs-search-input');
    const cards = document.querySelectorAll('.gs-card');

    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            const query = (e.target.value || '').toLowerCase();
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                card.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });
    }

    const toggles = Array.from(document.querySelectorAll('.gs-plugin-toggle'));

    const getRequires = (toggle) => {
        const raw = toggle.getAttribute('data-requires');
        if (!raw) return [];
        return raw
            .split(',')
            .map(s => s.trim())
            .filter(Boolean);
    };

    const getToggleByKey = (key) => {
        return document.querySelector(`.gs-plugin-toggle[data-handle="${CSS.escape(key)}"]`);
    };

    const activateToggle = (key) => {
        if (!key) return;
        const t = getToggleByKey(key);
        if (t && !t.checked) {
            t.checked = true;
            t.dispatchEvent(new Event('change'));
        }
    };

    const deactivateToggle = (key) => {
        if (!key) return;
        const t = getToggleByKey(key);
        if (t && t.checked) {
            t.checked = false;
            t.dispatchEvent(new Event('change'));
        }
    };

    const enforceDependenciesOnLoad = () => {
        toggles.forEach(toggle => {
            if (!toggle.checked) return;
            getRequires(toggle).forEach(activateToggle);
        });
    };

    enforceDependenciesOnLoad();

    const saveSetting = (toggle) => {
        const handle = toggle.getAttribute('data-handle');
        const isChecked = toggle.checked ? '1' : '0';

        const slider = toggle.nextElementSibling;
        if (slider) slider.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('action', 'gsap_sl_save_setting');
        formData.append('nonce', gsapVal.nonce);
        formData.append('plugin_handle', handle);
        formData.append('state', isChecked);

        return fetch(gsapVal.ajax_url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (slider) slider.style.opacity = '1';
                if (!data.success) {
                    alert('Error saving setting.');
                    toggle.checked = !toggle.checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (slider) slider.style.opacity = '1';
                alert('Network error.');
                toggle.checked = !toggle.checked;
            });
    };

    toggles.forEach(toggle => {
        toggle.addEventListener('change', function () {
            const handle = this.getAttribute('data-handle');
            const isChecked = this.checked;

            if (!isChecked && handle === 'gsap-core') {
                const confirmed = window.confirm('Deactivating GSAP Core will disable all other modules. Do you want to continue?');
                if (!confirmed) {
                    this.checked = true;
                    return;
                }
            }

            // 1) Switching ON -> switch ON all dependencies.
            if (isChecked) {
                getRequires(this).forEach(activateToggle);
            }

            // 2) Switching OFF -> switch OFF all dependents.
            if (!isChecked) {
                toggles.forEach(other => {
                    if (!other.checked) return;
                    const reqs = getRequires(other);
                    if (reqs.includes(handle)) {
                        deactivateToggle(other.getAttribute('data-handle'));
                    }
                });
            }

            saveSetting(this);
        });
    });

    // Manual refresh of CDN data (clears transients server-side)
    const refreshBtn = document.getElementById('gs-refresh-cdn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            refreshBtn.disabled = true;
            refreshBtn.textContent = 'Refreshing…';

            const formData = new FormData();
            formData.append('action', 'gsap_sl_refresh_cdn_data');
            formData.append('nonce', gsapVal.nonce);

            fetch(gsapVal.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    refreshBtn.disabled = false;
                    refreshBtn.textContent = 'Refresh CDN data';

                    if (!data.success) {
                        alert('Failed to refresh CDN data.');
                        return;
                    }

                    // Simple: reload to update the version pill.
                    window.location.reload();
                })
                .catch(err => {
                    console.error(err);
                    refreshBtn.disabled = false;
                    refreshBtn.textContent = 'Refresh CDN data';
                    alert('Network error.');
                });
        });
    }
});
