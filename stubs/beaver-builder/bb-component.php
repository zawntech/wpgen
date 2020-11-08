<?php
namespace {{ plugin_namespace }}\BeaverBuilder;

class BeaverBuilderComponent
{
    public function __construct() {
        add_action( 'init', [$this, 'register'] );
    }

    public function register() {

        // Check if beaver builder is active.
        if ( ! class_exists( '\FLBuilder' ) ) {
            return;
        }

        // Register custom settings forms.
        foreach( $this->settings_forms() as $form ) {
            \FLBuilder::register_settings_form( $form::FIELD_KEY, $form::form_settings() );
        }

        // Register custom modules.
        foreach( $this ->modules() as $module ) {
            \FLBuilder::register_module( $module, $module::module_settings() );
        }
    }

    /**
     * @return array An array of module class names.
     */
    public function modules() {
        return [
        ];
    }

    /**
     * @return array An array of settings forms class names.
     */
    public function settings_forms() {
        return [
        ];
    }
}