<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use Zawntech\WPAdminOptions\InputOption;

/**
 * Class {{ meta_box_class }}
 */
class {{ meta_box_class }}MetaBox
{
    const ID = '{{ meta_box_id }}';

    const TITLE = '{{ meta_box_title }}';

    public function __construct() {
        // Define which post types we want to hook.
        $post_types = ['{{ post_type_key }}'];
        foreach( $post_types as $post_type ) {
            add_action( 'add_meta_boxes_' . $post_type, [$this, 'register_meta_box'] );
            add_action( 'save_post_' . $post_type, [$this, 'save_post'] );
        }
    }

    public function register_meta_box() {
        add_meta_box( static::ID, static::TITLE, [$this, 'render_meta_box'] );
    }

    public function render_meta_box( \WP_Post $post ) {
        ?>
        <table class="form-table">
            <tbody>
            <?php
            new InputOption([
                'key' => '_some_option',
                'label' => 'Some Option',
                'value' => get_post_meta( $post->ID, '_some_option', true )
            ]);
            ?>
            </tbody>
        </table>
        <?php
    }

    public function save_post( $post_id ) {

        // Stringy options
        $keys = [
            '_some_option'
        ];

        foreach( $keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = filter_var( $_POST[$key], FILTER_SANITIZE_STRING );
                update_post_meta( $post_id, $key, $value );
            }
        }

        // Json options
        $json_keys = [
        ];

        foreach( $json_keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = stripslashes( $_POST[$key] );
                $value = json_decode( $value, true );
                update_post_meta( $post_id, $key, $value );
            }
        }
    }
}
