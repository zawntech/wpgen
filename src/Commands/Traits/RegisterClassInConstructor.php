<?php
namespace WPGen\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait RegisterClassInConstructor
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $class
     */
    public function addToComponentConstructor( InputInterface $input, OutputInterface $output, $class ) {

        $file = $this->getComponentName() . 'Component.php';
        $path = getcwd() . '/' . $file;
        $contents = file_get_contents( $path );

        // Extract the 'register_components() { ... }' section of the code.
        $start = strpos( $contents, '__construct() {' );

        if ( false === $start ) {
            $output->writeln( '<error>Error: Unable to parse component file, no __construct() function found...</error>.' );
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
        $lines[$last_index] = "        new {$class};";
        $lines[] = $tail;

        // Recombine string.
        $replace = implode( "\n", $lines );

        // Replace content.
        $contents = str_replace( $search, $replace, $contents );

        // Store file.
        file_put_contents( $path, $contents );

        $output->writeln( ["<info>$class added to component constructor.</info>"] );
    }
}