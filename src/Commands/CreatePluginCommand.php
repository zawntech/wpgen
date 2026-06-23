<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
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
        $this->inferPluginIdentifiers();
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
            mkdir( $path . '/assets/js/src/frontend', 0775, true );
            mkdir( $path . '/assets/js/src/admin', 0775, true );
            mkdir( $path . '/assets/sass', 0775, true );
            mkdir( $path . '/assets/build', 0775, true );
            mkdir( $path . '/assets/scripts', 0775, true );
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
            ],
            [
                'source' => 'setup-plugin-svg-support.php',
                'target' => '/src/Setup/SvgSupport.php',
            ]
        ];

        // Replace
        $this->processFiles( $stub_path, $target_path, $files );

        // Webpack configuration and asset stubs.
        $webpack_stub_path = APP_ROOT . 'stubs/webpack/';
        $webpack_files = [
            [
                'source' => 'webpack.config.js',
                'target' => 'webpack.config.js',
            ],
            [
                'source' => 'package.json',
                'target' => 'package.json',
            ],
            [
                'source' => 'frontend-index.js',
                'target' => 'assets/js/src/frontend/index.js',
            ],
            [
                'source' => 'admin-index.js',
                'target' => 'assets/js/src/admin/index.js',
            ],
            [
                'source' => 'frontend.scss',
                'target' => 'assets/sass/frontend.scss',
            ],
            [
                'source' => 'admin.scss',
                'target' => 'assets/sass/admin.scss',
            ],
            [
                'source' => '_variables.scss',
                'target' => 'assets/sass/_variables.scss',
            ],
            [
                'source' => 'build-zip.js',
                'target' => 'assets/scripts/build-zip.js',
            ],
        ];
        $this->processFiles( $webpack_stub_path, $target_path, $webpack_files );

        // Store config
        $options = array_map( function( $opt ) {
            return $opt['value'];
        }, $this->options );
        $json = json_encode( $options, JSON_PRETTY_PRINT );
        file_put_contents( $target_path . '/wpgen.config.json', $json );

        // Run composer install in the new plugin directory.
        $output->writeln( '' );
        $output->writeln( '<info>Running composer install...</info>' );

        $process = new Process( ['composer', 'install'], $path );
        $process->setTimeout( 120 );
        $process->run( function ( $type, $buffer ) use ( $output ) {
            $output->write( $buffer );
        } );

        if ( ! $process->isSuccessful() ) {
            $output->writeln( '<error>composer install failed. Run it manually in ' . $path . '</error>' );
        }

        $output->writeln( '' );
        $output->writeln( '<info>To get started, run:</info>' );
        $output->writeln( '  cd ' . $plugin_dir_name );

        return 0;
    }

    /**
     * Convert a plugin name to PascalCase.
     *
     * @param string $name
     * @return string
     */
    protected function toPascalCase( $name ) {
        return str_replace( ' ', '', ucwords( $name ) );
    }

    /**
     * Convert a plugin name to snake_case.
     *
     * @param string $name
     * @return string
     */
    protected function toSnakeCase( $name ) {
        return strtolower( str_replace( ' ', '_', $name ) );
    }

    /**
     * Infer plugin-specific identifiers from the plugin name and confirm with the user.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function inferPluginIdentifiers() {
        $plugin_name = $this->options['plugin_name']['value'];

        // Derive identifiers from plugin name.
        $this->options['plugin_text_domain']['value']     = strtolower( str_replace( ' ', '-', $plugin_name ) );
        $this->options['plugin_namespace']['value']       = $this->toPascalCase( $plugin_name );
        $this->options['plugin_constants_prefix']['value'] = strtoupper( str_replace( ' ', '_', $plugin_name ) ) . '_';
        $this->options['plugin_main_class']['value']      = $this->toPascalCase( $plugin_name );
        $this->options['plugin_filter_prefix']['value']   = $this->toSnakeCase( $plugin_name ) . '_';

        // Derive composer package name from vendor + text domain.
        $vendor = $this->options['composer_vendor_name']['value'] ?? '';
        $text_domain = $this->options['plugin_text_domain']['value'];
        $this->options['composer_package_name']['value'] = $vendor . '/' . $text_domain;
    }
}