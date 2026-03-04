<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Config;

class ConfigCommand extends Command
{
    use QueryOptions;

    protected $options = [];

    protected static $defaultName = 'config';

    protected function configure() {
        $this
            ->setDescription( 'Set default values for plugin creation.' )
            ->setHelp( 'This command allows you to define default values that will be pre-populated when running create:plugin.' );
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        $output->writeln( '<info>Set default values for plugin creation. Press Enter to skip an option.</info>' );
        $output->writeln( '' );

        // Load plugin options with empty values, excluding plugin-specific options.
        $this->options = array_filter( array_map( function( $opt ) {
            $opt['value'] = '';
            return $opt;
        }, Config::get()->pluginOptions() ), function( $opt ) {
            return empty( $opt['inferred'] );
        } );

        // Pre-fill from existing defaults file.
        $path = APP_ROOT . 'defaults.json';
        if ( file_exists( $path ) ) {
            $json = json_decode( file_get_contents( $path ), true );
            if ( is_array( $json ) ) {
                foreach ( $json as $key => $value ) {
                    if ( isset( $this->options[$key] ) ) {
                        $this->options[$key]['value'] = $value;
                    }
                }
            }
        }

        // Re-key options array by key field for trait compatibility.
        $keyed = [];
        foreach ( $this->options as $option ) {
            $keyed[$option['key']] = $option;
        }
        $this->options = $keyed;

        $this->queryOptions( $input, $output, $this->options );
        $this->confirmOptions( $input, $output, $this->options );
    }

    protected function execute( InputInterface $input, OutputInterface $output ) {

        // Filter to only non-empty values.
        $defaults = [];
        foreach ( $this->options as $key => $option ) {
            if ( !empty( $option['value'] ) ) {
                $defaults[$key] = $option['value'];
            }
        }

        $path = APP_ROOT . 'defaults.json';
        file_put_contents( $path, json_encode( $defaults, JSON_PRETTY_PRINT ) );

        $output->writeln( '' );
        $output->writeln( '<info>Defaults saved to ' . $path . '</info>' );

        return 0;
    }

    /**
     * Override trait's validateValue to allow empty strings (skipping an option).
     */
    protected function validateValue( $value ) {
        if ( $value === null ) {
            return '';
        }

        // Problematic strings and characters...
        $replacements = [
            '"',
            '/*',
            '*/'
        ];

        foreach ( $replacements as $char ) {
            $value = str_replace( $char, '', $value );
        }

        return $value;
    }
}
