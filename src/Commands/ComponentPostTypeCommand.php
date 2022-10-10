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

class ComponentPostTypeCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:post-type';

    protected function configure() {
        $this
            ->setDescription( 'Add a custom post type to a component.' )
            ->setHelp( 'Add a custom post type to a component.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        // Query options.
        $options = Config::get()->postTypeOptions();
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];
        $stub_path = APP_ROOT . 'stubs/post-types/';

        $singular = str_replace( ' ', '', $this->options['post_type_singular']['value'] );
        $plural = str_replace( ' ', '', $this->options['post_type_plural']['value'] );

        $files = [
            [
                'source' => 'post-type.php',
                'target' => $singular . 'PostType.php',
            ],
            [
                'source' => 'post-type-model.php',
                'target' => $plural . '.php'
            ]
        ];

        $this->processFiles( $stub_path, getcwd() . '/', $files );

        // Register post type.
        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$file not found.</error>."] );
        }

        $this->addToComponentConstructor( $input, $output, $singular . 'PostType' );

        return 0;
    }
}