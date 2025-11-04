<?php

return [
    'delivery' => [
        'retry_intervals' => [
            env('CUSTODIANSHIP_RETRY_INTERVAL_1', 3600),
            env('CUSTODIANSHIP_RETRY_INTERVAL_2', 86400),
            env('CUSTODIANSHIP_RETRY_INTERVAL_3', 604800),
        ],

        'max_attempts' => env('CUSTODIANSHIP_MAX_ATTEMPTS', 3),

        'pending_timeout' => env('CUSTODIANSHIP_PENDING_TIMEOUT', 7200),
    ],

    'thresholds' => [
        'warning_days' => env('CUSTODIANSHIP_WARNING_DAYS', 30),
        'urgent_days' => env('CUSTODIANSHIP_URGENT_DAYS', 7),
    ],
];
