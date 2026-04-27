<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractUserListTable;

class UserListTable extends AbstractUserListTable
{
    /**
     * Restrict customizations to specific roles via ?role=. Empty applies
     * to the All Users view. Example: protected $roles = ['subscriber'];
     */
    protected $roles = [];

    /**
     * Custom columns: column_key => Label.
     */
    protected $columns = [
        '_example_meta'  => 'Example Meta',
        '_example_count' => 'Example Count',
    ];

    /**
     * Built-in user columns to remove (e.g. 'posts').
     */
    protected $unset_columns = [];

    /**
     * Sortable columns: column_key => orderby_value (the value that arrives
     * in $_GET['orderby']; matched to an `orderby_<value>()` method below).
     */
    protected $sortable_columns = [
        '_example_meta' => 'example_meta',
    ];

    public function render_example_meta( $user_id ) {
        $value = get_user_meta( $user_id, '_example_meta', true );
        echo esc_html( $value !== '' ? $value : '—' );
    }

    public function render_example_count( $user_id ) {
        $count = (int) get_user_meta( $user_id, '_example_count', true );
        echo esc_html( (string) $count );
    }

    public function orderby_example_meta( \WP_User_Query $query ) {
        $query->set( 'meta_key', '_example_meta' );
        $query->set( 'orderby', 'meta_value' );
    }

    /**
     * Render filter UI inside the tablenav. Submitted via the "Filter"
     * button below; the abstract wraps this output in its own
     * `.alignleft.actions` div so it forms a distinct visual group.
     */
    protected function render_filters() {
        $current = isset( $_GET['_example_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['_example_filter'] ) ) : '';
        $options = [
            ''    => 'All',
            'one' => 'Option One',
            'two' => 'Option Two',
        ];
        ?>
        <label class="screen-reader-text" for="_example_filter">Filter</label>
        <select name="_example_filter" id="_example_filter">
            <?php foreach ( $options as $value => $label ) : ?>
                <option value="<?= esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
                    <?= esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php submit_button( 'Filter', '', '_example_filter_submit', false ); ?>
        <?php
    }

    /**
     * Apply filter UI selections to the user query.
     */
    public function process_filters( \WP_User_Query $query ) {
        if ( ! is_admin() ) {
            return;
        }

        $value = isset( $_GET['_example_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['_example_filter'] ) ) : '';
        if ( '' === $value ) {
            return;
        }

        $meta_query   = (array) $query->get( 'meta_query' );
        $meta_query[] = [
            'key'   => '_example_meta',
            'value' => $value,
        ];
        $query->set( 'meta_query', $meta_query );
    }
}
