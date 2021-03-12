<?php
namespace {{ plugin_namespace }}\BeaverBuilder\Forms;

class {{ form_class }}SettingsForm
{
    const FIELD_KEY = '{{ field_key }}';

    public static function form_settings() {

        $text_domain = {{ plugin_constants_prefix }}TEXT_DOMAIN;

        return [
            'title' => __( 'My Form Field', $text_domain ),
            'tabs' => [
                'general' => [
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
            ]
        ];
    }
}