<?php
namespace {{ plugin_namespace }}\{{ component_name }};

class {{ taxonomy_singular }}Taxonomy {

    const KEY = '{{ taxonomy_key }}';
    const SINGULAR = '{{ taxonomy_singular }}';
    const PLURAL = '{{ taxonomy_plural }}';
    public $post_types = ['{{ post_type }}'];

    public function get_labels() {
        $singular = static::SINGULAR;
        $plural = static::PLURAL;
        $text_domain = '{{ plugin_text_domain }}';
        $labels = [
            'name'                       => _x( $plural, 'Taxonomy General Name', $text_domain ),
            'singular_name'              => _x( $singular, 'Taxonomy Singular Name', $text_domain ),
            'menu_name'                  => __( $plural, $text_domain ),
            'all_items'                  => __( 'All ' . $plural, $text_domain ),
            'parent_item'                => __( 'Parent ' . $singular, $text_domain ),
            'parent_item_colon'          => __( 'Parent ' . $singular . ':', $text_domain ),
            'new_item_name'              => __( 'New ' . $singular . ' Name', $text_domain ),
            'add_new_item'               => __( 'Add New ' . $singular, $text_domain ),
            'edit_item'                  => __( 'Edit ' . $singular, $text_domain ),
            'update_item'                => __( 'Update ' . $singular, $text_domain ),
            'view_item'                  => __( 'View ' . $singular, $text_domain ),
            'separate_items_with_commas' => __( 'Separate items with commas', $text_domain ),
            'add_or_remove_items'        => __( 'Add or remove items', $text_domain ),
            'choose_from_most_used'      => __( 'Choose from the most used', $text_domain ),
            'popular_items'              => __( 'Popular Items', $text_domain ),
            'search_items'               => __( 'Search Items', $text_domain ),
            'not_found'                  => __( 'Not Found', $text_domain ),
            'no_terms'                   => __( 'No items', $text_domain ),
            'items_list'                 => __( 'Items list', $text_domain ),
            'items_list_navigation'      => __( 'Items list navigation', $text_domain ),
        ];

        return $labels;
    }

    public function get_args() {
        $args = [
            'labels'            => $this->get_labels(),
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
        ];

        return $args;
    }

    public function __construct() {
        add_action( 'init', [$this, 'register_taxonomy'], 0 );
    }

    public function register_taxonomy() {
        register_taxonomy( static::KEY, $this->post_types, $this->get_args() );
    }
}