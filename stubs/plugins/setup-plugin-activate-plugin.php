<?php
namespace {{ plugin_namespace }}\Setup;

class ActivatePlugin
{
    public function __construct() {
        add_action( 'admin_init', [$this, 'maybe_activate_plugin'] );
    }

    public function maybe_activate_plugin() {
        $option_key = '{{ plugin_constants_prefix }}ACTIVATE';
        $option = get_option( $option_key );
        if ( ! empty( $option ) ) {
            $this->activate_plugin();
            delete_option( $option_key );
        }
    }

    public function activate_plugin() {
        // Activate plugin...
    }
}