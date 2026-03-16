<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;

class CreateThemeComponentCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:theme-component';

    protected function configure() {
        $this
            ->setDescription( 'Add a Theme helper class to the plugin.' )
            ->setHelp( 'Creates a static Theme class for resolving template parts. Run from your plugin root.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {
        $this->isWPGenPluginDirectory( $input, $output );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {

        // Create src/Theme/ directory.
        $theme_path = getcwd() . '/src/Theme/';
        if ( ! is_dir( $theme_path ) ) {
            mkdir( $theme_path, 0755, true );
        }

        // Create assets/templates/ directory.
        $templates_path = getcwd() . '/assets/templates/';
        if ( ! is_dir( $templates_path ) ) {
            mkdir( $templates_path, 0755, true );
        }

        $stub_path = APP_ROOT . 'stubs/theme/';

        $this->processFiles( $stub_path, $theme_path, [
            ['source' => 'theme.php', 'target' => 'Theme.php'],
        ]);

        return 0;
    }
}
