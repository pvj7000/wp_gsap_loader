<div class="wrap gs-wrap">
    <div class="gs-header">
        <div class="gs-header-left">
            <div class="gs-title">
                <span class="gs-logo-icon"></span> GSAP Script Loader
            </div>
            <a href="https://gsap.com/docs/v3/Plugins" target="_blank" class="gs-docs-link">Official Plugin
                Documentation &nearr;</a>
        </div>
        <div class="gs-search">
            <input type="text" id="gs-search-input" placeholder="Search plugins..."
                style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 250px;">
        </div>
    </div>

    <form onsubmit="return false;">
        <?php
        // settings_fields( 'gsap_sl_settings_group' ); // Not strictly needed for AJAX but keeps nonce fields if we wanted standard save backup. We'll use our own nonce.
        $options = get_option('gsap_sl_settings', []);
        $plugins = gsap_sl_get_plugins();
        $handle_to_name = array_column($plugins, 'name', 'handle');
        ?>

        <div class="gs-grid" id="gs-plugin-grid">
            <?php foreach ($plugins as $key => $plugin):
                $checked = isset($options[$key]) && $options[$key] === '1';
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
                            <input type="checkbox" class="gs-plugin-toggle" data-handle="<?php echo esc_attr($key); ?>" 
                                <?php echo isset($plugin['requires']) ? 'data-requires="' . esc_attr($plugin['requires']) . '"' : ''; ?>
                                <?php checked($checked, true); ?>>
                            <span class="gs-slider"></span>
                        </label>
                    </div>
                    <p class="gs-plugin-desc"><?php echo esc_html($plugin['description']); ?></p>
                    <?php if (isset($plugin['requires'])): ?>
                        <div class="gs-requires">
                            Requires: <?php echo esc_html($handle_to_name[$plugin['requires']] ?? $plugin['requires']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="gs-meta" style="font-size:0.8rem; color:#aaa;">
                        <?php echo esc_html($plugin['filename']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>
