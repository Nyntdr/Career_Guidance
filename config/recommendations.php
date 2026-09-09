<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recommendation weights
    |--------------------------------------------------------------------------
    |
    | Default blend of the deterministic overlap score and the semantic
    | similarity score. Admins can override these from Settings -> Scoring.
    |
    */

    'weights' => [
        'overlap' => 60,
        'semantic' => 40,
    ],

    /*
    |--------------------------------------------------------------------------
    | Career-path bonus
    |--------------------------------------------------------------------------
    |
    | A small, hop-decayed bonus added to roles reachable via career paths
    | from the strongest direct matches. Admins can tweak it in Settings.
    |
    */

    'path_bonus' => 5,

    /*
    |--------------------------------------------------------------------------
    | LLM rationale provider
    |--------------------------------------------------------------------------
    |
    | Placeholder values until a real provider is configured. Nothing calls
    | out to an LLM while RECOMMENDATION_LLM_KEY is empty: the rationale is
    | generated deterministically and the "enabled" setting alone is not
    | enough to activate the provider.
    |
    | Set RECOMMENDATION_LLM_ENDPOINT to any OpenAI-compatible chat endpoint,
    | e.g. https://generativelanguage.googleapis.com/v1beta/openai/chat/completions
    |
    */

    'rationale_default' => 'disabled',

    /*
    |--------------------------------------------------------------------------
    | Which LLM provider to use for the AI rationale. Admins can override this
    | from Settings -> ML ("AI rationale provider"). Each provider block must
    | be an OpenAI-compatible chat completions endpoint.
    |--------------------------------------------------------------------------
    */

    'default_provider' => env('RECOMMENDATION_LLM_PROVIDER', 'gemini'),

    'providers' => [
        'gemini' => [
            'endpoint' => env('RECOMMENDATION_LLM_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions'),
            'key' => env('RECOMMENDATION_LLM_KEY', ''),
            'model' => env('RECOMMENDATION_LLM_MODEL', 'gemini-3.6-flash'),
        ],
        'groq' => [
            'endpoint' => env('RECOMMENDATION_LLM_GROQ_ENDPOINT', 'https://api.groq.com/openai/v1/chat/completions'),
            'key' => env('RECOMMENDATION_LLM_GROQ_KEY', ''),
            'model' => env('RECOMMENDATION_LLM_GROQ_MODEL', 'openai/gpt-oss-120b'),
        ],
    ],

    'timeout' => (int) env('RECOMMENDATION_LLM_TIMEOUT', 60),
    'max_tokens' => (int) env('RECOMMENDATION_LLM_MAX_TOKENS', 1024),

];