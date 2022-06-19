<?php
$temp_results_tasks_auxiliar = '';

function arejs_deferred( $home_url_body, $thedomain ) {
    $deferred = 0; // Counter of deferred
    $deferstring = "/defer='defer'|defer=\"defer\"/";

    // Find all local js files
    preg_match_all( "/<script.*{$thedomain}.*\.js.*<\/script>/i", $home_url_body, $js_files );

    // Loop through all the local js files and check if they are deferred
    foreach ( $js_files[0] as $js_file ) {
        if ( preg_match( $deferstring, $js_file ) ) {
            $deferred++;
        }
    }
    if ( count( $js_files[0] ) - $deferred <= 3 ) {
        return 'B'; // Return if files are deferred
    } else {
        return 'A';
    }
}

// If WP Rocket/AO/LiteSpeed are already doing that, all good
if ( ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['defer_all_js'] ) ||
    ( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_defer', false ) > 0 ) ||
    ( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_defer_not_aggregate' ) == 'on' ) ||
    ( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_aggregate' ) == 'on' && ! get_option( 'autoptimize_js_forcehead' ) ) ) {
        $results_tasks[] = 'B';
} else {
    $results_tasks[] = arejs_deferred( $home_url_body, $thedomain );
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;