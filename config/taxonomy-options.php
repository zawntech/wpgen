<?php
return [
    [
        'key' => 'taxonomy_key',
        'label' => 'Taxonomy Key',
        'description' => 'Taxonomy key, ie: book-category',
        'type' => 'string'
    ],
    [
        'key' => 'taxonomy_singular',
        'label' => 'Taxonomy Singular',
        'description' => 'Singular version of word/content (no spaces), ie: Category',
        'type' => 'string'
    ],
    [
        'key' => 'taxonomy_plural',
        'label' => 'Taxonomy Plural',
        'description' => 'Plural version of word/content, ie: Categories',
        'type' => 'string'
    ],
    [
        'key' => 'post_type',
        'label' => 'Post Type',
        'description' => 'The post type to attach the taxonomy, ie: book',
        'type' => 'string'
    ]
];