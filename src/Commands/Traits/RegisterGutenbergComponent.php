<?php
namespace WPGen\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Bootstraps src/Gutenberg/GutenbergComponent.php in the target plugin
 * the first time create:block or create:pattern is run. Both commands
 * share a single component so blocks and patterns are registered in one
 * place instead of two parallel src/ subdirectories.
 */
trait RegisterGutenbergComponent
{
    public function maybeRegisterGutenbergComponent( InputInterface $input, OutputInterface $output ) {

        $component_path = getcwd() . '/src/Gutenberg/';

        if ( is_dir( $component_path ) ) {
            return;
        }

        mkdir( $component_path, 0775, true );

        $stub_path = APP_ROOT . 'stubs/gutenberg/';

        $files = [
            [
                'source' => 'gutenberg-component.php',
                'target' => 'GutenbergComponent.php',
            ],
            [
                'source' => 'register-blocks.php',
                'target' => 'RegisterBlocks.php',
            ],
            [
                'source' => 'register-patterns.php',
                'target' => 'RegisterPatterns.php',
            ],
            [
                'source' => 'editor-settings.php',
                'target' => 'EditorSettings.php',
            ],
        ];

        $this->processFiles( $stub_path, $component_path, $files );

        $component = 'Gutenberg\GutenbergComponent';
        $this->registerComponentInMainClass( $input, $output, $component );
    }
}
