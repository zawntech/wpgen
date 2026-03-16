<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class AbstractPostTypeModel
{
    const POST_TYPE_KEY = '';

    public static function all( $args = [], $prepare_models = true ) {
        $args = wp_parse_args( $args, [
            'post_type'      => static::POST_TYPE_KEY,
            'posts_per_page' => '-1',
        ]);

        $query = new \WP_Query( $args );

        $posts = array_map( function( $post ) use ( $prepare_models ) {
            return $prepare_models ? static::prepare_post( $post ) : $post;
        }, $query->posts );

        return $posts;
    }

    public static function create( $args = [] ) {
        $args = wp_parse_args( $args, [
            'post_type' => static::POST_TYPE_KEY,
        ]);

        return wp_insert_post( $args );
    }

    public static function all_as_select_options( $args = [], $initial_label = 'Select...' ) {
        $args = wp_parse_args( $args, [
            'post_type'      => static::POST_TYPE_KEY,
            'orderby'        => 'title',
            'order'          => 'asc',
            'posts_per_page' => '-1',
        ] );

        $query = new \WP_Query( $args );

        $output = ['' => $initial_label];

        foreach( $query->posts as $post ) {
            $output[(string) $post->ID] = $post->post_title;
        }

        return $output;
    }

    public static function prepare_post( $post ) {
        if ( ! $post instanceof \WP_Post ) {
            $post = get_post( $post );
        }

        // $post->_some_option = static::get_some_option( $post->ID );

        return $post;
    }

    protected static function get_meta( $key, $post_id = 0 ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        return get_post_meta( $post_id, $key, true );
    }

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
    // public static function get_some_option( $post_id = 0 ) {
    //    return static::get_meta( '_some_option', $post_id );
    //}
}
