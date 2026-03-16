<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Config;

class ComponentApiCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckWorkingDirectory,
        QueryOptions;

    protected $options = [];

    protected static $defaultName = 'component:http-api';

    protected function configure() {
        $this
            ->setDescription( 'Add an HTTP API client to a component.' )
            ->setHelp( 'Generates an HttpClientAbstract base class and a concrete API client extending it.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        $options = Config::get()->componentApiOptions();
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];

        $stub_path          = APP_ROOT . 'stubs/http-client/';
        $abstract_stub_path = APP_ROOT . 'stubs/http-client/abstract/';
        $abstract_dir       = dirname( getcwd() ) . '/Abstract/';

        $api_class = $this->options['api_class']['value'];

        // Generate HttpClientAbstract into src/Abstract/ (skips if already exists).
        if ( !file_exists( $abstract_dir ) ) {
            mkdir( $abstract_dir, 0755, true );
        }
        $this->processFiles( $abstract_stub_path, $abstract_dir, [
            ['source' => 'abstract-http-client.php', 'target' => 'AbstractHttpClient.php'],
        ]);

        // Generate the concrete API client into the component directory.
        $this->processFiles( $stub_path, getcwd() . '/', [
            ['source' => 'http-client-api.php', 'target' => $api_class . 'Api.php'],
        ]);

        return 0;
    }
}
