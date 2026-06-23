<?php
return [
    [
        'key' => 'widget_name',
        'label' => 'Widget Name',
        'description' => 'The name of the custom widget; ie: Feature Cards',
        'type' => 'string'
    ],
    [
        'key' => 'widget_description',
        'label' => 'Widget Description',
        'description' => 'The widget description',
        'type' => 'string'
    ],
    [
        'key' => 'widget_icon',
        'label' => 'Widget Icon',
        'description' => 'An Elementor eicon class (see https://elementor.github.io/elementor-icons/); ie: eicon-gallery-grid. Leave blank for eicon-code.',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'widget_category',
        'label' => 'Widget Category',
        'description' => 'The Elementor panel category for this widget. Leave blank to use the plugin text domain.',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'widget_keywords',
        'label' => 'Widget Keywords',
        'description' => 'Comma-separated search keywords shown in the Elementor panel; ie: cards, features',
        'type' => 'string',
        'optional' => true
    ]
];
