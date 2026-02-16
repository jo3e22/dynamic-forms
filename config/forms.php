<?php

return [
    'default_colors' => [
        'primary' => env('FORM_PRIMARY_COLOR', '#3B82F6'),
        'secondary' => env('FORM_SECONDARY_COLOR', '#EFF6FF'),
    ],
    
    'field_types' => [
        'short-answer',
        'email',
        'long-answer',
        'checkbox',
        'multiple-choice',
        'textarea',
    ],
    
    'max_sections' => env('FORM_MAX_SECTIONS', 50),
    'max_fields_per_section' => env('FORM_MAX_FIELDS_PER_SECTION', 100),
];