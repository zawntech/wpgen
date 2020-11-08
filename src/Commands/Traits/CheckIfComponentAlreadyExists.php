<?php
namespace WPGen\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

trait CheckIfComponentAlreadyExists
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $component
     */
    protected function checkIfComponentAlreadyExists( InputInterface $input, OutputInterface $output, $component = '') {

        // Validate input.
        if ( empty( $component ) ) {
            $output->writeln('<error>Error: No component name specified...</error>.' );
            return;
        }

        if ( ! file_exists(  getcwd() . '/src/' . $component . '.php' ) ) {
            return;
        }

        // Prompt user if anything items are wrong.
        $helper = $this->getHelper( 'question' );
        $question = new ConfirmationQuestion( "<error>\n{$component} appears to already exist. \nFiles will be overwritten. Continue? (y/n)</error>", false );

        if ( ! $helper->ask( $input, $output, $question ) ) {
            $output->writeln(['Quitting...']);
            exit;
        }
    }
}