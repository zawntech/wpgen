<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AjaxControllerAbstract;

class {{ controller_class }}AjaxController extends AjaxControllerAbstract
{
    const EXAMPLE_ACTION = '{{ plugin_filter_prefix }}example_action';

    public function __construct() {
        add_action( 'wp_ajax_' . static::EXAMPLE_ACTION, [$this, 'example_action'] );
    }

    public function example_action() {
        $this->verify_nonce_token( static::EXAMPLE_ACTION );

        // Handle the action...

        wp_send_json_success( [] );
    }
}
