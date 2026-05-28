<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Commands\Traits\RegisterGutenbergComponent;
use WPGen\Config;

class CreatePatternCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        RegisterGutenbergComponent,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:pattern';

    protected function configure() {
        $this
            ->setDescription( 'Create a Gutenberg block pattern.' )
            ->setHelp( 'Scaffolds a block pattern PHP file under patterns/ and bootstraps the shared GutenbergComponent on first run.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        $this->isWPGenPluginDirectory( $input, $output );

        $options = Config::get()->patternOptions();
        $this->queryOptions( $input, $output, $options );
        $this->inferPatternIdentifiers( $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );

        $this->maybeRegisterGutenbergComponent( $input, $output );

        $this->alreadyExists( $input, $output );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {
        $this->createPatternsDirectory();
        $this->copyPatternFiles();

        $slug = $this->options['pattern_slug']['value'];
        $output->writeln( '' );
        $output->writeln( "<info>Pattern '{$slug}' scaffolded at patterns/{$slug}.php</info>" );
        $output->writeln( 'Edit the file body with your block markup, then look for the pattern in the editor inserter.' );

        return 0;
    }

    /**
     * Derive a kebab-case slug from the human-entered title. The slug is
     * used for both the filename and the second half of the pattern's
     * registered name (e.g. {text-domain}/{slug}).
     */
    protected function inferPatternIdentifiers( &$options ) {
        $title = '';
        foreach ( $options as $opt ) {
            if ( $opt['key'] === 'pattern_title' ) {
                $title = $opt['value'];
            }
        }

        $slug = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $title ) );
        $slug = trim( $slug, '-' );

        $options[] = [
            'key' => 'pattern_slug',
            'label' => 'Pattern Slug',
            'value' => $slug,
            'type' => 'string',
        ];
    }

    public function alreadyExists( InputInterface $input, OutputInterface $output ) {

        $path = getcwd() . '/patterns/' . $this->options['pattern_slug']['value'] . '.php';

        if ( ! file_exists( $path ) ) {
            return;
        }

        $name = $this->options['pattern_title']['value'];
        $helper = $this->getHelper( 'question' );
        $question = new ConfirmationQuestion( "<error>\nA {$name} pattern appears to already exist. \nFiles will be overwritten. Continue? (y/n)</error>", false );

        if ( ! $helper->ask( $input, $output, $question ) ) {
            $output->writeln( ['Quitting...'] );
            exit;
        }
    }

    public function createPatternsDirectory() {
        $path = getcwd() . '/patterns';
        if ( ! is_dir( $path ) ) {
            mkdir( $path, 0775, true );
        }
    }

    public function copyPatternFiles() {

        $stub_path = APP_ROOT . 'stubs/patterns/';
        $target_path = getcwd() . '/patterns/';

        $files = [
            [
                'source' => 'pattern.php',
                'target' => $this->options['pattern_slug']['value'] . '.php',
            ],
        ];

        $this->processFiles( $stub_path, $target_path, $files );
    }
}
