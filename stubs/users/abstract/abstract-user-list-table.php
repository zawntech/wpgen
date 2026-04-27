<?php
namespace {{ plugin_namespace }}\Abstract;

/**
 * Base for adding custom columns / sorting / filters to the wp-admin Users
 * list table (users.php).
 *
 * Subclasses declare:
 *   - $columns          map of column_key => Label to add
 *   - $unset_columns    list of column keys to remove
 *   - $sortable_columns map of column_key => orderby_value to make sortable
 *   - $roles            optional list of role slugs; if set, customizations
 *                       only apply when the list is filtered to one of them
 *
 * For each added column, define a `render_<column>( $user_id )` method.
 * For each sortable column, define an `orderby_<value>( WP_User_Query $q )` method.
 * Override `render_filters()` and `process_filters()` to add filter UI.
 */
abstract class AbstractUserListTable
{
    /**
     * @var string[] If non-empty, customizations only apply when the users
     * list is filtered to one of these role slugs (?role=...).
     */
    protected $roles = [];

    protected $columns = [];
    protected $unset_columns = [];
    protected $sortable_columns = [];

    public function __construct() {
        add_filter( 'manage_users_columns', [$this, 'columns'] );
        add_filter( 'manage_users_sortable_columns', [$this, 'sortable_columns'] );
        add_filter( 'manage_users_custom_column', [$this, 'column_content'], 10, 3 );
        add_action( 'pre_get_users', [$this, 'orderby'] );
        add_action( 'pre_get_users', [$this, 'process_filters'] );
        add_action( 'restrict_manage_users', [$this, 'restrict_manage_users'] );
    }

    /**
     * Whether this table's customizations apply to the current request.
     */
    protected function applies() {
        if ( empty( $this->roles ) ) {
            return true;
        }
        $role = isset( $_GET['role'] ) ? sanitize_key( $_GET['role'] ) : '';
        return in_array( $role, $this->roles, true );
    }

    public function columns( $columns ) {
        if ( ! $this->applies() ) {
            return $columns;
        }
        $columns = array_merge( $columns, $this->columns );
        foreach ( $this->unset_columns as $column ) {
            unset( $columns[$column] );
        }
        return $columns;
    }

    public function sortable_columns( $columns ) {
        if ( ! $this->applies() ) {
            return $columns;
        }
        return array_merge( $columns, $this->sortable_columns );
    }

    /**
     * `manage_users_custom_column` is a single global filter that fires for
     * every custom user column, so we must short-circuit on columns we did
     * not register before dispatching to a render method.
     */
    public function column_content( $output, $column_name, $user_id ) {
        if ( ! isset( $this->columns[$column_name] ) ) {
            return $output;
        }

        $method = 'render_' . ltrim( $column_name, '_' );

        if ( ! method_exists( $this, $method ) ) {
            throw new \BadMethodCallException( "No render method found for column '{$column_name}'. Expected method: {$method}()" );
        }

        ob_start();
        $this->$method( $user_id );
        return ob_get_clean();
    }

    public function orderby( \WP_User_Query $query ) {
        if ( ! is_admin() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( empty( $orderby ) || ! is_string( $orderby ) ) {
            return;
        }

        $method = 'orderby_' . ltrim( $orderby, '_' );

        if ( ! method_exists( $this, $method ) ) {
            return;
        }

        $this->$method( $query );
    }

    /**
     * `restrict_manage_users` fires twice (top + bottom). Render once, at top.
     *
     * Core's `extra_tablenav()` opens a single `<div class="alignleft actions">`
     * around the "Change role" controls AND the contents of this hook, which
     * visually merges our filter UI with core's role-change UI. We close core's
     * div, render ours in a sibling div, then reopen an (empty) one for core's
     * trailing `</div>` to match — yielding distinct inline-block groups.
     */
    public function restrict_manage_users( $which ) {
        if ( ! $this->applies() ) {
            return;
        }
        if ( 'top' !== $which ) {
            return;
        }
        ?>
        </div>
        <div class="alignleft actions">
            <?php $this->render_filters(); ?>
        </div>
        <div class="alignleft actions">
        <?php
    }

    /**
     * Subclasses override to render filter UI (e.g. <select> dropdowns).
     * The wrapping <form> is provided by core; render fields only.
     */
    protected function render_filters() {}

    /**
     * Subclasses override to apply filters to the user query based on $_GET.
     */
    public function process_filters( \WP_User_Query $query ) {}
}
