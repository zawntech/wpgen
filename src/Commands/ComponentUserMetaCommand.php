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
use WPGen\Commands\Traits\RegisterClassInConstructor;
use WPGen\Config;

class ComponentUserMetaCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:user-meta';

    protected function configure() {
        $this
            ->setDescription( 'Add a custom user meta class to component.' )
            ->setHelp( 'Add a custom user meta class to component.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        $this->options['component_name'] = ['value' => $this->getComponentName()];

        // Query options.
        $options = [];
//        $options = Config::get()->metaBoxOptions();
//        $this->queryOptions( $input, $output, $options );
//        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];
        $stub_path = APP_ROOT . 'stubs/users/';

        $files = [
            [
                'source' => 'user-meta.php',
                'target' => 'CustomUserMeta.php',
            ]
        ];

        $this->processFiles( $stub_path, getcwd() . '/', $files );

        // Register post type.
        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$file not found.</error>."] );
        }

        $this->addToComponentConstructor( $input, $output, 'CustomUserMeta' );

        return 0;
    }
}