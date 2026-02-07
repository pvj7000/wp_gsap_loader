<div class="wrap gs-wrap">
    <div class="gs-header">
        <div class="gs-header-left">
            <div class="gs-title">
                <span class="gs-logo-icon"></span> GSAP Script Loader
            </div>
            <a href="https://gsap.com/docs/v3/Plugins" target="_blank" class="gs-docs-link">Official GSAP Plugin
                Documentation &nearr;</a>
        </div>

        <div class="gs-header-right">
            <div class="gs-version-pill" title="Resolved automatically from cdnjs">
                cdnjs GSAP: <strong><?php echo esc_html($gsap_sl_resolved_version ?? ''); ?></strong>
            </div>
            <button type="button" class="button" id="gs-refresh-cdn">
                Refresh CDN data
            </button>
            <div class="gs-search">
                <input type="text" id="gs-search-input" placeholder="Search plugins..."
                    style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 250px;">
            </div>
        </div>
    </div>

    <form onsubmit="return false;">
        <?php
        $options = get_option('gsap_sl_settings', []);
        $plugins = gsap_sl_get_plugins();
        $externally_enqueued = get_option('gsap_sl_external_enqueues', []);
        if (!is_array($externally_enqueued)) {
            $externally_enqueued = [];
        }
        $key_to_name = array_column($plugins, 'name');
        ?>

        <div class="gs-grid" id="gs-plugin-grid">
            <?php foreach ($plugins as $key => $plugin):
                $is_required = isset($plugin['required']) && $plugin['required'] === true;
                $is_external = in_array($key, $externally_enqueued, true);
                $checked = $is_external || $is_required || (isset($options[$key]) && $options[$key] === '1');
                $is_toggle_locked = $is_required || $is_external;

                $requires = (array) ($plugin['requires'] ?? []);
                $requires_attr = !empty($requires) ? implode(',', array_map('sanitize_key', $requires)) : '';

                // Human label for dependencies (omit gsap-core to reduce noise)
                $requires_labels = [];
                foreach ($requires as $req_key) {
                    if ($req_key === 'gsap-core') {
                        continue;
                    }
                    $requires_labels[] = $plugins[$req_key]['name'] ?? $req_key;
                }
                $requires_label = implode(' + ', $requires_labels);
                ?>

                <div class="gs-card" data-name="<?php echo esc_attr(strtolower($plugin['name'])); ?>"
                    style="--hover-color: <?php echo esc_attr($plugin['color']); ?>;">
                    <div class="gs-card-header">
                        <div class="gs-header-text">
                            <span class="gs-cat-label"
                                style="background-color: <?php echo esc_attr($plugin['color']); ?>20; color: <?php echo esc_attr($plugin['color']); ?>;">
                                <?php echo esc_html($plugin['category']); ?>
                            </span>
                            <h3 class="gs-plugin-name"><?php echo esc_html($plugin['name']); ?></h3>
                        </div>

                        <label class="gs-toggle">
                            <input
                                type="checkbox"
                                class="gs-plugin-toggle"
                                data-handle="<?php echo esc_attr($key); ?>"
                                <?php echo $requires_attr !== '' ? 'data-requires="' . esc_attr($requires_attr) . '"' : ''; ?>
                                <?php echo $is_required ? 'data-required="1"' : ''; ?>
                                <?php echo $is_external ? 'data-external="1"' : ''; ?>
                                <?php echo $is_toggle_locked ? 'disabled' : ''; ?>
                                <?php checked($checked, true); ?>
                            >
                            <span class="gs-slider"></span>
                        </label>
                    </div>

                    <p class="gs-plugin-desc"><?php echo esc_html($plugin['description']); ?></p>

                    <?php if ($requires_label !== ''): ?>
                        <div class="gs-requires">
                            Requires: <?php echo esc_html($requires_label); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_external): ?>
                        <div class="gs-external-notice">
                            Enqueued elsewhere
                        </div>
                    <?php endif; ?>

                    <div class="gs-meta" style="font-size:0.8rem; color:#aaa;">
                        <?php echo esc_html($plugin['filename']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
    <div class="gs-footer-note">
  The GSAP Loader Plugin is not affiliated with GSAP or Webflow. Have fun building amazing animations.
</div>

</div>
