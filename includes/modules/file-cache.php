<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: File cache
 * Description: Checks the website and tells whether the user needs to apply page cache,
 * and with what plugin.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Good cache plugin installed
 * @internal 'B' = No cache plugin installed
 * @internal 'C' = Cache managed by hosting
 * @internal 'D' = Bad cache plugin installed
 */

$temp_results_tasks_auxiliar = '';

// First check for plugins that are not completely cache plugins
if ( $wpoptimize && $accelera_wpoptimizeoptions['enable_page_caching'] > 0 ) {
    $bad_cache_plugins['wp_optimize_cache'] = true;
}

// Check if we are on SiteGround server (taken from SG Optimizer)
function is_siteground() {
    if ( ! empty( ini_get( 'open_basedir' ) ) ) {
        return 0;
    }
    return (int) ( @file_exists( '/etc/yum.repos.d/baseos.repo' ) && @file_exists( '/Z' ) );
}


if ( defined('WPE_CACHE_PLUGIN_BASE') ) { // WP Engine?
    $results_tasks[] = 'C';
    $temp_results_tasks_auxiliar = 'WP Engine';
} elseif ( array_key_exists('x-kinsta-cache',$home_url_headers) ) { // Kinsta?
    $results_tasks[] = 'C';
    $temp_results_tasks_auxiliar = 'Kinsta';
} elseif ( is_siteground() ) { // SiteGround?
    $results_tasks[] = 'C';
    $temp_results_tasks_auxiliar = 'SiteGround';
} elseif ( in_array( true, $bad_cache_plugins ) ) { // Bad cache plugin?
    $results_tasks[] = 'D';
    foreach ( $bad_cache_plugins as $key => $value ) {
        if ( true === $value ) {
            $temp_results_tasks_auxiliar .= "$key\n";
        }
    }
} else { // Good cache plugin
    foreach ( $good_cache_plugins as $key => $value ) {
        if ( true === $value[0] ) {
            $temp_results_tasks_auxiliar = "$value[1]";
            $results_tasks[] = 'A';
            break; // As soon as we have a good plugin, it's enough
        }
    }
}

if ( '' == $temp_results_tasks_auxiliar ) {
    $results_tasks[] = 'B';
} // The string will be empty if we didn't find any good or bad cache plugin

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;