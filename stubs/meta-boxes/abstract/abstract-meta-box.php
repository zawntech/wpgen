<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class AbstractMetaBox
{
    const ID = '';
    const TITLE = '';
    const POST_TYPES = [];

    /** @var array Option keys saved as sanitized strings. */
    protected $stringy_keys = [];

    /** @var array Option keys saved as decoded JSON. */
    protected $json_keys = [];

    public function __construct() {
        foreach ( static::POST_TYPES as $post_type ) {
            add_action( 'add_meta_boxes_' . $post_type, [$this, 'register_meta_box'] );
            add_action( 'save_post_' . $post_type, [$this, 'save_post'] );
        }
    }

    public function register_meta_box() {
        add_meta_box( static::ID, static::TITLE, [$this, 'render_meta_box'] );
    }

    public function render_meta_box( \WP_Post $post ) {
        $this->render( $post );
        $this->nonce_field();
    }

    protected function render( \WP_Post $post ) {
        // Override render()...
    }

    public function save_post( $post_id ) {
        if ( !$this->verify_nonce() ) {
            return;
        }

        foreach ( $this->stringy_keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                update_post_meta( $post_id, $key, $this->sanitize_string( wp_unslash( $_POST[$key] ) ) );
            }
        }

        foreach ( $this->json_keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = json_decode( stripslashes( $_POST[$key] ), true );
                update_post_meta( $post_id, $key, $value );
            }
        }
    }

    protected function sanitize_string( $value ) {
        return sanitize_text_field( $value );
    }

    protected function get_nonce_key() {
        return static::ID . '_nonce';
    }

    protected function get_nonce_action() {
        return static::ID . '-' . get_current_user_id();
    }

    protected function nonce_field() {
        printf(
            '<input type="hidden" name="%s" value="%s" />',
            $this->get_nonce_key(),
            wp_create_nonce( $this->get_nonce_action() )
        );
    }

    protected function verify_nonce() {
        if (
            !isset( $_POST[$this->get_nonce_key()] ) ||
            !wp_verify_nonce( $_POST[$this->get_nonce_key()], $this->get_nonce_action() )
        ) {
            return false;
        }
        return true;
    }
}
