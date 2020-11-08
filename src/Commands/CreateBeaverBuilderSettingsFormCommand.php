<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use WPGen\Commands\Traits\CheckIfComponentAlreadyExists;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Config;

class CreateBeaverBuilderSettingsFormCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:bb-settings-form';

    protected function configure() {
        $this
            ->setDescription( 'Create a beaver builder settings form.' )
            ->setHelp( 'Creates a beaver builder settings form and accompanying plugin component if none exists' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Check if wpgen.config.json exists.
        $this->isWPGenPluginDirectory( $input, $output );

        // Query options.
        $options = Config::get()->bbFormSettingsOptions();
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );

        // Register beaver builder component if it does not exist.
        $this->maybeRegisterBeaverBuilderComponent( $input, $output );
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
        $this->copySettingsFormFile();
        $this->registerSettingsForm( $input, $output );
    }

    ////////////////////////////////////////////

    public function registerSettingsForm( InputInterface $input, OutputInterface $output ) {

        // Path to component class.
        $path = getcwd() . '/src/BeaverBuilder/BeaverBuilderComponent.php';
        if ( !file_exists( $path ) ) {
            $output->writeln( ["Error: $path does not exist."] );
            exit;
        }

        // Read file.
        $contents = file_get_contents( $path );

        // Get string positions of modules() function.
        $start = strpos( $contents, 'public function settings_forms() {' );
        $start = strpos( $contents, '[', $start );
        $end = strpos( $contents, ']', $start );

        $search = substr( $contents, $start, ( $end - $start ) );

        $lines = explode( "\n", $search );
        $tail = array_pop( $lines );
        $lines[] = "            {$this->options['form_class']['value']}SettingsForm::class,";
        $lines[] = $tail;

        $replace = implode( "\n", $lines );

        $contents = str_replace( $search, $replace, $contents );

        file_put_contents( $path, $contents );
    }

    public function copySettingsFormFile() {

        $stub_path = APP_ROOT . 'stubs/beaver-builder/';
        $component_path = getcwd() . '/src/BeaverBuilder/';

        // An array of files to process.
        $files = [
            [
                'source' => 'custom-settings-form.php',
                'target' => $this->options['form_class']['value'] . 'SettingsForm.php'
            ]
        ];

        $this->processFiles( $stub_path, $component_path, $files );
    }

    /**
     * Copy and register the BeaverBuilderComponent
     * class in the main plugin file, if it does not exist.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function maybeRegisterBeaverBuilderComponent( InputInterface $input, OutputInterface $output ) {

        // Output path.
        $component_path = getcwd() . '/src/BeaverBuilder/';

        if ( !is_dir( $component_path ) ) {
            mkdir( $component_path );
        } else {
            // Already exists, return so we don't over write files.
            return;
        }

        // Create modules dir if not exists.
        $modules_dir = getcwd() . '/bb-modules';
        if ( !is_dir( $modules_dir ) ) {
            mkdir( $modules_dir );
        }


        $stub_path = APP_ROOT . 'stubs/beaver-builder/';

        // An array of files to process.
        $files = [
            [
                'source' => 'bb-component.php',
                'target' => 'BeaverBuilderComponent.php'
            ]
        ];

        $this->processFiles( $stub_path, $component_path, $files );

        // Define our component name.
        $component = 'BeaverBuilder\BeaverBuilderComponent';
        $this->registerComponentInMainClass( $input, $output, $component );
    }
}