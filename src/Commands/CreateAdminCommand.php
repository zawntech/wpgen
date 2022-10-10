<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckIfComponentAlreadyExists;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Config;

class CreateAdminCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions;

    protected $options = [];

    protected static $defaultName = 'create:admin';

    protected function configure() {
        $this
            ->setDescription( 'Add a settings page and helper class to plugin.' )
            ->setHelp( 'Creates a plugin settings page and Settings helper class. Run from your plugin root.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Verify we're in a plugin directory.
        $this->isWPGenPluginDirectory( $input, $output );

        // Define our component name.
        $component = 'Admin\AdminComponent';

        // Prevent accidentally overwriting content.
        $this->checkIfComponentAlreadyExists($input, $output, $component);

        // Prepare settings page slug.
        $slug = strtolower( $this->options['plugin_name']['value'] );
        $slug = str_replace( ' ', '-', $slug );
        $this->options['settings_page_slug'] = ['value' => $slug];

        // Query options.
        //$options = Config::get()->adminOptions();
        //$this->mergeOptions( $options );
        //$this->querySecondaryOptions( $input, $output, $options );

        // Register in main class.
        $this->registerComponentInMainClass($input, $output, $component);
    }

    /**
     * Process and copy stub files to target directory.
     *
     * Fires after the interact function completes.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute( InputInterface $input, OutputInterface $output ) {

        // Output path.
        $component_path = getcwd()  . '/src/Admin/';
        if ( ! is_dir( $component_path ) ) {
            mkdir( $component_path );
        }
        $tab_path = $component_path . '/Tabs/';
        if ( ! is_dir( $tab_path ) ) {
            mkdir( $tab_path );
        }

        $stub_path = APP_ROOT . 'stubs/admin/';

        // An array of files to process.
        $files = [
            [
                'source' => 'admin-component.php',
                'target' => 'AdminComponent.php'
            ],
            [
                'source' => 'admin-settings-page.php',
                'target' => 'AdminSettingsPageContainer.php',
            ],
            [
                'source' => 'admin-settings-page-tab-abstract.php',
                'target' => 'AdminSettingsPageTabAbstract.php',
            ],
            [
                'source' => 'admin-main-settings-tab.php',
                'target' => 'Tabs/MainSettingsTab.php',
            ],
            [
                'source' => 'settings.php',
                'target' => 'Settings.php',
            ]
        ];

        $this->processFiles( $stub_path, $component_path, $files );

        return 0;
    }
}