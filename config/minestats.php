<?php

$trustedProxies = env('MINESTATS_TRUSTED_PROXIES', null);
if (!empty($trustedProxies)) {
    $trustedProxies = explode(',', $trustedProxies);
}

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

    /*
     * Periodically remove the old stats
     *
     * [
     *     ElapsedTime => MinInterval,
     *     ...
     * ]
     *
     * After ElapsedTime minutes, we must keep maximum one stat every MinInterval minutes per servers.
     * Set MinInterval to -1 to remove.
     */
    'stats_gc'              => [
        5            => 1, // After 5 mins, keep max 1 stat per minutes
        60           => 5, // After 60 mins, keep max 1 stat every 5 minutes
        24 * 60      => 10, // After 1 day, keep max 1 stat every 10 minutes
        // 15 * 24 * 60 => 30, // After 15 day, keep max 1 stat every 30 minutes
        60 * 24 * 60 => -1 // Delete stats after 60 days
    ],

    /*
     * Graph navigator options
     *
     * [MaxInterval, SamplePerMinutes]
     */
    'stats_graph_navigator' => [60 * 24 * 60, 60],

    /*
     * Graph options
     *
     * [
     *     Interval => [SamplePerMinutes, SideCache]
     *     ...
     * ]
     *
     * Must be from smallest to largest Interval!
     */
    'stats_graph'           => [
        24 * 60      => [10, 24 * 60],
        3 * 24 * 60  => [30, 24 * 60],
        60 * 24 * 60 => [60, 3 * 24 * 60],
    ],

    /*
     * Trusted proxies
     */
    'trusted_proxies'       => $trustedProxies, // MINESTATS_TRUSTED_PROXIES

    /*
     * Assets revisions to force cache reload.
     */
    'assets_revision'       => env('MINESTATS_ASSETS_REV', null),

];
