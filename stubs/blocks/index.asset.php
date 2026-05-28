<?php
/**
 * {{ block_title }} - Editor script dependencies
 *
 * Read by register_block_type() to declare which WordPress packages
 * the editorScript depends on. WP enqueues these automatically.
 *
 * The version is derived from the file's mtime so cache busts whenever
 * the asset list is updated. Replace with a literal version string if
 * you want stable cache keys.
 */
return [
    'dependencies' => [
        'wp-blocks',
        'wp-block-editor',
        'wp-element',
        'wp-i18n',
        'wp-components',
    ],
    'version' => filemtime( __FILE__ ),
];
