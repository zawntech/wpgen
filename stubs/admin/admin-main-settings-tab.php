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
            <button type="submit" class="button button-primary">Save</button>
        </form>
        <?php
    }

    public function save() {

        // Process stringy values.
        $keys = [
            'example_option'
        ];

        $values = [];
        foreach( $keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = $_POST[$key];
                $value = stripslashes( $value );
                $value = filter_var( $value, FILTER_SANITIZE_STRING );
                $values[$key] = $value;
            }
        }

        // Process JSON values.
        $json_keys = [
        ];

        foreach( $json_keys as $key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = $_POST[$key];
                $value = stripslashes( $value );
                $value = json_decode( $value, true );
                $values[$key] = $value;
            }
        }

        Settings::get()->set( $values );

        $this->print_admin_notice( 'Great job!' );
    }
}