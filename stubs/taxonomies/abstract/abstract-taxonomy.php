<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class AbstractTaxonomy
{
    const KEY = '';
    const SINGULAR = '';
    const PLURAL = '';

    protected $post_types = [];

    public function __construct() {
        $this->validate();
        add_action( 'init', [$this, 'register_taxonomy'], 0 );
    }

    protected function validate() {
        $required = ['KEY', 'SINGULAR', 'PLURAL'];
        foreach ( $required as $constant ) {
            if ( empty( constant( 'static::' . $constant ) ) ) {
                throw new \Exception( static::class . ' must define the ' . $constant . ' constant.' );
            }
        }
        if ( empty( $this->post_types ) ) {
            throw new \Exception( static::class . '::$post_types must not be empty.' );
        }
    }

    public function register_taxonomy() {
        register_taxonomy( static::KEY, $this->post_types, $this->get_args() );
    }

    public function get_labels() {
        $singular = static::SINGULAR;
        $plural = static::PLURAL;
        $text_domain = '{{ plugin_text_domain }}';
        $labels = [
            'name'                       => _x( $plural, 'Taxonomy General Name', $text_domain ),
            'singular_name'              => _x( $singular, 'Taxonomy Singular Name', $text_domain ),
            'menu_name'                  => __( $plural, $text_domain ),
            'all_items'                  => __( 'All ' . $plural, $text_domain ),
            'edit_item'                  => __( 'Edit ' . $singular, $text_domain ),
            'view_item'                  => __( 'View ' . $singular, $text_domain ),
            'update_item'                => __( 'Update ' . $singular, $text_domain ),
            'add_new_item'               => __( 'Add New ' . $singular, $text_domain ),
            'new_item_name'              => __( 'New ' . $singular . ' Name', $text_domain ),
            'parent_item'                => __( 'Parent ' . $singular, $text_domain ),
            'parent_item_colon'          => __( 'Parent ' . $singular . ':', $text_domain ),
            'search_items'               => __( 'Search ' . $plural, $text_domain ),
            'popular_items'              => __( 'Popular ' . $plural, $text_domain ),
            'separate_items_with_commas' => __( 'Separate ' . $plural . ' with commas', $text_domain ),
            'add_or_remove_items'        => __( 'Add or remove ' . $plural, $text_domain ),
            'choose_from_most_used'      => __( 'Choose from the most used ' . $plural, $text_domain ),
            'not_found'                  => __( 'No ' . $plural . ' found.', $text_domain ),
            'no_terms'                   => __( 'No ' . $plural, $text_domain ),
            'items_list'                 => __( $plural . ' list', $text_domain ),
            'items_list_navigation'      => __( $plural . ' list navigation', $text_domain ),
            'back_to_items'              => __( '&larr; Back to ' . $plural, $text_domain ),
        ];

        return $labels;
    }

    public function get_args() {
        $args = [
            'labels'             => $this->get_labels(),
            'description'        => '',
            'public'             => true,
            'publicly_queryable' => true,
            'hierarchical'       => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_rest'       => false,
            'show_tagcloud'      => true,
            'show_in_quick_edit' => true,
            'show_admin_column'  => true,
            'query_var'          => true,
            'rewrite'            => $this->get_rewrite(),
        ];

        return $args;
    }

    public function get_rewrite() {
        return [
            'slug'         => static::KEY,
            'with_front'   => true,
            'hierarchical' => false,
        ];
    }
}
