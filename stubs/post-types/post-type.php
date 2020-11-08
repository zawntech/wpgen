<?php
namespace {{ plugin_namespace }}\{{ component_name }};

class {{ post_type_singular }}PostType
{
    const KEY = '{{ post_type_key }}';
    const SINGULAR = '{{ post_type_singular }}';
    const PLURAL = '{{ post_type_plural }}';
    const SLUG = '{{ post_type_slug }}';

    public function __construct() {
        add_action( 'init', [$this, 'register_post_type'], 0 );
    }

    public function register_post_type() {
        register_post_type( static::KEY, $this->get_args() );
    }

    /**
     * @return array
     */
    public function get_labels() {
        $singular = static::SINGULAR;
        $plural = static::PLURAL;
        $text_domain = '{{ plugin_text_domain }}';
        $labels = [
            'name'                  => _x( $plural, 'Post Type General Name', $text_domain ),
            'singular_name'         => _x( $singular, 'Post Type Singular Name', $text_domain ),
            'menu_name'             => __( $plural, $text_domain ),
            'name_admin_bar'        => __( $singular, $text_domain ),
            'archives'              => __( $singular . ' Archives', $text_domain ),
            'attributes'            => __( $singular . ' Attributes', $text_domain ),
            'parent_item_colon'     => __( 'Parent ' . $singular . ':', $text_domain ),
            'all_items'             => __( 'All ' . $plural, $text_domain ),
            'add_new_item'          => __( 'Add New ' . $singular, $text_domain ),
            'add_new'               => __( 'Add New', $text_domain ),
            'new_item'              => __( 'New ' . $singular, $text_domain ),
            'edit_item'             => __( 'Edit ' . $singular, $text_domain ),
            'update_item'           => __( 'Update ' . $singular, $text_domain ),
            'view_item'             => __( 'View ' . $singular, $text_domain ),
            'view_items'            => __( 'View ' . $plural, $text_domain ),
            'search_items'          => __( 'Search ' . $singular, $text_domain ),
            'not_found'             => __( 'Not found', $text_domain ),
            'not_found_in_trash'    => __( 'Not found in Trash', $text_domain ),
            'featured_image'        => __( 'Featured Image', $text_domain ),
            'set_featured_image'    => __( 'Set featured image', $text_domain ),
            'remove_featured_image' => __( 'Remove featured image', $text_domain ),
            'use_featured_image'    => __( 'Use as featured image', $text_domain ),
            'insert_into_item'      => __( 'Insert into item', $text_domain ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', $text_domain ),
            'items_list'            => __( 'Items list', $text_domain ),
            'items_list_navigation' => __( 'Items list navigation', $text_domain ),
            'filter_items_list'     => __( 'Filter items list', $text_domain ),
        ];
        return $labels;
    }

    /**
     * @return array
     */
    public function get_args() {
        $text_domain = '{{ plugin_text_domain }}';
        $args = [
            'label'               => __( static::SINGULAR, $text_domain ),
            'description'         => __( 'Post Type Description', $text_domain ),
            'labels'              => $this->get_labels(),
            'supports'            => ['title', 'editor', 'thumbnail'],
            'taxonomies'          => [],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'menu_icon'           => '',
            'rewrite'             => $this->get_rewrite()
        ];
        return $args;
    }

    /**
     * @return array|bool
     */
    public function get_rewrite() {
        if ( empty( static::SLUG ) ) {
            return false;
        }
        return [
            'slug'       => static::SLUG,
            'with_front' => true,
            'pages'      => true,
            'feeds'      => true
        ];
    }
}