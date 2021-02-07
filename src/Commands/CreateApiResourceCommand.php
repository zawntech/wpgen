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

class CreateApiResourceCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions;

    protected $options = [];

    protected static $defaultName = 'api:resource-controller';

    protected function configure() {
        $this
            ->setDescription( 'Create an API resource controller file.' )
            ->setHelp( 'Create an API resource controller file.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Verify we're in a plugin directory.
        $this->isWPGenPluginDirectory( $input, $output );

        // Define our component name.
        $component = 'API\ApiComponent';

        // Query options.
        $options = Config::get()->apiResourceControllerOptions();
        $this->querySecondaryOptions( $input, $output, $options );

        // Register in main class.
        // $this->registerComponentInMainClass($input, $output, $component);
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
        $component_path = getcwd()  . '/src/API/';
        if ( ! is_dir( $component_path ) ) {
            mkdir( $component_path );
        }

        $stub_path = APP_ROOT . 'stubs/api/';

        // An array of files to process.
        $files = [
            [
                'source' => 'resource-controller.php',
                'target' => $this->options['resource-plural-class-name']['value'] . 'Controller.php'
            ],
        ];

        $this->processFiles( $stub_path, $component_path, $files );
    }
}