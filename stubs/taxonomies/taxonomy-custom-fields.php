<?php
namespace {{ plugin_namespace }}\{{ component_name }};

class {{ taxonomy_singular }}TaxonomyCustomFields
{
    public function __construct() {
        $taxonomies = ['{{ taxonomy_key }}'];
        foreach( $taxonomies as $taxonomy ) {
            add_action( "edited_{$taxonomy}", [$this, 'save'], 10, 2 );
            add_action( "created_{$taxonomy}", [$this, 'save'], 10, 2 );
            add_action( "{$taxonomy}_add_form_fields", [$this, 'add_fields'] );
            add_filter( "manage_edit-{$taxonomy}_columns", [$this, 'columns'] );
            add_action( "{$taxonomy}_edit_form_fields", [$this, 'edit_fields'], 10, 2 );
            add_filter( "manage_{$taxonomy}_custom_column", [$this, 'column_content'], 10, 3 );
        }
    }

    public function add_fields( $taxonomy ) {
        ?>
        <div class="form-field">
            <label for="_order">Order</label>
            <input type="number" step="1" name="_order" value="0">
            <p>The order of the grouping. The lowest value shows up first, the highest value shows up last.</p>
        </div>
        <?php
    }

    public function edit_fields( \WP_Term $term, $taxonomy ) {
        $order = get_term_meta( $term->term_id, '_order', true );
        ?>
        <tr class="form-field">
            <th scope="row"><label for="_order">Order</label></th>
            <td><input name="_order" id="_order" type="number" value="<?= esc_attr( $order ); ?>" step="1">
                <p class="description">The order of the grouping. The lowest value shows up first, the highest value shows up last.</p></td>
        </tr>
        <?php
    }

    public function save( $term_id, $tt_id ) {
        $keys = [
            '_order'
        ];
        foreach( $keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = $_POST[$key];
                update_term_meta( $term_id, $key, $value );
            }
        }
    }

    public function columns( $columns = [] ) {
        $columns['_order'] = 'Order';
        return $columns;
    }

    public function column_content( $content, $column_name, $term_id ) {
        switch ( $column_name ) {
            case '_order':
                $meta = get_term_meta( $term_id, '_order', true );
                $content = $meta;
                break;
        }
        return $content;
    }
}