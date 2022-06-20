<?php
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

if ( defined('WPE_CACHE_PLUGIN_BASE') ) { // WP Engine?
    $results_tasks[] = 'C';
    $temp_results_tasks_auxiliar = 'WP Engine';
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