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

class ComponentUserListTableCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckIfComponentAlreadyExists,
        CheckWorkingDirectory,
        QueryOptions,
        RegisterClassInConstructor;

    protected $options = [];

    protected static $defaultName = 'component:user-list-table';

    protected function configure() {
        $this
            ->setDescription( 'Add a custom user list table (wp-admin Users page) to a component.' )
            ->setHelp( 'Adds an AbstractUserListTable to src/Abstract/ and a UserListTable.php in the component, then registers it in the component constructor.' );
        $this->loadPluginOptions();
    }

    public function interact( InputInterface $input, OutputInterface $output ) {
        if ( !$this->isComponentDirectory( $input, $output ) ) {
            $output->writeln( ['<error>Command must be run from within a component directory.</error>'] );
        }

        $this->options['component_name'] = ['value' => $this->getComponentName()];

        // No per-instance prompts — abstract is universal, concrete stub
        // ships with example columns/filters the developer customizes.
        $this->mergeOptions( [] );
    }

    public function execute( InputInterface $input, OutputInterface $output ) {

        $this->options['component_name'] = ['value' => $this->getComponentName()];

        $stub_path          = APP_ROOT . 'stubs/users/';
        $abstract_stub_path = APP_ROOT . 'stubs/users/abstract/';
        $abstract_dir       = dirname( getcwd() ) . '/Abstract/';

        if ( !file_exists( $abstract_dir ) ) {
            mkdir( $abstract_dir, 0755, true );
        }

        $this->processFiles( $abstract_stub_path, $abstract_dir, [
            ['source' => 'abstract-user-list-table.php', 'target' => 'AbstractUserListTable.php'],
        ]);

        $files = [
            [
                'source' => 'user-list-table.php',
                'target' => 'UserListTable.php',
            ],
        ];

        $this->processFiles( $stub_path, getcwd() . '/', $files );

        // Register in component constructor.
        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        if ( !file_exists( $path ) ) {
            $output->writeln( ["<error>$file not found.</error>."] );
        }

        $this->addToComponentConstructor( $input, $output, 'UserListTable' );

        return 0;
    }
}
