<?php
namespace {{ plugin_namespace }}\Admin;

/**
 * {{ plugin_name }} Admin settings page container.
 * A container for settings page 'Tabs'.
 *
 * To add a new settings page (tab), extend the tab
 * abstract class and then instantiate on 'init' h ook.
 *
 * Class AdminSettingsPageContainer
 */
class AdminSettingsPageContainer
{
    /**
     * @var string Menu and page title.
     */
    public $title;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string If defined, this settings page will be a
     * submenu page instead of a top level administration page.
     */
    public $parent_slug;

    /**
     * @var string Permission required to view settings page.
     */
    public $capability;

    /**
     * @var AbstractAdminSettingsPageTab[]
     */
    public $tabs;

    /**
     * @param array $options {
     *     @type string $title       Menu and page title.
     *     @type string $slug        Page slug.
     *     @type string $parent_slug Parent menu slug for submenu pages.
     *     @type string $capability  Required capability. Default 'manage_options'.
     *     @type AbstractAdminSettingsPageTab[] $tabs Array of tab instances.
     * }
     */
    public function __construct( $options = [] ) {
        $this->title = $options['title'] ?? '';
        $this->slug = $options['slug'] ?? '';
        $this->parent_slug = $options['parent_slug'] ?? '';
        $this->capability = $options['capability'] ?? 'manage_options';
        $this->tabs = $options['tabs'] ?? [];

        add_action( 'admin_menu', [$this, 'register_settings_page'] );
        add_action( 'admin_init', [$this, 'save_tab_content'] );

        // Filter submenu items at a late priority, after the menu page is registered.
        if ( empty( $this->parent_slug ) ) {
            add_action( 'admin_menu', [$this, 'filter_admin_submenu'], 99 );
        }
    }

    public function register_settings_page() {
        if ( ! empty( $this->parent_slug ) ) {
            add_submenu_page(
                $this->parent_slug,
                $this->title,
                $this->title,
                $this->capability,
                $this->slug,
                [$this, 'render_settings_page']
            );
        } else {
            add_menu_page(
                $this->title,
                $this->title,
                $this->capability,
                $this->slug,
                [$this, 'render_settings_page']
            );
        }
    }

    /**
     * Replace the auto-generated submenu with tab-based navigation items.
     * Runs at priority 99 so the menu page is already registered.
     */
    public function filter_admin_submenu() {
        global $submenu;

        $tabs = apply_filters( '{{ plugin_filter_prefix }}admin_settings_tabs', $this->tabs, $this->slug );

        $items = [];
        foreach ( $tabs as $tab ) {
            $items[] = [
                $tab->label,
                $this->capability,
                'admin.php?page=' . $this->slug . '&tab=' . $tab->key,
            ];
        }

        $submenu[$this->slug] = $items;
    }

    /**
     * @return array Associative array of tab key => label.
     */
    public function get_tabs() {
        $tabs = [];
        foreach ( $this->tabs as $tab ) {
            $tabs[$tab->key] = $tab->label;
        }
        return $tabs;
    }

    /**
     * @return AbstractAdminSettingsPageTab|null
     */
    public function get_current_tab_instance() {
        if ( empty( $this->tabs ) ) {
            return null;
        }

        if ( isset( $_GET['tab'] ) ) {
            foreach ( $this->tabs as $tab ) {
                if ( $tab->key === $_GET['tab'] ) {
                    return $tab;
                }
            }
        }

        return $this->tabs[0];
    }

    public function get_current_tab() {
        $tab = $this->get_current_tab_instance();
        return $tab ? $tab->key : '';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h2><?= $this->title; ?></h2>
            <?php $this->render_admin_notices(); ?>
            <div class="wrap">
                <?php $this->render_tab_navigation(); ?>
                <div class="wrap"><?php $this->render_tab_content(); ?></div>
            </div>
        </div>
        <?php
    }

    /**
     * Get the admin URL for a given tab key.
     *
     * @param string $key Tab key.
     * @return string
     */
    public function get_url( $key = '' ) {
        return admin_url( 'admin.php?page=' . $this->slug . '&tab=' . $key );
    }

    public function render_tab_navigation() {
        $tabs = $this->get_tabs();
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $key => $label ) {
            $url = $this->get_url( $key );
            $active = $key === $this->get_current_tab() ? ' nav-tab-active' : '';
            printf( '<a href="%s" class="nav-tab%s">%s</a>', $url, $active, $label );
        }
        echo '</h2>';
    }

    public function render_tab_content() {
        $tab = $this->get_current_tab_instance();
        if ( $tab ) {
            $tab->render();
        }
    }

    public function render_admin_notices() {
        $tab = $this->get_current_tab_instance();
        if ( $tab ) {
            $tab->print_notices();
        }
    }

    public function save_tab_content() {
        if (
            empty( $_POST ) ||
            ! isset( $_GET['page'] ) ||
            $this->slug != $_GET['page']
        ) {
            return;
        }
        $tab = $this->get_current_tab_instance();
        if ( $tab ) {
            $tab->save();
        }
    }
}
