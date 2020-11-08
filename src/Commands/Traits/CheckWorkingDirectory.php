<?php
namespace WPGen\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

trait CheckWorkingDirectory
{
    /**
     * Are we in a generated plugin root directory?
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function isWPGenPluginDirectory( InputInterface $input, OutputInterface $output ) {

        // Path to plugin configuration.
        $json_path = getcwd() . '/wpgen.config.json';

        if ( ! file_exists( $json_path ) ) {
            $output->write( "<error>\nError: cannot location wpgen.config.json. \nRun command from plugin root directory.</error>" );
            exit;
        }
    }

    /**
     * Check if the script is being executed form a 'plugins' directory.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function isWPPluginsDirectory( InputInterface $input, OutputInterface $output ) {
        $cwd = getcwd();
        if (
            false === strpos( $cwd, '/plugins' ) &&
            false === strpos( $cwd, '\plugins' )
        ) {
            $helper = $this->getHelper( 'question' );
            $question = new ConfirmationQuestion( 'You do not appear to be in a WordPress plugin directory. Continue? (y/n)', false );
            if ( !$helper->ask( $input, $output, $question ) ) {
                $output->write( 'Quitting...' );
                exit;
            }
        }
    }

    public function getComponentName() {
        $component = '';
        $files = scandir( getcwd() );
        foreach( $files as $file ) {
            if ( false !== strpos( $file, 'Component.php' ) ) {
                $component = str_replace( 'Component.php', '', $file );
            }
        }
        return $component;
    }

    protected function isComponentDirectory( InputInterface $input, OutputInterface $output ) {
        return ! empty( $this->getComponentName() );
    }
}