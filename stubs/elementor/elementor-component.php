<?php
namespace {{ plugin_namespace }}\Elementor;

class ElementorComponent
{
    public function __construct() {
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
    }

    /**
     * Register custom widgets with Elementor.
     *
     * The elementor/widgets/register hook only fires when Elementor is active,
     * so no class_exists() guard is required.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function register_widgets( $widgets_manager ) {
        foreach ( $this->widgets() as $widget ) {
            $widgets_manager->register( new $widget() );
        }
    }

    /**
     * Register a custom widget category for this plugin.
     *
     * @param \Elementor\Elements_Manager $elements_manager
     */
    public function register_categories( $elements_manager ) {
        $elements_manager->add_category(
            '{{ plugin_text_domain }}',
            [
                'title' => __( '{{ plugin_name }}', {{ plugin_constants_prefix }}TEXT_DOMAIN ),
                'icon'  => 'fa fa-plug',
            ]
        );
    }

    /**
     * @return array An array of widget class names.
     */
    public function widgets() {
        return [
        ];
    }
}
