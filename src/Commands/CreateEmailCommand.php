<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;

class CreateEmailCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckWorkingDirectory,
        QueryOptions;

    protected $options = [];

    protected static $defaultName = 'create:email';

    protected function configure() {
        $this
            ->setDescription( 'Add an Emails component, AbstractEmail base class, and an example email to your plugin.' )
            ->setHelp( 'Run from your plugin root. Generates src/Emails/, src/Abstract/AbstractEmail.php, and assets/templates/emails/. Existing files are not overwritten.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {
        $this->isWPGenPluginDirectory( $input, $output );
        $this->mergeOptions( [] );

        // Register Emails\EmailsComponent in the main class. The trait is
        // idempotent -- it skips if the component is already registered.
        $this->registerComponentInMainClass( $input, $output, 'Emails\EmailsComponent' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {

        $emails_dir   = getcwd() . '/src/Emails/';
        $abstract_dir = getcwd() . '/src/Abstract/';
        $template_dir = getcwd() . '/assets/templates/emails/';

        foreach ( [ $emails_dir, $abstract_dir, $template_dir ] as $dir ) {
            if ( ! is_dir( $dir ) ) {
                mkdir( $dir, 0755, true );
            }
        }

        $stub_path = APP_ROOT . 'stubs/emails/';

        $sets = [
            [ 'dir' => $abstract_dir, 'files' => [
                ['source' => 'abstract-email.php', 'target' => 'AbstractEmail.php'],
            ]],
            [ 'dir' => $emails_dir, 'files' => [
                ['source' => 'emails-component.php', 'target' => 'EmailsComponent.php'],
                ['source' => 'example-email.php',    'target' => 'ExampleEmail.php'],
            ]],
            [ 'dir' => $template_dir, 'files' => [
                ['source' => 'template-layout.php',  'target' => '_layout.php'],
                ['source' => 'template-example.php', 'target' => 'example.php'],
            ]],
        ];

        foreach ( $sets as $set ) {
            // Pre-flight: report what will be created vs preserved. processFiles
            // itself silently skips existing files (its $overwrite default is
            // false), so this loop only adds visibility -- it does not change
            // file contents.
            foreach ( $set['files'] as $file ) {
                $rel = str_replace( getcwd() . '/', '', $set['dir'] . $file['target'] );
                if ( file_exists( $set['dir'] . $file['target'] ) ) {
                    $output->writeln( "<comment>Preserved (already exists):</comment> {$rel}" );
                } else {
                    $output->writeln( "<info>Created:</info> {$rel}" );
                }
            }
            $this->processFiles( $stub_path, $set['dir'], $set['files'] );
        }

        $output->writeln( '<info>Emails scaffold complete.</info>' );

        return 0;
    }
}
