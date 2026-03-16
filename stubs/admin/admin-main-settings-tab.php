<?php
namespace {{ plugin_namespace }}\Admin\Tabs\{{ settings_page_namespace }};

use {{ plugin_namespace }}\Admin\Settings;
use {{ plugin_namespace }}\Abstract\AbstractAdminSettingsPageTab;
use AllegedWizard\WPAdminOptions\Fields\InputOption;
use AllegedWizard\WPAdminOptions\Structure\OptionsContainer;

/**
 * And example settings page tab.
 *
 * Class MainSettingsTab
 */
class MainSettingsTab extends AbstractAdminSettingsPageTab
{
    public $key = 'main';

    public $label = 'Main';

    public function render() {
        $settings = Settings::get();
        ?>
        <form method="post">
            <?php
            new OptionsContainer([
                'key'   => 'main',
                'title' => 'Main Settings',
                'fields' => function() use ( $settings ) {
                    new InputOption([
                        'key'   => 'example_option',
                        'label' => 'Example Option',
                        'value' => $settings->example_option()
                    ]);
                }
            ]);
            ?>
            <?php $this->nonce_field(); ?>
            <button type="submit" class="button button-primary">Save</button>
        </form>
        <?php
    }

    public function save() {

        if ( !$this->verify_nonce() ) {
            $this->print_admin_notice( 'Nonce error...', 'error' );
            return;
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