<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractMetaBox;
use AllegedWizard\WPAdminOptions\Fields\InputOption;

class {{ meta_box_class }}MetaBox extends AbstractMetaBox
{
    const ID = '{{ meta_box_id }}';

    const TITLE = '{{ meta_box_title }}';
    
    const POST_TYPES = [{{ post_type_class }}::KEY];

    protected $stringy_keys = [
        '_some_option',
    ];

    protected $json_keys = [];

    protected function render( \WP_Post $post ) {
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
}
