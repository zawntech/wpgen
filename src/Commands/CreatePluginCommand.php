<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;

class CreatePluginCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:plugin';

    protected function configure() {
        $this
            ->setDescription( 'Create a new WordPress plugin.' )
            ->setHelp( 'This command allows you to create a new WordPress plugin. It should be executed from a WordPress plugins directory.' );
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {
        $this->loadPluginOptions();
        // Check if we're in a 'plugins' directory.
        $this->isWPPluginsDirectory( $input, $output );
        $this->queryOptions( $input, $output, $this->options );
        $this->confirmOptions( $input, $output, $this->options );
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
        $path = getcwd();

        // Get plugin dir name.
        $plugin_dir_name = $this->options['plugin_name']['value'];
        $plugin_dir_name = trim( $plugin_dir_name );
        $plugin_dir_name = str_replace( ' ', '-', $plugin_dir_name );
        $plugin_dir_name = strtolower( $plugin_dir_name );

        // Path.
        $path = $path . '/' . $plugin_dir_name;
        if ( ! is_dir( $path ) ) {
            mkdir( $path );
            mkdir( $path . '/src' );
            mkdir( $path . '/src/Setup' );
        }

        $target_path = $path . '/';

        $stub_path = APP_ROOT . 'stubs/plugins/';

        // An array of files to process.
        $files = [
            [
                'source' => 'composer.json',
                'target' => 'composer.json'
            ],
            [
                'source' => '.gitignore',
                'target' => '.gitignore'
            ],
            [
                'source' => 'main-plugin-file.php',
                'target' => $plugin_dir_name . '.php',
            ],
            [
                'source' => 'main-plugin-class.php',
                'target' => '/src/' . $this->options['plugin_main_class']['value'] . '.php',
            ],
            [
                'source' => 'setup-plugin-component.php',
                'target' => '/src/Setup/SetupComponent.php',
            ],
            [
                'source' => 'setup-plugin-enqueue-assets.php',
                'target' => '/src/Setup/EnqueueAssets.php',
            ],
            [
                'source' => 'setup-plugin-activate-plugin.php',
                'target' => '/src/Setup/ActivatePlugin.php',
            ],
            [
                'source' => 'setup-plugin-deactivate-plugin.php',
                'target' => '/src/Setup/DeactivatePlugin.php',
            ]
        ];

        // Replace
        $this->processFiles( $stub_path, $target_path, $files );


        // Store config
        $options = array_map( function( $opt ) {
            return $opt['value'];
        }, $this->options );
        $json = json_encode( $options, JSON_PRETTY_PRINT );
        file_put_contents( $target_path . '/wpgen.config.json', $json );
    }

    ////////////////////////////////////////////

}