<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class AbstractPostTypeListTable
{
    protected $post_types = [];
    protected $columns = [];
    protected $unset_columns = [];
    protected $sortable_columns = [];

    public function __construct() {
        foreach( $this->post_types as $post_type ) {
            add_filter( "manage_{$post_type}_posts_columns", [$this, 'columns'] );
            add_filter( "manage_edit-{$post_type}_sortable_columns", [$this, 'sortable_columns'] );
            add_action( "manage_{$post_type}_posts_custom_column", [$this, 'column_content'], 10, 2 );
        }
        add_action( 'pre_get_posts', [$this, 'orderby'] );

        // Custom drop down filters
        // add_action( 'pre_get_posts', [$this, 'process_filters'] );
        // add_action( 'restrict_manage_posts', [$this, 'custom_filters'], 10, 2 );
    }

    public function columns( $columns ) {
        $columns = array_merge( $columns, $this->columns );
        foreach( $this->unset_columns as $column ) {
            unset( $columns[$column] );
        }
        return $columns;
    }

    public function sortable_columns( $columns ) {
        return array_merge( $columns, $this->sortable_columns );
    }

    public function column_content( $column_name, $post_id ) {
        $method = 'render_' . ltrim( $column_name, '_' );

        if ( ! method_exists( $this, $method ) ) {
            throw new \BadMethodCallException( "No render method found for column '{$column_name}'. Expected method: {$method}()" );
        }

        $this->$method( $post_id );
    }

    public function orderby( \WP_Query $query ) {
        if ( ! is_admin() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( empty( $orderby ) ) {
            return;
        }

        $method = 'orderby_' . ltrim( $orderby, '_' );

        if ( ! method_exists( $this, $method ) ) {
            return;
        }

        $this->$method( $query );
    }

    public function custom_filters( $post_type ) {
        if ( !in_array( $post_type, $this->post_types ) ) {
            return;
        }
        ?>
        <select name="_meta_key">
            <?php
            $items = ['' => 'Select item...'];
            foreach ( $items as $id => $title ) {
                $selected = $_GET['_meta_key'] == $id ? ' selected="selected"' : '';
                printf( '<option value="%s"%s>%s</option>', $id, $selected, $title );
            }
            ?>
        </select>
        <?php
    }

    public function process_filters( \WP_Query $query ) {
        if ( !in_array( $query->query['post_type'], $this->post_types ) ) {
            return;
        }

        if ( isset( $_GET['_meta_key'] ) && !empty( $_GET['_meta_key'] ) ) {
            $meta_value = (int) $_GET['_meta_key'];
            $meta_query = $query->get( 'meta_query' );
            if ( empty( $meta_query ) ) {
                $meta_query = [];
            }
            $meta_query[] = [
                'key'   => '_meta_key',
                'value' => $meta_value,
            ];
            $query->set( 'meta_query', $meta_query );
        }
    }
}
