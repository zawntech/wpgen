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

class CreateBlockCommand extends Command
{
    use LoadOptions,
        QueryOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        RegisterGutenbergComponent,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:block';

    protected function configure() {
        $this
            ->setDescription( 'Create a Gutenberg block.' )
            ->setHelp( 'Scaffolds a Gutenberg block under assets/blocks/{slug}/ and bootstraps a BlocksComponent on first run.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        $this->isWPGenPluginDirectory( $input, $output );

        $options = Config::get()->blockOptions();
        $this->queryOptions( $input, $output, $options );
        $this->inferBlockIdentifiers( $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );

        $this->maybeRegisterGutenbergComponent( $input, $output );

        $this->alreadyExists( $input, $output );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {
        $this->createBlockDirectories();
        $this->copyBlockFiles();

        $slug = $this->options['block_slug']['value'];
        $output->writeln( '' );
        $output->writeln( "<info>Block '{$slug}' scaffolded under assets/blocks/{$slug}/</info>" );
        $output->writeln( 'Run <comment>npm run build</comment> to compile assets, then activate the block in the editor.' );

        return 0;
    }

    /**
     * Derive the block slug (kebab-case) and a JSON-ready keywords array
     * from the human-entered title and comma-separated keyword string.
     */
    protected function inferBlockIdentifiers( &$options ) {
        $title = '';
        $keywords_csv = '';
        foreach ( $options as $opt ) {
            if ( $opt['key'] === 'block_title' ) {
                $title = $opt['value'];
            }
            if ( $opt['key'] === 'block_keywords' ) {
                $keywords_csv = $opt['value'];
            }
        }

        // Fall back to the plugin's own block category if the user left
        // the category prompt blank. This is the category registered by
        // GutenbergComponent, so new blocks land in the plugin's own
        // section of the inserter sidebar by default.
        foreach ( $options as &$opt ) {
            if ( $opt['key'] === 'block_category' && empty( $opt['value'] ) ) {
                $opt['value'] = $this->options['plugin_text_domain']['value'];
            }
        }
        unset( $opt );

        $slug = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $title ) );
        $slug = trim( $slug, '-' );

        $options[] = [
            'key' => 'block_slug',
            'label' => 'Block Slug',
            'value' => $slug,
            'type' => 'string',
        ];

        $keywords_json = '';
        if ( ! empty( $keywords_csv ) ) {
            $parts = array_filter( array_map( 'trim', explode( ',', $keywords_csv ) ) );
            $parts = array_map( function( $k ) {
                return '"' . str_replace( '"', '\\"', $k ) . '"';
            }, $parts );
            $keywords_json = implode( ', ', $parts );
        }

        $options[] = [
            'key' => 'block_keywords_json',
            'label' => 'Block Keywords (JSON)',
            'value' => $keywords_json,
            'type' => 'string',
        ];
    }

    public function alreadyExists( InputInterface $input, OutputInterface $output ) {

        $path = getcwd() . '/assets/blocks/' . $this->options['block_slug']['value'] . '/';

        if ( ! is_dir( $path ) ) {
            return;
        }

        $name = $this->options['block_title']['value'];
        $helper = $this->getHelper( 'question' );
        $question = new ConfirmationQuestion( "<error>\nA {$name} block appears to already exist. \nFiles will be overwritten. Continue? (y/n)</error>", false );

        if ( ! $helper->ask( $input, $output, $question ) ) {
            $output->writeln( ['Quitting...'] );
            exit;
        }
    }

    public function createBlockDirectories() {

        $path = getcwd() . '/assets/blocks/' . $this->options['block_slug']['value'] . '/';

        $dirs = [
            '',
            'build',
        ];

        foreach ( $dirs as $dir ) {
            $full = $path . $dir;
            if ( ! is_dir( $full ) ) {
                mkdir( $full, 0775, true );
            }
        }
    }

    public function copyBlockFiles() {

        $stub_path = APP_ROOT . 'stubs/blocks/';
        $block_path = getcwd() . '/assets/blocks/' . $this->options['block_slug']['value'] . '/';

        $files = [
            [
                'source' => 'block.json',
                'target' => 'block.json',
            ],
            [
                'source' => 'index.js',
                'target' => 'index.js',
            ],
            [
                'source' => 'edit.js',
                'target' => 'edit.js',
            ],
            [
                'source' => 'save.js',
                'target' => 'save.js',
            ],
            [
                'source' => 'render.php',
                'target' => 'render.php',
            ],
            [
                'source' => 'style.scss',
                'target' => 'style.scss',
            ],
            [
                'source' => 'editor.scss',
                'target' => 'editor.scss',
            ],
            [
                'source' => 'index.asset.php',
                'target' => 'build/index.asset.php',
            ],
        ];

        $this->processFiles( $stub_path, $block_path, $files );
    }

}
