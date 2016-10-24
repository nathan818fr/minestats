<?php

return [

    /*
     * Allow or not anonymous users to view the servers
     */
    'allow_anonymous'       => env('MINESTATS_PUBLIC', true),

    /*
     * Ping interval in seconds (not recommended to set below 5)
     */
    'ping_interval'         => env('MINESTATS_PING_INTERVAL', 5),

    /*
     * Favicon cache period in minutes
     */
    'favicon_cache_period'  => env('MINESTATS_FAVICON_CACHE', 60),

    /*
     * Versions cache period in minutes
     */
    'versions_cache_period' => env('MINESTATS_VERSIONS_CACHE', 60),

    /*
     * Client realtime ping update interval in milliseconds
     */
    'ui_update_interval'    => env('MINESTATS_UI_PING_INTERVAL', 5500),

    /*
     * Client realtime graph period
     */
    'ui_realtime_period'    => env('MINESTATS_UI_GRAPH_PERIOD', 5 * 60),

    /*
     * Ping mode
     * cron: use laravel crontab
     * manual: config ping yourself
     */
    'ping_mode'             => env('MINESTATS_PING_MODE', 'cron'),

];
