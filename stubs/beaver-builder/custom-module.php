<?php
namespace {{ plugin_namespace }}\BeaverBuilder;

class {{ module_class }}Module extends \FLBuilderModule
{
    public function __construct() {

        $text_domain = {{ plugin_constants_prefix }}TEXT_DOMAIN;

        parent::__construct([
            'name'            => __( '{{ module_name }}', $text_domain ),
            'description'     => __( '{{ module_description }}', $text_domain ),
            'group'           => __( '{{ module_group }}', $text_domain ),
            'category'        => __( '{{ module_category }}', $text_domain ),
            'dir'             => {{ plugin_constants_prefix }}DIR . 'bb-modules/{{ module_dir }}/',
            'url'             => {{ plugin_constants_prefix }}URL . 'bb-modules/{{ module_dir }}/',
            // 'icon'            => '',
            'editor_export'   => true,
            'enabled'         => true,
            'partial_refresh' => false
        ]);
    }

    public static function module_settings() {

        $text_domain = {{ plugin_constants_prefix }}TEXT_DOMAIN;

        return [
            'tab_1' => [
                'title' => __( 'Tab Title', $text_domain ),
                'sections' => [
                    'section_1' => [
                        'title' => __( 'Section Title', $text_domain ),
                        'fields' => [
                            'custom_field' => [
                                'type' => 'text',
                                'label' => __( 'Field Label', $text_domain ),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}