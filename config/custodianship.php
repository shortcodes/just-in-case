<?php

return [
    'delivery' => [
        'retry_intervals' => [
            (int) env('CUSTODIANSHIP_RETRY_INTERVAL_1', 3600),
            (int) env('CUSTODIANSHIP_RETRY_INTERVAL_2', 86400),
            (int) env('CUSTODIANSHIP_RETRY_INTERVAL_3', 604800),
        ],

        'max_attempts' => (int) env('CUSTODIANSHIP_MAX_ATTEMPTS', 3),

        'pending_timeout' => (int) env('CUSTODIANSHIP_PENDING_TIMEOUT', 7200),
    ],

    'thresholds' => [
        'warning_days' => (int) env('CUSTODIANSHIP_WARNING_DAYS', 30),
        'urgent_days' => (int) env('CUSTODIANSHIP_URGENT_DAYS', 7),
    ],

    'attachments' => [
        'allowed_mime_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'audio/mpeg',
            'audio/wav',
            'video/mp4',
            'video/mpeg',
        ],
        'max_total_size' => 10485760,
        'max_count' => 10,
        'temporary_cleanup_hours' => 24,
    ],
];
