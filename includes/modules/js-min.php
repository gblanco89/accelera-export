<?php
$temp_results_tasks_auxiliar = '';

function how_many_unminified_js_files( $home_url_body, $thedomain, $lines_per_file ) {
    $unminimized_js_files = 0;

    // Find all local js files (except those .min.js). Regex looks for http(s)://subdomains.domain.com/whatever/blabla.(!min).js
    if ( preg_match_all( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*(?<!\.min)\.js)/", $home_url_body, $js_files ) ) {
        // Loop through all the local js files and count how many lines they have
        foreach ( $js_files[0] as $js_file ) {
            $linecount = 0;
            if ( $handle = fopen( $js_file, 'r' ) ) { // Open the js file
                while ( !feof( $handle ) ) { // Count the number of lines
                    $line = fgets( $handle );
                    $linecount++;
                }
                fclose( $handle ); // Close the js file
                if ( $linecount > $lines_per_file ) { // If the js file has more lines than we deem appropriate, we'll consider it not minified
                    $unminimized_js_files++;
                }
            }
        }
    }
    return $unminimized_js_files; // Return the number of files that we don't think are minified
}

if ( $true_cloudflare ) {
    $results_tasks[] = 'D';
} elseif ( // If WP Rocket, AO or LiteSpeed are already minimizing
    ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['minify_js'] ) ||
    ( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_min', false ) > 0 ) ||
    ( $ao && get_option( 'autoptimize_js' ) == 'on' ) ||
    ( $assetcleanup && strpos( $accelera_assetcleanoptions, '"minify_loaded_js":"1"' ) !== false ) ) {
    $results_tasks[] = 'C';
} elseif ( how_many_unminified_js_files( $home_url_body, $thedomain, 4 ) > 0 ) { // Unminified
    if ( $ao || $good_cache_plugins['rocket'][0] || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) {
        $results_tasks[] = 'B';
    } else {
        $results_tasks[] = 'A';
    }
}
else { // Minified, either because there's nothing to minimize or because of a bad plugin
    if ( $ao || $good_cache_plugins['rocket'][0] || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) { // Ideally we would check if there's a bad plugin minimizing, to do
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'E';
    }
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;