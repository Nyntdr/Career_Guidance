<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings definitions
    |--------------------------------------------------------------------------
    |
    | Single source of truth for every dynamic setting. Each key defines how
    | it is rendered in the admin Settings page and its default value.
    | Adding a new setting is just adding an entry here — no migration needed.
    |
    | type: text | number | select | percent_group
    */
    'definitions' => [
        'site_name' => [
            'label' => 'Site name',
            'type' => 'text',
            'group' => 'Branding',
            'default' => 'Ujjwol Bato',
        ],
        'currency_symbol' => [
            'label' => 'Currency symbol',
            'type' => 'text',
            'group' => 'Currency',
            'default' => '$',
        ],
        'currency_position' => [
            'label' => 'Currency position',
            'type' => 'select',
            'options' => [
                'before' => 'Before number ($100)',
                'after' => 'After number (100$)',
            ],
            'group' => 'Currency',
            'default' => 'before',
        ],
        'ats_weights' => [
            'label' => 'ATS scan weights',
            'type' => 'percent_group',
            'keys' => [
                'keyword' => 'Keyword match',
                'structure' => 'Structure',
                'formatting' => 'Formatting',
                'readability' => 'Readability',
            ],
            'group' => 'Scoring',
            'default' => [
                'keyword' => 50,
                'structure' => 15,
                'formatting' => 15,
                'readability' => 20,
            ],
        ],
        'top_matches' => [
            'label' => 'Top matches shown',
            'type' => 'number',
            'min' => 1,
            'max' => 50,
            'group' => 'Limits',
            'default' => 10,
        ],
        'admin_page_size' => [
            'label' => 'Admin rows per page',
            'type' => 'number',
            'min' => 5,
            'max' => 100,
            'group' => 'Limits',
            'default' => 20,
        ],
        'ml_enabled' => [
            'label' => 'ML resume parsing',
            'type' => 'select',
            'options' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'group' => 'ML',
            'default' => 'enabled',
        ],
        'ml_min_confidence' => [
            'label' => 'ML confidence threshold',
            'type' => 'number',
            'min' => 0.05,
            'max' => 0.99,
            'group' => 'ML',
            'default' => 0.3,
        ],
        'ml_cluster_purity' => [
            'label' => 'ML cluster purity',
            'type' => 'number',
            'min' => 0.5,
            'max' => 1,
            'group' => 'ML',
            'default' => 0.7,
        ],
        'recommendation_weights' => [
            'label' => 'Recommendation score weights',
            'type' => 'percent_group',
            'keys' => [
                'overlap' => 'Skill overlap',
                'semantic' => 'Semantic similarity',
            ],
            'group' => 'Scoring',
            'default' => [
                'overlap' => 60,
                'semantic' => 40,
            ],
        ],
        'recommendation_path_bonus' => [
            'label' => 'Career-path bonus',
            'type' => 'number',
            'min' => 0,
            'max' => 20,
            'group' => 'Limits',
            'default' => 5,
        ],
        'llm_rationale' => [
            'label' => 'AI rationale (LLM)',
            'type' => 'select',
            'options' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'group' => 'ML',
            'default' => 'disabled',
        ],
        'llm_provider' => [
            'label' => 'AI rationale provider',
            'type' => 'select',
            'options' => [
                'gemini' => 'Gemini (Google)',
                'groq' => 'Groq (GPT-OSS)',
            ],
            'group' => 'ML',
            'default' => 'gemini',
        ],
        'llm_prompt_version' => [
            'label' => 'AI rationale prompt',
            'type' => 'select',
            'options' => [
                'concise' => 'Concise (current)',
                'detailed' => 'Detailed (enhanced)',
            ],
            'group' => 'ML',
            'default' => 'concise',
        ],
    ],
];
