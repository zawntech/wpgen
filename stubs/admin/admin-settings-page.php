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
    public $title = '{{ plugin_name }} Settings';

    /**
     * @var string
     */
    public $slug = '{{ settings_page_slug }}-settings';

    /**
     * @var string If defined, this settings page will be a
     * submenu page instead of a top level administration page.
     */
    public $parent_slug = '';

    /**
     * @var string Permission required to view settings page.
     */
    public $capability = 'manage_options';

    public function __construct() {
        add_action( 'admin_menu', [$this, 'register_settings_page'] );
        add_action( 'admin_init', [$this, 'save_tab_content'] );
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
     * @return array
     */
    public function get_tabs() {
        $slug = $this->slug;
        return apply_filters( 'admin_settings_page_tabs', [], $slug );
    }

    public function get_current_tab() {
        $tabs = $this->get_tabs();
        if ( empty( $tabs ) ) {
            return '';
        }
        $tab_values = array_keys( $tabs );
        if ( ! isset( $_GET['tab'] ) || empty( $_GET['tab'] ) ) {
            return $tab_values[0];
        }
        return $_GET['tab'];
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

    public function get_url( $key = '' ) {
        return admin_url() . 'admin.php?page=' . $this->slug . '&tab=' . $key;
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
        $slug = $this->slug;
        $tab = $this->get_current_tab();
        do_action( 'admin_settings_page_render_tab', $tab, $slug );
    }

    public function render_admin_notices() {
        $slug = $this->slug;
        $tab  = $this->get_current_tab();
        do_action( 'admin_settings_page_render_admin_notices', $tab, $slug );
    }

    public function save_tab_content() {
        if (
            empty( $_POST ) ||
            ! isset( $_GET['page'] ) ||
            $this->slug != $_GET['page']
        ) {
            return;
        }
        $slug = $this->slug;
        $tab  = $this->get_current_tab();
        do_action( 'admin_settings_page_save_tab', $tab, $slug );
    }
}
