<?php
return [
    [
        'key' => 'block_title',
        'label' => 'Block Title',
        'description' => 'The human-readable block title shown in the inserter; ie: Pricing Table',
        'type' => 'string'
    ],
    [
        'key' => 'block_description',
        'label' => 'Block Description',
        'description' => 'Short description shown in the inspector sidebar',
        'type' => 'string'
    ],
    [
        'key' => 'block_category',
        'label' => 'Block Category',
        'description' => 'Inserter category slug. Leave blank to use this plugin\'s own category (registered by GutenbergComponent). Core categories: text, media, design, widgets, theme, embed.',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'block_icon',
        'label' => 'Block Icon',
        'description' => 'Dashicon slug (without the dash- prefix). Examples: smiley, star-filled, layout. Leave blank for default.',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'block_keywords',
        'label' => 'Block Keywords',
        'description' => 'Comma-separated search keywords for the inserter',
        'type' => 'string',
        'optional' => true
    ]
];
