<?php
namespace {{ plugin_namespace }}\Admin\Tabs;

use {{ plugin_namespace }}\Admin\Settings;
use {{ plugin_namespace }}\Admin\AdminSettingsPageTabAbstract;
use Zawntech\WPAdminOptions\InputOption;

/**
 * And example settings page tab.
 *
 * Class MainSettingsTab
 */
class MainSettingsTab extends AdminSettingsPageTabAbstract
{
    public $key = 'main';

    public $label = 'Main';

    public function render() {
        $settings = Settings::get();
        ?>
        <form method="post">
            <table class="form-table">
                <?php
                new InputOption([
                    'key' => 'example_option',
                    'label' => 'Example Option',
                    'value' => $settings->example_option()
                ]);
                ?>
            </table>
            <?php $this->nonce_field(); ?>
            <button type="submit" class="button button-primary">Save</button>
        </form>
        <?php
    }

    public function save() {

        if ( !$this->verify_nonce() ) {
            $this->print_admin_notice( 'Nonce error...', 'error' );
        }

        // Process stringy keys.
        $this->save_strings([
            'example_option'
        ]);

        // Process JSON keys.
        $this->save_json([]);

        $this->print_admin_notice( 'Settings saved!' );
    }
}