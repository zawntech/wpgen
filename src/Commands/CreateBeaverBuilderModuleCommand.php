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

class CreateBeaverBuilderModuleCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:bb-module';

    protected function configure() {
        $this
            ->setDescription( 'Create a beaver builder module.' )
            ->setHelp( 'Creates a beaver builder module and accompanying plugin component if none exists' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Check if wpgen.config.json exists.
        $this->isWPGenPluginDirectory( $input, $output );

        // Query options.
        $options = Config::get()->bbModuleOptions();
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );

        // Register beaver builder component if it does not exist.
        $this->maybeRegisterBeaverBuilderComponent( $input, $output );

        // Check if module files already exist (prevent overwrite).
        $this->alreadyExists( $input, $output );
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
        $this->createModuleDirectories();
        $this->copyComponentFiles();
        $this->copyModuleFiles();
        $this->registerModule( $input, $output );
    }

    ////////////////////////////////////////////

    public function registerModule( InputInterface $input, OutputInterface $output ) {

        // Path to component class.
        $path = getcwd() . '/src/BeaverBuilder/BeaverBuilderComponent.php';
        if ( !file_exists( $path ) ) {
            $output->writeln( ["Error: $path does not exist."] );
            exit;
        }

        // Read file.
        $contents = file_get_contents( $path );

        // Get string positions of modules() function.
        $start = strpos( $contents, 'public function modules() {' );
        $start = strpos( $contents, '[', $start );
        $end = strpos( $contents, ']', $start );

        $search = substr( $contents, $start, ( $end - $start ) );

        $lines = explode( "\n", $search );
        $tail = array_pop( $lines );
        $value = $this->options['module_class']['value'];
        $lines[] = "            Modules\\" . $value . "Module::class,";
        $lines[] = $tail;

        $replace = implode( "\n", $lines );
        $contents = substr_replace( $contents, $replace, $start, strlen( $search ) );

        file_put_contents( $path, $contents );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function alreadyExists( InputInterface $input, OutputInterface $output ) {

        $path = getcwd() . '/bb-modules/' . $this->options['module_dir']['value'] . '/';

        if ( !is_dir( $path ) ) {
            return;
        }

        // Prompt user if anything items are wrong.
        $name = $this->options['module_name']['value'];
        $helper = $this->getHelper( 'question' );
        $question = new ConfirmationQuestion( "<error>\nA {$name} module appears to already exist. \nFiles will be overwritten. Continue? (y/n)</error>", false );

        if ( !$helper->ask( $input, $output, $question ) ) {
            $output->writeln( ['Quitting...'] );
            exit;
        }

    }

    public function copyModuleFiles() {

        /////////////////////

        $stub_path = APP_ROOT . 'stubs/beaver-builder/';

        $module_path = getcwd() . '/bb-modules/' . $this->options['module_dir']['value'] . '/';

        // An array of files to process.
        $files = [
            // Main module html
            [
                'source' => 'frontend.php',
                'target' => 'includes/frontend.php'
            ],

            // CSS
            [
                'source' => 'frontend.css',
                'target' => 'css/frontend.css'
            ],
            [
                'source' => 'frontend.css',
                'target' => 'css/frontend.scss'
            ],
            [
                'source' => 'frontend.responsive.css',
                'target' => 'css/frontend.responsive.css'
            ],
            [
                'source' => 'frontend.responsive.css',
                'target' => 'css/frontend.responsive.scss'
            ],
            [
                'source' => 'frontend.css.php',
                'target' => 'includes/frontend.css.php'
            ],

            // JS
            [
                'source' => 'frontend.js',
                'target' => 'js/frontend.js'
            ],
            [
                'source' => 'frontend.js.php',
                'target' => 'includes/frontend.js.php'
            ],

            // Sass compiler
            [
                'source' => 'build-scss.js',
                'target' => '../../assets/scripts/build-scss.js',
            ],
            [
                'source' => 'package.json',
                'target' => '../../package.json',
            ]
        ];

        $this->processFiles( $stub_path, $module_path, $files );
    }

    public function copyComponentFiles() {

        $stub_path = APP_ROOT . 'stubs/beaver-builder/';
        $component_path = getcwd() . '/src/BeaverBuilder/';

        if ( !is_dir( $component_path . 'Modules' ) ) {
            mkdir( $component_path . 'Modules', 0775, true );
        }

        // An array of files to process.
        $files = [
            [
                'source' => 'custom-module.php',
                'target' => 'Modules/' . $this->options['module_class']['value'] . 'Module.php'
            ]
        ];

        $this->processFiles( $stub_path, $component_path, $files );
    }

    public function createModuleDirectories() {

        // Create module directories
        $path = getcwd() . '/bb-modules/' . $this->options['module_dir']['value'] . '/';

        $dirs = [
            'js',
            'css',
            'includes',
            '../../assets/scripts'
        ];

        foreach ( $dirs as $dir ) {
            if ( !is_dir( $path . $dir ) ) {
                mkdir( $path . $dir, 0775, true );
            }
        }
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