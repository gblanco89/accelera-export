<?php
$temp_results_tasks_auxiliar = '';

function how_many_unminified_css_files( $home_url_body, $thedomain, $lines_per_file ) {
    $unminimized_css_files = 0;

    // Find all local css files (except those .min.css). Regex looks for http(s)://subdomains.domain.com/whatever/blabla.(!min).css
    if ( preg_match_all( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*(?<!\.min)\.css)/", $home_url_body, $css_files ) ) {
        // Loop through all the local CSS files and count how many lines they have
        foreach( $css_files[0] as $css_file ) {
            $linecount = 0;
            if ( $handle = fopen( $css_file, 'r' ) ) { // Open the CSS file
                while ( !feof( $handle ) ) { // Count the number of lines
                    $line = fgets( $handle );
                    $linecount++;
                }
                fclose( $handle ); // Close the CSS file
                if ( $linecount > $lines_per_file ) { // If the CSS file has more lines than we deem appropriate, we'll consider it not minified
                    $unminimized_css_files++;
                }
            }
        }
    }
    return $unminimized_css_files; // Return the number of files that we don't think are minified
}

if( $true_cloudflare ) {
    $results_tasks[] = 'D';
} elseif ( //If SPAI, WP Rocket, AO or LiteSpeed are already minimizing
    ( $spai && $accelera_spaioptions->settings->areas->parse_css_files ) ||
    ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['minify_css'] ) ||
    ( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-css_min', false ) > 0 ) ||
    ( $ao && get_option( 'autoptimize_css' ) == 'on' ) ||
    ( $assetcleanup && strpos( $accelera_assetcleanoptions, '"minify_loaded_css":"1"' ) !== false ) ) {
    $results_tasks[] = 'C';
} elseif ( how_many_unminified_css_files( $home_url_body, $thedomain, 4 ) > 0 ) { // Unminified
    if ( $ao ) {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'Autoptimize';
    } elseif ( $good_cache_plugins['rocket'][0] ) {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'WP Rocket';
    } elseif ( $good_cache_plugins['litespeed-cache'][0] ) {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'LiteSpeed Cache';
    } elseif ( $good_cache_plugins['flying-press'][0] ) {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'FlyingPress';
    } elseif ( $spai ) {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'ShortPixel Adaptive Images';
    } elseif ( $assetcleanup ) {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'Asset CleanUp';
    } else {
        $results_tasks[] = 'A';
    }
} else { // Minified, either because there's nothing to minimize or because of a bad plugin
    if ( $spai || $good_cache_plugins['rocket'][0] || $ao || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) { // Ideally we would check if there's a bad plugin minimizing, to do
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'E';
    }
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

write_log( 'Accelera Export - Step 7 completed' );