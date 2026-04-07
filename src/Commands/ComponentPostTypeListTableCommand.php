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

class ComponentPostTypeListTableCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:post-type-list-table';

    protected function configure() {
        $this
            ->setDescription( 'Add a custom post type list table filter to a component.' )
            ->setHelp( 'Add a custom post type list table filter to a component.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        $options = Config::get()->postTypeListTableOptions();

        // Try to infer the singular post type name from a *PostType.php file in the CWD.
        $inferred = $this->inferPostTypeSingular( getcwd() );
        if ( $inferred ) {
            foreach ( $options as &$option ) {
                if ( $option['key'] === 'post_type_singular' ) {
                    $option['value'] = $inferred;
                    $output->writeln( "<info>Inferred post type: {$inferred}</info>" );
                    break;
                }
            }
            unset( $option );
        }

        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
    }

    protected function inferPostTypeSingular( $dir ) {
        foreach ( glob( $dir . '/*PostType.php' ) as $file ) {
            return str_replace( 'PostType.php', '', basename( $file ) );
        }
        return null;
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];
        $stub_path          = APP_ROOT . 'stubs/post-types/';
        $abstract_stub_path = APP_ROOT . 'stubs/post-types/abstract/';
        $abstract_dir       = dirname( getcwd() ) . '/Abstract/';

        $singular = $this->options['post_type_singular']['value'];

        if ( !file_exists( $abstract_dir ) ) {
            mkdir( $abstract_dir, 0755, true );
        }

        $this->processFiles( $abstract_stub_path, $abstract_dir, [
            ['source' => 'abstract-post-type-list-table.php', 'target' => 'AbstractPostTypeListTable.php'],
        ]);

        $files = [
            [
                'source' => 'post-type-list-table.php',
                'target' => $singular . 'PostTypeListTableFilter.php',
            ]
        ];

        $this->processFiles( $stub_path, getcwd() . '/', $files );

        // Register post type.
        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$file not found.</error>."] );
        }

        $this->addToComponentConstructor( $input, $output, $singular . 'PostTypeListTableFilter' );

        return 0;
    }
}