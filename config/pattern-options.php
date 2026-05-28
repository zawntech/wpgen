<?php
return [
    [
        'key' => 'pattern_title',
        'label' => 'Pattern Title',
        'description' => 'Human-readable pattern title shown in the inserter; ie: Pricing Hero',
        'type' => 'string'
    ],
    [
        'key' => 'pattern_description',
        'label' => 'Pattern Description',
        'description' => 'Short description shown in the inserter preview',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'pattern_categories',
        'label' => 'Pattern Categories',
        'description' => 'Comma-separated inserter categories (e.g. featured, banner). Core categories: featured, banner, columns, header, footer, gallery, text, query, services, contact, about, posts.',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'pattern_keywords',
        'label' => 'Pattern Keywords',
        'description' => 'Comma-separated search keywords for the inserter',
        'type' => 'string',
        'optional' => true
    ],
    [
        'key' => 'pattern_block_types',
        'label' => 'Pattern Block Types',
        'description' => 'Comma-separated block types this pattern is intended to transform (e.g. core/query, core/template-part/header)',
        'type' => 'string',
        'optional' => true
    ]
];
