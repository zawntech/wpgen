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

class ComponentTaxonomyCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:taxonomy';

    protected function configure() {
        $this
            ->setDescription( 'Add a custom taxonomy to a component.' )
            ->setHelp( 'Add a custom taxonomy to a component.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        // Query options.
        $options = Config::get()->taxonomyOptions();
        $this->querySecondaryOptions( $input, $output, $options );
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $stub_path = APP_ROOT . 'stubs/taxonomies/';
        $this->options['component_name'] = ['value' => $this->getComponentName()];
        $singular = $this->options['taxonomy_singular']['value'];

        $files = [
            [
                'source' => 'taxonomy.php',
                'target' => $singular . 'Taxonomy.php',
            ]
        ];

        $this->processFiles( $stub_path, getcwd() . '/', $files );

        // Register post type.
        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$file not found.</error>."] );
        }

        $this->addToComponentConstructor( $input, $output, $singular . 'Taxonomy' );

        return 0;
    }
}