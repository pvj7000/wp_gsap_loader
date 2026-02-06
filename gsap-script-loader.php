<?php
/**
 * Plugin Name: GSAP Script Loader
 * Description: Easily manage and enqueue GSAP 3 scripts via CDN.
 * Version: 1.0.0
 * Author: PVJ
 * Author URI: https://example.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include data file
require_once plugin_dir_path(__FILE__) . 'includes/plugins-data.php';

class GSAP_Script_Loader
{

    private $option_name = 'gsap_sl_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('wp_ajax_gsap_sl_save_setting', [$this, 'ajax_save_setting']);
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'GSAP Loader',
            'GSAP Loader',
            'manage_options',
            'gsap-script-loader',
            [$this, 'render_settings_page'],
            'dashicons-media-code'
        );
    }

    public function ajax_save_setting()
    {
        check_ajax_referer('gsap_sl_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $plugin_handle = isset($_POST['plugin_handle']) ? sanitize_text_field($_POST['plugin_handle']) : '';
        $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '0';

        if (!$plugin_handle) {
            wp_send_json_error('Invalid plugin');
        }

        // Get current settings
        $options = get_option($this->option_name, []);

        // Update specific plugin
        if ($state === '1') {
            $options[$plugin_handle] = '1';
        } else {
            unset($options[$plugin_handle]);
        }

        update_option($this->option_name, $options);

        wp_send_json_success(['handle' => $plugin_handle, 'state' => $state]);
    }

    public function register_settings()
    {
        register_setting('gsap_sl_settings_group', $this->option_name);
    }

    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_gsap-script-loader') {
            return;
        }

        wp_enqueue_style('gsap-sl-admin-css', plugin_dir_url(__FILE__) . 'admin/ui.css', [], '1.0.0');
        wp_enqueue_script('gsap-sl-admin-js', plugin_dir_url(__FILE__) . 'admin/ui.js', [], '1.0.0', true);
        wp_localize_script('gsap-sl-admin-js', 'gsapVal', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gsap_sl_nonce')
        ]);
    }

    public function render_settings_page()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
    }

    public function enqueue_frontend_scripts()
    {
        $options = get_option($this->option_name, []);
        $plugins = gsap_sl_get_plugins();

        // Check if GSAP Core is active or needed
        $core_active = isset($options['gsap-core']) && $options['gsap-core'] === '1';

        // Loop and enqueue
        foreach ($plugins as $key => $plugin) {
            // Skip if not active
            if (!isset($options[$key]) || $options[$key] !== '1') {
                continue;
            }

            $deps = [];
            // If this is NOT core, it likely depends on core
            if ($key !== 'gsap-core') {
                if ($core_active) {
                    $deps[] = 'gsap-core';
                }
            }

            // Add specific plugin dependencies
            if (isset($plugin['requires']) && !empty($plugin['requires'])) {
                $deps[] = $plugin['requires'];
            }

            wp_enqueue_script(
                $plugin['handle'],
                $plugin['url'],
                $deps,
                '3.14.1',
                true // In footer
            );
        }
    }
}

new GSAP_Script_Loader();
