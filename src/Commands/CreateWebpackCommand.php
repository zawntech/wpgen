<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;

class CreateWebpackCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        CheckWorkingDirectory;

    protected $options = [];

    protected static $defaultName = 'create:webpack';

    protected function configure() {
        $this
            ->setDescription( 'Create a webpack configuration.' )
            ->setHelp( 'Creates webpack.config.js, package.json, and starter JS/SCSS asset files.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {
        $this->isWPGenPluginDirectory( $input, $output );

        if ( file_exists( getcwd() . '/webpack.config.js' ) ) {
            $output->writeln( '<error>webpack.config.js already exists. Aborting.</error>' );
            exit;
        }
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {
        $this->createDirectories();
        $this->copyWebpackFiles();

        $output->writeln( '' );
        $output->writeln( '<info>Webpack configuration created.</info>' );
        $output->writeln( 'Run <comment>npm install</comment> to install dependencies.' );
        $output->writeln( 'Run <comment>npm run dev</comment> to start development watch mode.' );
        $output->writeln( 'Run <comment>npm run build</comment> to create a production build.' );

        return 0;
    }

    protected function createDirectories() {
        $dirs = [
            'assets/js/src/frontend',
            'assets/js/src/admin',
            'assets/sass',
            'assets/build',
            'assets/scripts',
        ];

        foreach ( $dirs as $dir ) {
            $path = getcwd() . '/' . $dir;
            if ( !is_dir( $path ) ) {
                mkdir( $path, 0775, true );
            }
        }
    }

    protected function copyWebpackFiles() {
        $stub_path = APP_ROOT . 'stubs/webpack/';
        $target_path = getcwd() . '/';

        $files = [
            [
                'source' => 'webpack.config.js',
                'target' => 'webpack.config.js',
            ],
            [
                'source' => 'package.json',
                'target' => 'package.json',
            ],
            [
                'source' => 'frontend-index.js',
                'target' => 'assets/js/src/frontend/index.js',
            ],
            [
                'source' => 'admin-index.js',
                'target' => 'assets/js/src/admin/index.js',
            ],
            [
                'source' => 'frontend.scss',
                'target' => 'assets/sass/frontend.scss',
            ],
            [
                'source' => 'admin.scss',
                'target' => 'assets/sass/admin.scss',
            ],
            [
                'source' => '_variables.scss',
                'target' => 'assets/sass/_variables.scss',
            ],
            [
                'source' => 'build-zip.js',
                'target' => 'assets/scripts/build-zip.js',
            ],
        ];

        $this->processFiles( $stub_path, $target_path, $files );
    }
}
