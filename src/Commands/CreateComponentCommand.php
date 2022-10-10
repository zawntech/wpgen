<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckIfComponentAlreadyExists;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Config;

class CreateComponentCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        CheckWorkingDirectory,
        CheckIfComponentAlreadyExists,
        RegisterComponentInMainClass;

    protected $options = [];

    protected static $defaultName = 'create:component';

    protected function configure() {
        $this
            ->setDescription( 'Create a component in your plugin that encapsulates logically functionality.' )
            ->setHelp( 'Create a component in your plugin that encapsulates logically functionality.' );
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {
        $this->loadPluginOptions();
        $this->isWPGenPluginDirectory( $input, $output );

        // Query options.
        $options = Config::get()->componentOptions();
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
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

        $component = $this->options['component_name']['value'];
        $component_name = $component . '\\' . $component . 'Component';

        // Path to component new directory.
        $path = getcwd() . '/src/' . $component . '/';
        if ( ! is_dir( $path ) ) {
            mkdir( $path );
        }

        $this->checkIfComponentAlreadyExists( $input, $output, $component_name );
        $this->registerComponentInMainClass( $input, $output, $component_name );

        $stub_path = APP_ROOT . 'stubs/components/';

        // Copy files
        $files = [
            [
                'source' => 'component.php',
                'target' => $component . 'Component.php'
            ]
        ];

        $this->processFiles( $stub_path, $path, $files );

        return 0;
    }

    ////////////////////////////////////////////

}