<?php
$temp_results_tasks_auxiliar = '';

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