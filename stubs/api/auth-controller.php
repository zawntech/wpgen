<?php

namespace {{ plugin_namespace }}\API;

class AuthController extends AbstractApiController
{
    public function __construct() {
        add_action( 'rest_api_init', [$this, 'register_routes'] );
    }

    public function register_routes() {

        // POST {base-url}/auth/login
        register_rest_route( $this->namespace, 'auth/login', [
            'methods' => 'post',
            'callback' => [$this, 'login'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // POST {base-url}/auth/register
        register_rest_route( $this->namespace, 'auth/register', [
            'methods' => 'post',
            'callback' => [$this, 'register'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // POST {base-url}/auth/request-password-reset
        register_rest_route( $this->namespace, 'auth/request-password-reset', [
            'methods' => 'post',
            'callback' => [$this, 'request_password_reset'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // POST {base-url}/auth/request-password-reset
        register_rest_route( $this->namespace, 'auth/reset-password', [
            'methods' => 'post',
            'callback' => [$this, 'reset_password'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );
    }

    public function login( \WP_REST_Request $request ) {

        // Get 'email' and 'password' from request.
        $email = $request->get_param( 'email' );
        $password = $request->get_param( 'password' );

        // Get user...
        $user = $this->get_user_by_email( $email );
        if ( !$user ) {
            return wp_send_json( 'Invalid user requested...', 406 );
        }

        // Check password...
        if ( !wp_check_password( $password, $user->user_pass ) ) {
            return wp_send_json( 'Invalid password supplied...', 406 );
        }

        // All good!
    }

    public function register( \WP_REST_Request $request ) {

        // Get 'email' and 'password' from request.
        $email = $request->get_param( 'email' );
        $password = $request->get_param( 'password' );

        // Check if user already exists...
        if ( $this->get_user_by_email( $email ) ) {
            return wp_send_json( 'User already exists...', 406 );
        }

        // Create the new user...
        $user_id = wp_insert_user( [
            'user_email' => $email,
            'user_login' => $email,
            'user_pass' => $password
        ] );
    }

    public function request_password_reset( \WP_REST_Request $request ) {

        // Check supplied email address.
        $email = $request->get_param( 'email' );
        $user = $this->get_user_by_email( $email );
        if ( !$user ) {
            return wp_send_json( 'Invalid user requested...', 406 );
        }

        // Create and store random password reset token.
        $random_token = $this->generate_random_token();
        update_user_meta( $user->ID, '_reset_password_token', $random_token );
    }

    public function reset_password( \WP_REST_Request $request ) {

        // Get request parameters.
        $email = $request->get_param( 'email' );
        $password = $request->get_param( 'password' );
        $reset_token = $request->get_param( 'reset_token' );

        // Check requested user.
        $user = $this->get_user_by_email( $email );
        if ( !$user ) {
            return wp_send_json( 'Invalid user requested...', 406 );
        }

        // Validate password.
        $min_characters = 8;
        if ( strlen( $password ) < 8 ) {
            return wp_send_json(
                'Invalid password, please use a password with at least ' . $min_characters . ' characters',
                406
            );
        }

        // Check request token...
        $current_reset_token = get_user_meta( $user->ID, '_reset_password_token', true );
        if ( empty( $current_reset_token ) || $current_reset_token !== $reset_token ) {
            return wp_send_json( 'Invalid reset token supplied.' );
        }

        // Reset password!
        reset_password( $user->ID, $password );

        return rest_ensure_response(true);
    }

    //////

    /**
     * @param $email
     * @return \WP_User|false
     */
    protected function get_user_by_email( $email ) {
        return get_user_by( 'email', $email );
    }

    protected function generate_random_token( $length = 16 ) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $output = '';
        while ( strlen( $output ) < $length ) {
            $random_char_index = mt_rand( 0, strlen( $chars ) - 1 );
            $output .= $chars[$random_char_index];
        }
        return $output;
    }
}