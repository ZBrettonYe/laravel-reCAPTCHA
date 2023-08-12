<?php

return [
    'secret' => env('NOCAPTCHA_SECRET'),
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'get_config_method' => env('NOCAPTCHA_CONFIG', false),
    'options' => env('NOCAPTCHA_OPTIONS', ['timeout' => 30]), // http options
    'score_verification' => env('NOCAPTCHA_SCORE', false), // This is an v3 feature
    'score_threshold' => env('NOCAPTCHA_THRESHOLD', 0.7) // Any requests above this score will be considered as spam
];
