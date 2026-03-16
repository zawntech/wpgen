<?php

namespace {{ plugin_namespace }}\Theme;

/**
 * Plugin Theme interface.
 */
class Theme
{
    /**
     * @return string Absolute path to plugin's templates.
     */
    protected static function get_plugin_theme_path() {
        return {{ plugin_constants_prefix }}DIR . 'assets/templates/';
    }

    /**
     * @return string Absolute path to WP theme override directory.
     */
    protected static function get_theme_override_path() {
        return '/templates/{{ plugin_text_domain }}/';
    }

    /**
     * A wrapper for get_template_part().
     *
     * Maps to parent/child theme root: '/templates/{{ plugin_text_domain }}/
     *
     * @param $slug
     * @param $args
     * @return void
     */
    public static function echo_template_part( $slug, $args = [] ) {

        // Allow template args to be filtered.
        $args = apply_filters( '{{ plugin_filter_prefix }}_get_template_part_args', $args, $slug );

        // Path to theme override template file.
        $template_override_path_slug = static::get_theme_override_path() . $slug;

        // Path to plugin template file.
        $template_path_slug = static::get_plugin_theme_path() . $slug;
        $template_path_filename = $template_path_slug . '.php';

        // Attempt to resolve theme override...
        ob_start();
        get_template_part( $template_override_path_slug, null, $args );
        $output = trim( ob_get_clean() );

        // Theme content found...
        if ( !empty( $output ) ) {
            echo $output;
            return;
        }

        // Require the plugin version of the template file.
        if ( file_exists( $template_path_filename ) ) {
            require $template_path_filename;
            return;
        }

        static::print_debug( "{{ plugin_name }} Theme Debug: unable to resolve template path: $slug" );
    }

    /**
     * Print debug info only visible to administrators.
     * @param $content
     * @return void
     */
    public static function print_debug( $content ) {

        // Only show debug to administrators.
        if (
            !is_user_logged_in() ||
            !current_user_can( 'manage_options' )
        ) {
            return;
        }

        printf( '<div class="{{ plugin_filter_prefix }}-debug">%s</div>', $content );
    }

    public static function get_template_part( $slug, $args = [] ) {
        ob_start();
        static::echo_template_part( $slug, $args );
        return ob_get_clean();
    }
}
