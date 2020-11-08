<?php
/*
Plugin Name: {{ plugin_name }}
Plugin URI: {{ plugin_url }}
Description: {{ plugin_description }}
Author: {{ plugin_author }}
Version: 0.0.1
Author URI: {{ plugin_author_url }}
*/

/** Absolute path to plugin directory (with trailing slash). */
define( '{{ plugin_constants_prefix }}DIR', trailingslashit( __DIR__ ) );

/** Public URL to plugin directory (with trailing slash). */
define( '{{ plugin_constants_prefix }}URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/** Plugin text domain. */
define( '{{ plugin_constants_prefix }}TEXT_DOMAIN', '{{ plugin_text_domain }}' );

/** Plugin version. */
define( '{{ plugin_constants_prefix }}VERSION', '0.0.1' );

// Verify composer autoloader is installed.
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    add_action( 'admin_notices', function() {
        $class = 'notice notice-error';
        $message = __( 'Error: the composer autoloader does not exist for {{ plugin_name }}', {{ plugin_constants_prefix }}TEXT_DOMAIN );
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    });
    return;
}

// Load composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Initialize plugin.
{{ plugin_namespace }}\{{ plugin_main_class }}::get_instance();

// On register activate hook, set an option that which triggers  upon the next admin request.
// This allows the fully initialized state of WordPress and all plugin functionalities.
// The option is immediately deleted after firing once, effectively replacing the
// activation hook with higher functional access.
register_activation_hook( __FILE__, function() {
    $option_key = '{{ plugin_constants_prefix }}ACTIVATE';
    update_option( $option_key, 1 );
});

register_deactivation_hook( __FILE__, function() {
    new {{ plugin_namespace }}\Setup\DeactivatePlugin;
});
