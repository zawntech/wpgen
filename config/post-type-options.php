<?php
return [
    [
        'key' => 'post_type_key',
        'label' => 'Post Type Key',
        'description' => 'Typically singular version of word/concept; ie: book',
        'type' => 'string'
    ],
    [
        'key' => 'post_type_singular',
        'label' => 'Post Type Singular',
        'description' => 'Singular version of word/content (no spaces), ie: Book',
        'type' => 'string'
    ],
    [
        'key' => 'post_type_plural',
        'label' => 'Post Type Plural',
        'description' => 'Plural version of word/content, ie: Books',
        'type' => 'string',
    ],
    [
        'key' => 'post_type_slug',
        'label' => 'Post Type Slug',
        'description' => 'Post type slug rewrite. Leave empty for no rewrite. ie: books',
        'type' => 'string'
    ]
];