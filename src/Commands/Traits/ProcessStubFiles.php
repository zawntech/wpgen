<?php

namespace WPGen\Commands\Traits;

/**
 * Handles processing stub files.
 *
 * Trait ProcessStubFiles
 * @package WPGen\Commands\Traits
 */
trait ProcessStubFiles
{
    /**
     * Perform string replacements against option keys/values.
     *
     * @param $content
     * @return mixed
     */
    protected function replaceValues( $content ) {

        foreach ( $this->options as $key => $data ) {
            $value = $data['value'];
            $search = "{{ $key }}";
            $content = str_replace( $search, $value, $content );
        }
        return $content;
    }

    /**
     * Loop through array of stub files, perform value
     * replacements, then store file to target destinations.
     *
     * @param $stub_path
     * @param $target_path
     * @param $files
     * @param bool $overwrite
     */
    protected function processFiles( $stub_path, $target_path, $files, $overwrite = false ) {

        // Process files.
        foreach ( $files as $file ) {

            // Read source file.
            $data = file_get_contents( $stub_path . $file['source'] );

            // Replace values.
            $data = $this->replaceValues( $data );

            // Skip if file already exists.
            if ( !$overwrite && file_exists( $target_path . $file['target'] ) ) {
                continue;
            }

            // Store file.
            file_put_contents( $target_path . $file['target'], $data );
        }
    }
}