<?php

namespace {{ plugin_namespace }}\{{ component_name }};

use Zawntech\WPAdminOptions\UserSelectOption;

class {{ component_name }}UserMeta
{
    public function __construct() {
        add_action( 'show_user_profile', [$this, 'render_fields'] );
        add_action( 'edit_user_profile', [$this, 'render_fields'] );
        add_action( 'personal_options_update', [$this, 'save_fields'] );
        add_action( 'edit_user_profile_update', [$this, 'save_fields'] );
    }

    public function render_fields( \WP_User $user ) {
        ?>
        <h2>Example Custom User Meta</h2>
        <p>Some description text...</p>
        <table class="form-table">
            <?php
            $attached_users = get_user_meta( $user->ID, '_attached_users', true );
            if ( !is_array( $attached_users ) ) {
                $attached_users = [];
            }
            // Example option
            new UserSelectOption( [
                'multiple' => true,
                'label' => 'Attach Users',
                'key' => '_attached_users',
                'role__in' => ['subscriber'],
                'value' => $attached_users,
            ] );
            ?>
        </table>
        <?php
    }

    public function save_fields( $user_id ) {
        $stringy_fields = [];
        $json_fields = [];
        foreach ( $stringy_fields as $key ) {
            if ( isset( $_REQUEST[$key] ) ) {
                $value = $_REQUEST[$key];
                $value = stripslashes( $value );
                update_user_meta( $user_id, $key, $value );
            }
        }
        foreach ( $json_fields as $key ) {
            if ( isset( $_REQUEST[$key] ) ) {
                $value = $_REQUEST[$key];
                $value = stripslashes( $value );
                $value = json_decode( $value, true );
                update_user_meta( $user_id, $key, $value );
            }
        }
    }
}