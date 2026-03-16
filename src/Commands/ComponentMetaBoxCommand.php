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

class ComponentMetaBoxCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:meta-box';

    protected function configure() {
        $this
            ->setDescription( 'Add a meta box to a component.' )
            ->setHelp( 'Add a meta box to a component.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        $options = Config::get()->metaBoxOptions();

        // Try to infer the PostType class from a *PostType.php file in the CWD.
        $inferred_class = $this->inferPostTypeClass( getcwd() );
        if ( $inferred_class ) {
            foreach ( $options as &$option ) {
                if ( $option['key'] === 'post_type_class' ) {
                    $option['value'] = $inferred_class;
                    $output->writeln( "<info>Inferred post type class: {$inferred_class}</info>" );
                    break;
                }
            }
            unset( $option );
        }

        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );

        // Derive meta_box_id and meta_box_class from the title.
        $title = $this->options['meta_box_title']['value'];
        $this->options['meta_box_id']    = ['value' => strtolower( str_replace( ' ', '_', $title ) )];
        $this->options['meta_box_class'] = ['value' => str_replace( ' ', '', ucwords( $title ) )];
    }

    protected function inferPostTypeClass( $dir ) {
        foreach ( glob( $dir . '/*PostType.php' ) as $file ) {
            if ( preg_match( "/const\s+KEY\s*=\s*'[^']+'/", file_get_contents( $file ) ) ) {
                return basename( $file, '.php' );
            }
        }
        return null;
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];
        $stub_path          = APP_ROOT . 'stubs/meta-boxes/';
        $abstract_stub_path = APP_ROOT . 'stubs/meta-boxes/abstract/';
        $abstract_dir       = dirname( getcwd() ) . '/Abstract/';

        $class = $this->options['meta_box_class']['value'];

        // Generate abstract base class into src/Abstract/ (skips if already exists).
        if ( !file_exists( $abstract_dir ) ) {
            mkdir( $abstract_dir, 0755, true );
        }

        $this->processFiles( $abstract_stub_path, $abstract_dir, [
            ['source' => 'abstract-meta-box.php', 'target' => 'AbstractMetaBox.php'],
        ]);

        // Generate concrete meta box file into the component directory.
        $this->processFiles( $stub_path, getcwd() . '/', [
            ['source' => 'meta-box.php', 'target' => $class . 'MetaBox.php'],
        ]);

        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$file not found.</error>."] );
        }

        $this->addToComponentConstructor( $input, $output, $class . 'MetaBox' );

        return 0;
    }
}
