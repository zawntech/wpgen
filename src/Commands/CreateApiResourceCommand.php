<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckIfComponentAlreadyExists;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterClassInConstructor;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Config;

class CreateApiResourceCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'create:api-resource';

    protected function configure() {
        $this
            ->setDescription( 'Create an API resource controller file.' )
            ->setHelp( 'Create an API resource controller file.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Verify we're in a generated plugin directory.
        $this->isWPGenPluginDirectory( $input, $output );

        // Verify component exists...
        $path = realpath( getcwd() . '/src/API/ApiComponent.php' );
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>API Component not found, run wpgen create:api first.</error>."] );
            exit;
        }

        // Query options.
        $options = Config::get()->apiResourceControllerOptions();
        $this->querySecondaryOptions( $input, $output, $options );
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
        $component_dir = getcwd()  . '/src/API/';

        $stub_path = APP_ROOT . 'stubs/api/';

        // An array of files to process.
        $files = [
            [
                'source' => 'resource-controller.php',
                'target' => $this->options['resource-plural-class-name']['value'] . 'Controller.php'
            ],
        ];

        $this->processFiles( $stub_path, $component_dir, $files );

        // Register class.
        $path = getcwd() . '/src/API/ApiComponent.php';
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$path not found.</error>."] );
        }

        $class = $this->options['resource-plural-class-name']['value'];
        $this->addToComponentConstructor( $input, $output, $class . 'Controller', $component_dir );

        return 0;
    }
}