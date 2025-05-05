<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: CSS minification
 * Description: Checks the website and tells whether the user needs to minify CSS files.
 *
 * @since 1.0.0
 *
 * @internal 'A' = No Cloudflare + Not done + no installed plugin can do it
 * @internal 'B' = No Cloudflare + Not done + good installed plugin can do it
 * @internal 'C' = No Cloudflare + Done with a good plugin
 * @internal 'D' = Cloudflare active
 * @internal 'E' = No Cloudflare + Done with a bad plugin
 * @internal 'F' = Cloudflare active through hosting integration
 */

$temp_results_tasks_auxiliar = '';

/**
 * Counts how many unminified CSS files the page has.
 *
 * Finds all local css files (except those .min.css). Regex looks for http(s)://subdomains.domain.com/whatever/blabla.(!min).css
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @param int $lines_per_file The number of lines per file accepted to be unminified.
 * @return int The number of unminified files.
 */
function how_many_unminified_css_files( $home_url_body, $thedomain, $lines_per_file ) {
    $unminimized_css_files = 0;

    if ( preg_match_all( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*(?<!\.min)\.css(?![a-zA-Z0-9]))/", $home_url_body, $css_files ) ) {
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
    return $unminimized_css_files;
}

if ( array_key_exists( 'ki-cf-cache-status', $home_url_headers ) ) {
    $results_tasks[] = 'F';
} elseif ( $true_cloudflare ) {
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
    $results_tasks[] = 'E';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

write_log( 'Accelera Export - Step 7 completed' );