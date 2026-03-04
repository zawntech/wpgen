<?php
namespace WPGen\Commands\Traits;

use WPGen\Config;

trait LoadOptions
{
    /**
     * Load options from configuration files.
     */
    protected function loadPluginOptions() {
        $options = array_map( function( $opt ) {
            $opt['value'] = '';
            return $opt;
        }, Config::get()->pluginOptions() );

        foreach ( $options as $option ) {
            $key = $option['key'];
            if ( !isset( $this->options[$key] ) ) {
                $this->options[$key] = $option;
            }
        }

        // Load global defaults.
        $defaultsPath = APP_ROOT . 'defaults.json';
        if ( file_exists( $defaultsPath ) ) {
            $defaults = json_decode( file_get_contents( $defaultsPath ), true );
            if ( is_array( $defaults ) ) {
                foreach ( $defaults as $key => $value ) {
                    if ( isset( $this->options[$key] ) ) {
                        $this->options[$key]['value'] = $value;
                    }
                }
            }
        }

        // Read json
        $path = getcwd() . '/wpgen.config.json';
        if ( ! file_exists( $path ) ) {
            $path = getcwd() . '/../wpgen.config.json';
        }
        if ( ! file_exists( $path ) ) {
            $path = getcwd() . '/../../wpgen.config.json';
        }
        if ( ! file_exists( $path ) ) {
            return;
        }

        $json = file_get_contents( $path );
        $json = json_decode( $json ) ;

        if ( ! $json ) {
            return;
        }

        foreach( $json as $key => $value ) {
            $this->options[$key]['value'] = $value;
        }
    }
}