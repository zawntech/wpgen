<?php
namespace {{ plugin_namespace }}\Setup;

class EnqueueAssets
{
    public function __construct() {
        add_action( 'init', [$this, 'register'] );
        add_action( 'admin_enqueue_scripts', [$this, 'admin'] );
        add_action( 'wp_enqueue_scripts', [$this, 'frontend'] );
    }

    /** Register assets. */
    public function register() {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $select2_css = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.css';
            $select2_js = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.js';
            $vue_js = 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js';
        } else {
            $select2_css = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css';
            $select2_js = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js';
            $vue_js = 'https://cdn.jsdelivr.net/npm/vue@2.6.11';
        }
        wp_register_style( 'select2', $select2_css, [], '4.0.7' );
        wp_register_script( 'select2', $select2_js, [], '4.0.7', true );
        wp_register_script( 'vue', $vue_js, [], null, true );
    }

    /** Enqueue frontend assets. */
    public function admin() {
        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'select2' );
        wp_enqueue_script( 'vue' );
    }

    /** Enqueue backend assets. */
    public function frontend() {
    }
}