<?php
/**
 * Module Name: JS minification
 * Description: Checks the website and tells whether the user needs to minify JS files.
 *
 * @since 1.0.0
 *
 * @internal 'A' = No Cloudflare + Not done + no installed plugin can do it
 * @internal 'B' = No Cloudflare + Not done + good installed plugin can do it
 * @internal 'C' = No Cloudflare + Done with a good plugin
 * @internal 'D' = Cloudflare active
 * @internal 'E' = No Cloudflare + Done with a bad plugin
 */

$temp_results_tasks_auxiliar = '';

/**
 * Counts how many unminified JS files the page has.
 *
 * Finds all local css files (except those .min.js). Regex looks for http(s)://subdomains.domain.com/whatever/blabla.(!min).js
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @param int $lines_per_file The number of lines per file accepted to be unminified.
 * @return int The number of unminified files.
 */
function how_many_unminified_js_files( $home_url_body, $thedomain, $lines_per_file ) {
    $unminimized_js_files = 0;

    if ( preg_match_all( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*(?<!\.min)\.js(?![a-zA-Z0-9]))/", $home_url_body, $js_files ) ) {
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
    return $unminimized_js_files;
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
    if ( $ao || $good_cache_plugins['rocket'][0] || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) { // Ideally we would check if there's a bad plugin minimizing (@todo)
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'E';
    }
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;