<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterClassInConstructor;
use WPGen\Config;

class ComponentAjaxControllerCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:ajax-controller';

    protected function configure() {
        $this
            ->setDescription( 'Add an AJAX controller to a component.' )
            ->setHelp( 'Generates an AjaxControllerAbstract base class and a concrete AJAX controller extending it.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        $options = Config::get()->ajaxControllerOptions();
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];

        $stub_path          = APP_ROOT . 'stubs/ajax-controller/';
        $abstract_stub_path = APP_ROOT . 'stubs/ajax-controller/abstract/';
        $abstract_dir       = dirname( getcwd() ) . '/Abstract/';

        $controller_class = $this->options['controller_class']['value'];

        // Generate AjaxControllerAbstract into src/Abstract/ (skips if already exists).
        if ( !file_exists( $abstract_dir ) ) {
            mkdir( $abstract_dir, 0755, true );
        }
        $this->processFiles( $abstract_stub_path, $abstract_dir, [
            ['source' => 'abstract-ajax-controller.php', 'target' => 'AbstractAjaxController.php'],
        ]);

        // Generate the concrete controller into the component directory.
        $this->processFiles( $stub_path, getcwd() . '/', [
            ['source' => 'ajax-controller.php', 'target' => $controller_class . 'AjaxController.php'],
        ]);

        $this->addToComponentConstructor( $input, $output, $controller_class . 'AjaxController' );

        return 0;
    }
}
