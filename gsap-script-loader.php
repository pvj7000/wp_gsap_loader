<?php
/**
 * Plugin Name: GSAP Script Loader
 * Description: Easily enqueue GSAP scripts via CDN.
 * Version: 1.2
 * Author: PVJ
 * Author URI: https://philipjancsy.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include data file
require_once plugin_dir_path(__FILE__) . 'includes/plugins-data.php';

class GSAP_Script_Loader
{
    private const CDNJS_LIBRARY = 'gsap';
    private const CDNJS_API_BASE = 'https://api.cdnjs.com/libraries/';
    private const CDNJS_CDN_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/';

    private const TRANSIENT_VERSION = 'gsap_sl_cdnjs_version';
    private const TRANSIENT_FILES_PREFIX = 'gsap_sl_cdnjs_files_';

    private const OPTION_LAST_KNOWN_VERSION = 'gsap_sl_last_known_version';
    private const OPTION_SETTINGS = 'gsap_sl_settings';
    private const OPTION_EXTERNAL_ENQUEUES = 'gsap_sl_external_enqueues';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('wp_print_scripts', [$this, 'capture_external_enqueues'], 999);

        add_action('wp_ajax_gsap_sl_save_setting', [$this, 'ajax_save_setting']);
        add_action('wp_ajax_gsap_sl_refresh_cdn_data', [$this, 'ajax_refresh_cdn_data']);
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

    public function register_settings()
    {
        register_setting('gsap_sl_settings_group', self::OPTION_SETTINGS);
    }

    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_gsap-script-loader') {
            return;
        }

        wp_enqueue_style('gsap-sl-admin-css', plugin_dir_url(__FILE__) . 'admin/ui.css', [], '1.1.0');
        wp_enqueue_script('gsap-sl-admin-js', plugin_dir_url(__FILE__) . 'admin/ui.js', [], '1.1.0', true);
        wp_localize_script('gsap-sl-admin-js', 'gsapVal', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gsap_sl_nonce'),
            'resolved_version' => $this->get_cdnjs_latest_version(),
        ]);
    }

    public function render_settings_page()
    {
        // Make version available to the settings template.
        $gsap_sl_resolved_version = $this->get_cdnjs_latest_version();
        require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
    }

    public function ajax_save_setting()
    {
        check_ajax_referer('gsap_sl_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $plugin_key = isset($_POST['plugin_handle']) ? sanitize_text_field($_POST['plugin_handle']) : '';
        $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '0';

        if ($plugin_key === '') {
            wp_send_json_error('Invalid plugin');
        }

        $options = get_option(self::OPTION_SETTINGS, []);

        if ($state === '1') {
            $options[$plugin_key] = '1';
        } else {
            unset($options[$plugin_key]);
        }

        update_option(self::OPTION_SETTINGS, $options);
        wp_send_json_success(['handle' => $plugin_key, 'state' => $state]);
    }

    public function ajax_refresh_cdn_data()
    {
        check_ajax_referer('gsap_sl_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Blow away cached version.
        delete_transient(self::TRANSIENT_VERSION);

        // Blow away cached files for the last known version (if any).
        $last = get_option(self::OPTION_LAST_KNOWN_VERSION, '');
        if (is_string($last) && $last !== '') {
            delete_transient(self::TRANSIENT_FILES_PREFIX . $last);
        }

        $version = $this->get_cdnjs_latest_version(true);
        wp_send_json_success(['version' => $version]);
    }

    /**
     * Resolve latest GSAP version available on cdnjs (cached).
     *
     * @param bool $force_refresh
     * @return string
     */
    private function get_cdnjs_latest_version($force_refresh = false)
    {
        if (!$force_refresh) {
            $cached = get_transient(self::TRANSIENT_VERSION);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $url = self::CDNJS_API_BASE . self::CDNJS_LIBRARY . '?fields=version';
        $res = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($res)) {
            return $this->get_fallback_version();
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        $body = wp_remote_retrieve_body($res);
        if ($code !== 200 || !is_string($body) || $body === '') {
            return $this->get_fallback_version();
        }

        $json = json_decode($body, true);
        $version = isset($json['version']) ? sanitize_text_field($json['version']) : '';
        if ($version === '') {
            return $this->get_fallback_version();
        }

        // cdnjs API responses are cached on their side; 6h is a good local TTL.
        set_transient(self::TRANSIENT_VERSION, $version, 6 * HOUR_IN_SECONDS);
        update_option(self::OPTION_LAST_KNOWN_VERSION, $version);

        return $version;
    }

    private function get_cdnjs_files_for_version($version)
    {
        $version = sanitize_text_field((string) $version);
        if ($version === '') {
            return [];
        }

        $key = self::TRANSIENT_FILES_PREFIX . $version;
        $cached = get_transient($key);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $url = self::CDNJS_API_BASE . self::CDNJS_LIBRARY . '/' . rawurlencode($version) . '?fields=files';
        $res = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($res)) {
            return [];
        }

        $code = (int) wp_remote_retrieve_response_code($res);
        $body = wp_remote_retrieve_body($res);
        if ($code !== 200 || !is_string($body) || $body === '') {
            return [];
        }

        $json = json_decode($body, true);
        $files = (isset($json['files']) && is_array($json['files'])) ? $json['files'] : [];

        // Versions are immutable; cache for a long time.
        set_transient($key, $files, 30 * DAY_IN_SECONDS);

        return $files;
    }

    private function build_cdnjs_url($version, $filename)
    {
        $version = rawurlencode(sanitize_text_field((string) $version));
        $filename = ltrim((string) $filename, '/');
        return self::CDNJS_CDN_BASE . $version . '/' . $filename;
    }

    private function get_fallback_version()
    {
        $last = get_option(self::OPTION_LAST_KNOWN_VERSION, '');
        if (is_string($last) && $last !== '') {
            return $last;
        }

        // Safe default if the site has never resolved cdnjs before.
        return '3.14.1';
    }

    /**
     * Convert plugin key dependencies to WP script-handle dependencies.
     *
     * @param array $plugins Registry from gsap_sl_get_plugins()
     * @param array $required_keys Array of plugin keys
     * @return array Array of script handles
     */
    private function map_required_keys_to_handles(array $plugins, array $required_keys)
    {
        $deps = [];

        foreach ($required_keys as $req_key) {
            $req_key = (string) $req_key;
            if ($req_key === '' || !isset($plugins[$req_key]['handle'])) {
                continue;
            }
            $deps[] = (string) $plugins[$req_key]['handle'];
        }

        return array_values(array_unique($deps));
    }

    public function enqueue_frontend_scripts()
    {
        $options = get_option(self::OPTION_SETTINGS, []);
        $plugins = gsap_sl_get_plugins();

        $version = $this->get_cdnjs_latest_version();
        $available_files = $this->get_cdnjs_files_for_version($version);
        $has_file_index = !empty($available_files);

        foreach ($plugins as $key => $plugin) {
            // Always enforce 'required' plugins.
            $is_required = isset($plugin['required']) && $plugin['required'] === true;
            $is_enabled = $is_required || (isset($options[$key]) && $options[$key] === '1');

            if (!$is_enabled) {
                continue;
            }

            $filename = $plugin['filename'] ?? '';
            if (!is_string($filename) || $filename === '') {
                continue;
            }

            // Optional safety: avoid 404s if cdnjs lags or a file got renamed.
            if ($has_file_index && !in_array($filename, $available_files, true)) {
                continue;
            }

            $required_keys = (array) ($plugin['requires'] ?? []);
            $deps = $this->map_required_keys_to_handles($plugins, $required_keys);

            wp_enqueue_script(
                $plugin['handle'],
                $this->build_cdnjs_url($version, $filename),
                $deps,
                $version,
                true
            );
        }
    }

    public function capture_external_enqueues()
    {
        if (is_admin()) {
            return;
        }

        $options = get_option(self::OPTION_SETTINGS, []);
        $plugins = gsap_sl_get_plugins();
        $external = [];

        foreach ($plugins as $key => $plugin) {
            $handle = $plugin['handle'] ?? '';
            if (!is_string($handle) || $handle === '') {
                continue;
            }

            $is_required = isset($plugin['required']) && $plugin['required'] === true;
            $is_enabled = $is_required || (isset($options[$key]) && $options[$key] === '1');

            if (!$is_enabled && wp_script_is($handle, 'enqueued')) {
                $external[] = $key;
            }
        }

        if (empty($external)) {
            delete_option(self::OPTION_EXTERNAL_ENQUEUES);
            return;
        }

        update_option(self::OPTION_EXTERNAL_ENQUEUES, array_values(array_unique($external)));
    }
}

new GSAP_Script_Loader();
