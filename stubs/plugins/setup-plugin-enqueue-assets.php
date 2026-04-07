<?php
namespace {{ plugin_namespace }}\Setup;

class EnqueueAssets
{
    protected $allow_debug_assets = true;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [$this, 'admin'] );
        add_action( 'wp_enqueue_scripts', [$this, 'frontend'] );
    }

    /** Get the plugin version, or current timestamp in debug mode. */
    protected function get_plugin_version() {
        if (
            $this->allow_debug_assets &&
            defined( 'WP_DEBUG' ) &&
            WP_DEBUG
        ) {
            return time();
        }
        return {{ plugin_constants_prefix }}VERSION;
    }

    /** Enqueue admin assets. */
    public function admin() {
        $plugin_version = $this->get_plugin_version();

        wp_enqueue_style(
            '{{ plugin_text_domain }}-admin',
            {{ plugin_constants_prefix }}URL . 'assets/build/{{ plugin_text_domain }}.admin.css',
            [],
            $plugin_version
        );

        wp_enqueue_script(
            '{{ plugin_text_domain }}-admin',
            {{ plugin_constants_prefix }}URL . 'assets/build/{{ plugin_text_domain }}.admin.js',
            [],
            $plugin_version,
            true
        );
    }

    /** Enqueue frontend assets. */
    public function frontend() {
        $plugin_version = $this->get_plugin_version();

        wp_enqueue_style(
            '{{ plugin_text_domain }}-frontend',
            {{ plugin_constants_prefix }}URL . 'assets/build/{{ plugin_text_domain }}.frontend.css',
            [],
            $plugin_version
        );

        wp_enqueue_script(
            '{{ plugin_text_domain }}-frontend',
            {{ plugin_constants_prefix }}URL . 'assets/build/{{ plugin_text_domain }}.frontend.js',
            [],
            $plugin_version,
            true
        );
    }
}