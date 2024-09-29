<?php

use NextBuild\ActivityTracker\Http\Middleware\Authorize;
use NextBuild\ActivityTracker\Watchers;

return [

    /*
    |--------------------------------------------------------------------------
    | Activity Tracker Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Activity Tracker watchers regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable or disable Activity Tracker data storage.
    |
    */

    'enabled' => env('ACTIVITY_TRACKER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Activity Tracker Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Activity Tracker will be accessible from. If the
    | setting is null, Activity Tracker will reside under the same domain as the
    | application. Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('ACTIVITY_TRACKER_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Activity Tracker Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Activity Tracker will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('ACTIVITY_TRACKER_PATH', 'activity-tracker'),

    /*
    |--------------------------------------------------------------------------
    | Activity Tracker Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the storage driver that will
    | be used to store Activity Tracker's data. In addition, you may set any
    | custom options as needed by the particular driver you choose.
    |
    */

    'driver' => env('ACTIVITY_TRACKER_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Tracker Queue
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the queue connection and queue
    | which will be used to process ProcessPendingUpdate jobs. This can
    | be changed if you would prefer to use a non-default connection.
    |
    */

    'queue' => [
        'connection' => env('ACTIVITY_TRACKER_QUEUE_CONNECTION', null),
        'queue' => env('ACTIVITY_TRACKER_QUEUE', null),
    ],

    'response_size_limit' => env('ACTIVITY_TRACKER_RESPONSE_SIZE_LIMIT', 64),

    /*
    |--------------------------------------------------------------------------
    | Activity Tracker Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Activity Tracker route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => [
        'web',
        Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed / Ignored Paths & Commands
    |--------------------------------------------------------------------------
    |
    | The following array lists the URI paths and Artisan commands that will
    | not be watched by Activity Tracker. In addition to this list, some Laravel
    | commands, like migrations and queue commands, are always ignored.
    |
    */

    'only_paths' => [
        // 'api/*'
    ],

    'ignore_paths' => [
        'livewire*',
        'nova-api*',
        'pulse*',
    ],

    'ignore_http_methods' => [
        // 'post'
    ],

    'ignore_status_codes' => [
        // 419
    ],
];
