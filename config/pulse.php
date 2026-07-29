<?php

use Laravel\Pulse\Recorders;

return [
    'enabled' => env('PULSE_ENABLED', true),

    'authorized_email' => env('PULSE_AUTHORIZED_EMAIL', 'admin@tartil.id'),

    'domain' => env('PULSE_DOMAIN', null),

    'path' => env('PULSE_PATH', 'pulse'),

    'middleware' => [
        'web',
        App\Http\Middleware\PulseAccess::class,
    ],

    'timezone' => env('PULSE_TIMEZONE', config('app.timezone')),

    'ingest' => [
        'driver' => env('PULSE_INGEST_DRIVER', 'storage'),
        'buffer' => env('PULSE_INGEST_BUFFER', 5_000),
        'trim' => [
            'lottery' => [1, 1_000],
            'keep' => '7 days',
        ],
        'redis' => [
            'connection' => env('PULSE_REDIS_CONNECTION'),
            'chunk' => 1000,
        ],
    ],

    'storage' => [
        'driver' => 'database',
        'database' => [
            'connection' => env('PULSE_DB_CONNECTION', null),
            'chunk' => 1000,
        ],
    ],

    'cache' => env('PULSE_CACHE_DRIVER', 'array'),

    'servers' => [
        'server_name' => 'TartilPro Server',
        'direction' => 'desc',
    ],

    'recorders' => [
        Recorders\CacheInteractions::class => [
            'enabled' => env('PULSE_CACHE_INTERACTIONS_ENABLED', false),
            'sample_rate' => env('PULSE_CACHE_INTERACTIONS_SAMPLE_RATE', 1),
            'ignore' => [
                '/^laravel:pulse:/',
                '/^illuminate:queue:/',
            ],
            'groups' => [
                '/^job-exceptions/' => 'job-exceptions',
            ],
        ],
        Recorders\Exceptions::class => [
            'enabled' => env('PULSE_EXCEPTIONS_ENABLED', true),
            'sample_rate' => env('PULSE_EXCEPTIONS_SAMPLE_RATE', 1),
            'location' => true,
            'ignore' => [
                '/^Horizon/',
                '/^Termwind/',
            ],
        ],
        Recorders\Queues::class => [
            'enabled' => env('PULSE_QUEUES_ENABLED', true),
            'sample_rate' => env('PULSE_QUEUES_SAMPLE_RATE', 1),
            'ignore' => [
                '/^sync$/',
            ],
        ],
        Recorders\SlowJobs::class => [
            'enabled' => env('PULSE_SLOW_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_JOBS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_JOBS_THRESHOLD', 1000),
        ],
        Recorders\SlowOutgoingRequests::class => [
            'enabled' => env('PULSE_SLOW_OUTGOING_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_OUTGOING_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_OUTGOING_REQUESTS_THRESHOLD', 1000),
            'ignore' => [
                '/^127\.0\.0\.1/',
                '/^localhost/',
            ],
        ],
        Recorders\SlowQueries::class => [
            'enabled' => env('PULSE_SLOW_QUERIES_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_QUERIES_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 1000),
            'location' => true,
            'max_query_length' => env('PULSE_SLOW_QUERIES_MAX_QUERY_LENGTH', null),
        ],
        Recorders\SlowRequests::class => [
            'enabled' => env('PULSE_SLOW_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_REQUESTS_THRESHOLD', 1000),
            'ignore' => [
                '#^/pulse$#',
                '#^/telescope#',
                '#^/horizon#',
            ],
        ],
        Recorders\UserJobs::class => [
            'enabled' => env('PULSE_USER_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_USER_JOBS_SAMPLE_RATE', 1),
            'ignore' => [],
        ],
        Recorders\UserRequests::class => [
            'enabled' => env('PULSE_USER_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_USER_REQUESTS_SAMPLE_RATE', 1),
            'ignore' => [
                '#^/pulse$#',
                '#^/telescope#',
                '#^/horizon#',
            ],
        ],
    ],
];