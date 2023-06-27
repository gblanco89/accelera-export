<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: DNS Prefetch
 * Description: Checks the website and tells whether the user needs to apply DNS Prefetch.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done + installed plugin can take care
 * @internal 'C' = Not done + no installed plugin can take care
 */

$temp_results_tasks_auxiliar = '';

/**
 * Checks if there is a need to apply dns-prefetch
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param array $good_cache_plugins An array of good cache plugins to consider.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return string 'A' if dns-prefetch is applied.
 * @return string 'B' if dns-prefetch is not applied but an installed plugin can do it.
 * @return string 'C' if dns-prefetch is not applied and no installed plugin can do it.
 */
function dnsprefetch_count( $home_url_body, $good_cache_plugins, &$temp_results_tasks_auxiliar ) {
    $dnsprefetch = 0; // Counter of dns-prefetch
    $dnsprefetchtr = "/rel='dns-prefetch'|rel=\"dns-prefetch\"/";
    preg_match_all( "/<link.*>/i", $home_url_body, $linklines );

    foreach( $linklines[0] as $linkgs ) {
        if ( preg_match( $dnsprefetchtr, $linkgs ) ) {
            $dnsprefetch++;
        }
    }

    if ( $dnsprefetch <= 2 ) { // If only <=2 dns-prefetch, needs to be done
        if ( $good_cache_plugins['rocket'][0] ) {
            $temp_results_tasks_auxiliar = 'WP Rocket';
            return 'B';
        } elseif ( $good_cache_plugins['swift-performance'][0] ) {
            $temp_results_tasks_auxiliar = 'Swift Performance';
            return 'B';
        } elseif ( $good_cache_plugins['litespeed-cache'][0] ) {
            $temp_results_tasks_auxiliar = 'LiteSpeed Cache';
            return 'B';
        } else {
            return 'C';
        }
    } else {
        return 'A';
    }
}
$results_tasks[] = dnsprefetch_count( $home_url_body, $good_cache_plugins, $temp_results_tasks_auxiliar );

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

write_log( 'Accelera Export - Step 11 completed' );