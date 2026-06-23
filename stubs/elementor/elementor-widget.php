<?php
namespace {{ plugin_namespace }}\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class {{ widget_class }}Widget extends \Elementor\Widget_Base
{
    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );

        wp_register_style(
            '{{ widget_handle }}',
            {{ plugin_constants_prefix }}URL . 'elementor-widgets/{{ widget_dir }}/css/frontend.css',
            [],
            {{ plugin_constants_prefix }}VERSION
        );

        wp_register_script(
            '{{ widget_handle }}',
            {{ plugin_constants_prefix }}URL . 'elementor-widgets/{{ widget_dir }}/js/frontend.js',
            [],
            {{ plugin_constants_prefix }}VERSION,
            true
        );
    }

    /**
     * Unique widget name. Must be globally unique across all plugins.
     */
    public function get_name(): string {
        return '{{ widget_slug }}';
    }

    public function get_title(): string {
        return __( '{{ widget_name }}', {{ plugin_constants_prefix }}TEXT_DOMAIN );
    }

    /**
     * eicon class. See https://elementor.github.io/elementor-icons/
     */
    public function get_icon(): string {
        return '{{ widget_icon }}';
    }

    public function get_categories(): array {
        return [ '{{ widget_category }}' ];
    }

    public function get_keywords(): array {
        return [ {{ widget_keywords }} ];
    }

    /**
     * Style handles enqueued only when this widget is on the page.
     */
    public function get_style_depends(): array {
        return [ '{{ widget_handle }}' ];
    }

    /**
     * Script handles enqueued only when this widget is on the page.
     */
    public function get_script_depends(): array {
        return [ '{{ widget_handle }}' ];
    }

    /**
     * Register the widget controls.
     *
     * Per-instance styling is driven by each control's `selectors` arg
     * (scoped to {{WRAPPER}}); Elementor generates the scoped CSS - there is
     * no css.php. Keep base/structural styles in css/frontend.scss.
     */
    protected function register_controls(): void {

        $text_domain = {{ plugin_constants_prefix }}TEXT_DOMAIN;

        // Content tab.
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', $text_domain ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label'   => __( 'Title', $text_domain ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( '{{ widget_name }}', $text_domain ),
            ]
        );

        $this->end_controls_section();

        // Style tab.
        $this->start_controls_section(
            'section_title_style',
            [
                'label' => __( 'Title', $text_domain ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => __( 'Text Color', $text_domain ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .{{ widget_dir }}__title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .{{ widget_dir }}__title',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend output (PHP). Reads resolved settings (dynamic tags/globals).
     */
    protected function render(): void {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['title'] ) ) {
            return;
        }
        ?>
        <div class="{{ widget_dir }}">
            <h2 class="{{ widget_dir }}__title"><?php echo esc_html( $settings['title'] ); ?></h2>
        </div>
        <?php
    }

    /**
     * Live editor preview (Underscore.js). Must mirror render().
     */
    protected function content_template(): void {
        ?>
        <# if ( '' === settings.title ) { return; } #>
        <div class="{{ widget_dir }}">
            <h2 class="{{ widget_dir }}__title">{{{ settings.title }}}</h2>
        </div>
        <?php
    }
}
