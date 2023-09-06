<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Defer parsing of JS
 * Description: Checks the website and tells whether the user needs to defer JS files.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Not done
 * @internal 'B' = Done
 * @internal 'C' = Swift Performance doing it
 */

$temp_results_tasks_auxiliar = '';

/**
 * Checks if the JS files are deferred
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return string 'B' if we consider all JS files are deferred.
 * @return string 'A' if we consider JS files are not deferred.
 */
function arejs_deferred( $home_url_body, $thedomain, &$temp_results_tasks_auxiliar ) {
    $deferred = 0; // Counter of deferred
    $deferstring = "/defer='true'|defer=\"true\"|defer='defer'|defer=\"defer\"|defer=\"\"|defer=''|defer\s/";

    // Find all local js files
    preg_match_all( "/<script.*{$thedomain}.*\.js.*<\/script>/i", $home_url_body, $js_files );

    // Loop through all the local js files and check if they are deferred
    foreach ( $js_files[0] as $js_file ) {
        if ( preg_match( $deferstring, $js_file ) ) {
            $temp_results_tasks_auxiliar .= $js_file . "\n";
            $deferred++;
        }
    }
    if ( count( $js_files[0] ) - $deferred <= 3 ) {
        return 'B';
    } else {
        return 'A';
    }
}

// If WP Rocket/AO/LiteSpeed are already doing that, all good
if ( $http2_support && $bad_cache_plugins['swift-performance'][0] && $accelera_swiftoptions['merge-scripts'] > 0 && $accelera_swiftoptions['async-scripts'] > 0 ) { // If HTTP2 + Swift + Merge Scripts + Async
    $results_tasks[] = 'C';
} elseif ( ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['defer_all_js'] ) ||
    ( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_defer', false ) > 0 ) ||
    ( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_defer_not_aggregate' ) == 'on' ) ||
    ( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_aggregate' ) == 'on' && ! get_option( 'autoptimize_js_forcehead' ) ) ) {
        $results_tasks[] = 'B';
} else {
    $results_tasks[] = arejs_deferred( $home_url_body, $thedomain, $temp_results_tasks_auxiliar );
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;