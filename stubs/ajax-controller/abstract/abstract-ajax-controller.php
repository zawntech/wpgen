<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class AbstractAjaxController
{
    /**
     * Verifies a nonce token; sends JSON error 401 if invalid.
     *
     * @param string $action The nonce action.
     * @param string $key    The request key holding the nonce value.
     */
    protected function verify_nonce_token( $action, $key = 'nonce' ) {
        $nonce_value = $_REQUEST[$key] ?? '';

        if ( !wp_verify_nonce( $nonce_value, $action ) ) {
            wp_send_json_error( 'Invalid nonce token.', 401 );
        }
    }
}
