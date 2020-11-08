<?php
namespace WPGen\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait RegisterComponentInMainClass
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $component for example: 'Admin\AdminComponent'
     */
    protected function registerComponentInMainClass(InputInterface $input, OutputInterface $output, $component = '' ) {

        // Validate input.
        if ( empty( $component ) ) {
            $output->writeln('<error>Error: No component name specified...</error>.' );
            return;
        }

        // Get class name.
        $class = $this->options['plugin_main_class']['value'];

        // Absolute path to main class file.
        $path = getcwd() . '/src/' . $class . '.php';

        // Verify main class file exists.
        if ( ! file_exists( $path ) ) {
            $output->writeln(['Unable to locate main plugin class at:', $path]);
            return;
        }

        // Get contents.
        $contents = file_get_contents( $path );
        if ( false !== strpos( $contents, "new {$component}" ) ) {
            $output->writeln(["<info>{$component} is already registered in {$class}.</info>"]);
            return;
        }

        // Extract the 'register_components() { ... }' section of the code.
        $start = strpos( $contents, 'register_components() {' );

        if ( false === $start ) {
            $output->writeln('<error>Error: Unable to parse main plugin class, no register_components() function found...</error>.' );
            return;
        }

        $end = strpos( $contents, '}', $start );
        $search = substr( $contents, $start, ( $end - $start + 1 ) );

        // Split the extracted code by lines.
        $lines = explode( "\n", $search );

        // End of the array.
        $last_index = count( $lines ) - 1;
        $tail = $lines[$last_index];

        // Append new component class and tail.
        $lines[$last_index] = "        new {$component};";
        $lines[] = $tail;

        // Recombine string.
        $replace = implode( "\n", $lines );

        // Replace content.
        $contents = str_replace( $search, $replace, $contents );

        // Store file.
        file_put_contents( $path, $contents );

        $output->writeln(["<info>The {$component} class has been registered in $class</info>"]);
    }
}