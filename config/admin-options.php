<?php
return [
    [
        'key' => 'settings_page_name',
        'label' => 'Settings Page Name',
        'description' => 'The name of the settings page as it appears in the menu.',
        'type' => 'string'
    ],
    [
        'key' => 'top_level_admin_page',
        'label' => 'Top Level Admin Page',
        'description' => 'Show in left hand WPAdmin bar?',
        'type' => 'boolean'
    ],
    [
        'if' => ['top_level_admin_page' => false],
        'key' => 'sub_level_admin_page',
        'label' => 'Sub Level Admin Page',
        'description' => 'Choose a top level page to nest settings page.',
        'type' => 'select',
        'options' => [
            [
                'label' => 'Dashboard',
                'value' => 'index.php',
            ],
            [
                'label' => 'Posts',
                'value' => 'edit.php',
            ],
            [
                'label' => 'Media',
                'value' => 'uploads.php',
            ],
            [
                'label' => 'Pages',
                'value' => 'edit.php?post_type=page',
            ],
            [
                'label' => 'Comments',
                'value' => 'edit-comments.php',
            ],
            [
                'label' => 'Custom Post Type',
                'value' => 'edit.php?post_type=custom_post_type',
            ],
            [
                'label' => 'Appearance',
                'value' => 'themes.php',
            ],
            [
                'label' => 'Plugins',
                'value' => 'plugins.php',
            ],
            [
                'label' => 'Users',
                'value' => 'users.php',
            ],
            [
                'label' => 'Tools',
                'value' => 'tools.php',
            ],
            [
                'label' => 'Settings',
                'value' => 'options-general.php',
            ],
            [
                'label' => 'Network Settings',
                'value' => 'settings.php'
            ]
        ]
    ]
];