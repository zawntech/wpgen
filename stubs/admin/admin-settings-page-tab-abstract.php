<?php
namespace {{ plugin_namespace }}\Admin;

abstract class AdminSettingsPageTabAbstract
{
    public $key = '';

    public $label = '';

    public function render() {
        echo 'Overwrite render()...';
    }

    public function save() { }

    ////////////////////////

    /** @var array An array of notices to print. */
    protected $notices = [];

    /**
     * Register an admin notice to print to the active tab.
     *
     * @param $content
     * @param string $type 'success', 'error', 'notice'
     */
    public function print_admin_notice( $content, $type = 'success' ) {
        $class           = 'is-dismissible notice notice-' . $type;
        $message         = __( $content, '{{ plugin_text_domain }}' );
        $string          = sprintf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        $this->notices[] = $string;
    }

    /**
     * Output any registered admin notices.
     */
    public function print_notices() {
        foreach ( $this->notices as $notice ) {
            echo $notice;
        }
    }

    /**
     * @param array $keys An array of option keys to be saved as strings.
     */
    protected function save_strings( $keys = [] ) {
        $values = [];
        foreach( $keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = $_POST[$key];
                $value = stripslashes( $value );
                $value = filter_var( $value, FILTER_SANITIZE_STRING );
                $values[$key] = $value;
            }
        }
        Settings::get()->set( $values );
    }

    /**
     * @param array $keys An array of options keys to be saved as parsed JSON.
     */
    protected function save_json( $keys = [] ) {
        $values = [];
        foreach( $keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = $_POST[$key];
                $value = stripslashes( $value );
                $value = json_decode( $value, true );
                $values[$key] = $value;
            }
        }
        Settings::get()->set( $values );
    }

    protected function get_nonce_key() {
        return $this->key . '_nonce';
    }

    protected function get_nonce_action() {
        return $this->key . '-' . get_current_user_id();
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
