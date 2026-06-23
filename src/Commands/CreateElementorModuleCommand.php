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

class CreateElementorModuleCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:elementor-module';

    protected function configure() {
        $this
            ->setDescription( 'Create an Elementor widget.' )
            ->setHelp( 'Creates an Elementor widget and accompanying plugin component if none exists' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Check if wpgen.config.json exists.
        $this->isWPGenPluginDirectory( $input, $output );

        // Query options.
        $options = Config::get()->elementorModuleOptions();
        $this->queryOptions( $input, $output, $options );
        $this->inferModuleIdentifiers( $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );

        // Register Elementor component if it does not exist.
        $this->maybeRegisterElementorComponent( $input, $output );

        // Check if widget files already exist (prevent overwrite).
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
        $this->registerWidget( $input, $output );
        return 0;
    }

    /**
     * Derive directory/class/slug identifiers from the widget name and
     * apply defaults for the optional fields.
     */
    protected function inferModuleIdentifiers( &$options ) {
        $widget_name = '';
        foreach ( $options as &$opt ) {
            if ( $opt['key'] === 'widget_name' ) {
                $widget_name = $opt['value'];
            }
        }
        unset( $opt );

        $widget_dir = strtolower( str_replace( ' ', '-', $widget_name ) );
        $text_domain = $this->options['plugin_text_domain']['value'] ?? '';

        // Apply defaults for optional fields.
        foreach ( $options as &$opt ) {
            if ( $opt['key'] === 'widget_icon' && empty( $opt['value'] ) ) {
                $opt['value'] = 'eicon-code';
            }
            if ( $opt['key'] === 'widget_category' && empty( $opt['value'] ) ) {
                $opt['value'] = $text_domain;
            }
            if ( $opt['key'] === 'widget_keywords' ) {
                $opt['value'] = $this->formatKeywords( $opt['value'] );
            }
        }
        unset( $opt );

        // Kebab-case directory name.
        $options[] = [
            'key' => 'widget_dir',
            'label' => 'Widget Directory Name',
            'value' => $widget_dir,
            'type' => 'string',
        ];

        // PascalCase class name (without the Widget suffix; the stub appends it).
        $options[] = [
            'key' => 'widget_class',
            'label' => 'Widget Class Name',
            'value' => str_replace( ' ', '', ucwords( $widget_name ) ),
            'type' => 'string',
        ];

        // Globally-unique widget name used by get_name() (text-domain prefixed).
        $slug = $text_domain ? $text_domain . '-' . $widget_dir : $widget_dir;
        $options[] = [
            'key' => 'widget_slug',
            'label' => 'Widget Slug (get_name)',
            'value' => $slug,
            'type' => 'string',
        ];

        // Asset handle used for wp_register_style/script.
        $options[] = [
            'key' => 'widget_handle',
            'label' => 'Widget Asset Handle',
            'value' => $slug,
            'type' => 'string',
        ];
    }

    /**
     * Turn a comma-separated keyword string into a PHP-ready quoted list.
     * "cards, features" => "'cards', 'features'"
     *
     * @param string $raw
     * @return string
     */
    protected function formatKeywords( $raw ) {
        $raw = (string) $raw;
        if ( trim( $raw ) === '' ) {
            return '';
        }
        $parts = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
        $parts = array_map( function ( $kw ) {
            return "'" . str_replace( "'", '', $kw ) . "'";
        }, $parts );
        return implode( ', ', $parts );
    }

    public function registerWidget( InputInterface $input, OutputInterface $output ) {

        // Path to component class.
        $path = getcwd() . '/src/Elementor/ElementorComponent.php';
        if ( !file_exists( $path ) ) {
            $output->writeln( ["Error: $path does not exist."] );
            exit;
        }

        // Read file.
        $contents = file_get_contents( $path );

        // Get string positions of widgets() function.
        $start = strpos( $contents, 'public function widgets() {' );
        $start = strpos( $contents, '[', $start );
        $end = strpos( $contents, ']', $start );

        $search = substr( $contents, $start, ( $end - $start ) );

        $lines = explode( "\n", $search );
        $tail = array_pop( $lines );
        $value = $this->options['widget_class']['value'];
        $lines[] = "            Widgets\\" . $value . "Widget::class,";
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

        $path = getcwd() . '/elementor-widgets/' . $this->options['widget_dir']['value'] . '/';

        if ( !is_dir( $path ) ) {
            return;
        }

        // Prompt user if anything items are wrong.
        $name = $this->options['widget_name']['value'];
        $helper = $this->getHelper( 'question' );
        $question = new ConfirmationQuestion( "<error>\nA {$name} widget appears to already exist. \nFiles will be overwritten. Continue? (y/n)</error>", false );

        if ( !$helper->ask( $input, $output, $question ) ) {
            $output->writeln( ['Quitting...'] );
            exit;
        }

    }

    public function copyModuleFiles() {

        $stub_path = APP_ROOT . 'stubs/elementor/';

        $module_path = getcwd() . '/elementor-widgets/' . $this->options['widget_dir']['value'] . '/';

        // An array of files to process.
        $files = [
            // SCSS (compiled in place to css/frontend.css by webpack).
            [
                'source' => 'frontend.scss',
                'target' => 'css/frontend.scss'
            ],
            [
                'source' => '_variables.scss',
                'target' => 'css/_variables.scss'
            ],

            // JS
            [
                'source' => 'frontend.js',
                'target' => 'js/frontend.js'
            ],
        ];

        $this->processFiles( $stub_path, $module_path, $files );
    }

    public function copyComponentFiles() {

        $stub_path = APP_ROOT . 'stubs/elementor/';
        $component_path = getcwd() . '/src/Elementor/';

        if ( !is_dir( $component_path . 'Widgets' ) ) {
            mkdir( $component_path . 'Widgets', 0775, true );
        }

        // An array of files to process.
        $files = [
            [
                'source' => 'elementor-widget.php',
                'target' => 'Widgets/' . $this->options['widget_class']['value'] . 'Widget.php'
            ]
        ];

        $this->processFiles( $stub_path, $component_path, $files );
    }

    public function createModuleDirectories() {

        // Create widget asset directories.
        $path = getcwd() . '/elementor-widgets/' . $this->options['widget_dir']['value'] . '/';

        $dirs = [
            'js',
            'css',
        ];

        foreach ( $dirs as $dir ) {
            if ( !is_dir( $path . $dir ) ) {
                mkdir( $path . $dir, 0775, true );
            }
        }
    }

    /**
     * Copy and register the ElementorComponent class in the main plugin
     * file, if it does not exist.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function maybeRegisterElementorComponent( InputInterface $input, OutputInterface $output ) {

        // Output path.
        $component_path = getcwd() . '/src/Elementor/';

        if ( !is_dir( $component_path ) ) {
            mkdir( $component_path );
        } else {
            // Already exists, return so we don't over write files.
            return;
        }

        // Create widgets asset dir if not exists.
        $widgets_dir = getcwd() . '/elementor-widgets';
        if ( !is_dir( $widgets_dir ) ) {
            mkdir( $widgets_dir );
        }


        $stub_path = APP_ROOT . 'stubs/elementor/';

        // An array of files to process.
        $files = [
            [
                'source' => 'elementor-component.php',
                'target' => 'ElementorComponent.php'
            ]
        ];

        $this->processFiles( $stub_path, $component_path, $files );

        // Define our component name.
        $component = 'Elementor\ElementorComponent';
        $this->registerComponentInMainClass( $input, $output, $component );
    }
}
