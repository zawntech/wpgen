<?php

namespace {{ plugin_namespace }}\{{ component_name }};

class {{ post_type_plural }}
{
    /**
     * @param array $args
     * @param boolean $prepare_models Maps WP_Query posts through this model's prepare_post function.
     * @return \WP_Post[]
     */
    public static function all( $args = [], $prepare_models = true ) {

        $args = wp_parse_args( $args, [
            'post_type' => {{ post_type_singular }}PostType::KEY,
            'posts_per_page' => '-1',
        ]);

        $query = new \WP_Query( $args );

        $posts = array_map( function( $post ) use ( $prepare_models ) {
            return $prepare_models ? static::prepare_post( $post ) : $post;
        }, $query->posts );

        return $posts;
    }

    /**
     * @param array $args Post type args
     * @return mixed
     */
    public static function create( $args = [] ) {

        $args = wp_parse_args( $args, [
            'post_type' => {{ post_type_singular }}PostType::KEY,
        ]);

        $post_id = wp_insert_post( $args );

        return $post_id;
    }

    /**
     * Get an array of all posts as <select> options.
     * @param array $args WP Query args
     * @param string $initial_label
     * @return string[]
     */
    public static function all_as_select_options( $args = [], $initial_label = 'Select {{ post_type_singular }}...' ) {

        $args = wp_parse_args( $args, [
            'post_type' => {{ post_type_singular }}PostType::KEY,
            'orderby' => 'title',
            'order' => 'asc',
            'posts_per_page' => '-1',
        ] );

        $query = new \WP_Query( $args );

        $output = [
            '' => $initial_label
        ];

        foreach( $query->posts as $post ) {
            $output[(string) $post->ID] = $post->post_title;
        }

        return $output;
    }

    /**
     * Modify a \WP_Post object to include relevant post meta or taxonomy data.
     * @param \WP_Post|int $post
     * @return \WP_Post
     */
    public static function prepare_post( $post ) {

        if ( ! $post instanceof \WP_Post ) {
            $post = get_post( $post );
        }

        // $post->_some_option = static::get_some_option( $post->ID );

        return $post;
    }

    /**
     * Get post meta value by key, defaulting to current loop post ID.
     * @param $key
     * @param int $post_id
     * @return mixed
     */
    protected static function get_meta( $key, $post_id = 0 ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        return get_post_meta( $post_id, $key, true );
    }

    /**
     * Get post meta value as array.
     * @param $key
     * @param int $post_id
     * @return array
     */
    protected static function get_meta_as_array( $key, $post_id = 0 ) {
        $meta_value = static::get_meta( $key, $post_id );
        if ( ! is_array( $meta_value ) ) {
            return [];
        }
        return $meta_value;
    }

    /**
     * Example option...
     * @param int $post_id
     * @return mixed
     */
    public static function get_some_option( $post_id = 0 ) {
        return static::get_meta( '_some_option', $post_id );
    }
}
