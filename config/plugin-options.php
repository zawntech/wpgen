<?php
return [
    [
        'key' => 'plugin_name',
        'label' => 'Plugin Name',
        'description' => 'The name of the plugin, ie. My Plugin',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_description',
        'label' => 'Plugin Description',
        'description' => 'The plugin description that displays on the plugins page.',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_url',
        'label' => 'Plugin URL',
        'description' => 'The main URL of the plugin',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_author',
        'label' => 'Plugin Author',
        'description' => 'The plugin author name (company/developer name)',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_author_url',
        'label' => 'Plugin Author URL',
        'description' => 'The link to the plugin author\'s website',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_text_domain',
        'label' => 'Plugin Text Domain',
        'description' => 'Plugin text domain used for translations, ie: my-plugin',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_namespace',
        'label' => 'Plugin Namespace',
        'description' => 'A namespace used to prefix plugin code, ie: MyPlugin',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_constants_prefix',
        'label' => 'Plugin Constants Prefix',
        'description' => 'A prefix used for plugin constants, ie: MY_PLUGIN_',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_main_class',
        'label' => 'Plugin Main Class Name',
        'description' => 'The main class name for the plugin, ie: MyPlugin',
        'type' => 'string'
    ],
    [
        'key' => 'plugin_filter_prefix',
        'label' => 'Plugin Filter Prefix',
        'description' => 'The prefix used for plugin filters, ie: my_plugin_',
        'type' => 'string'
    ],
    [
        'key' => 'composer_package_name',
        'label' => 'Composer Package Name',
        'description' => 'Name of the composer package, ie: author/plugin-name',
        'type' => 'string'
    ],
    [
        'key' => 'composer_author_name',
        'label' => 'Composer Author Name',
        'description' => 'Composer developer name (personal name)',
        'type' => 'string'
    ],
    [
        'key' => 'composer_author_email',
        'label' => 'Composer Email Address',
        'description' => 'Composer developer email address',
        'type' => 'string'
    ]
];