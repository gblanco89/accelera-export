<?php
/**
 * Module Name: Enable Brotli/Gzip compression
 * Description: Checks the website and tells whether it's using Brotli (preferred) or Gzip or nothing.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Compression not enabled
 * @internal 'B' = Gzip enabled
 * @internal 'C' = Gzip enabled + Cloudflare active
 * @internal 'D' = Brotli enabled
 */

$temp_results_tasks_auxiliar = '';

if ( 'br' === $home_url_headers['content-encoding'] ) {
    $results_tasks[] = 'D';
} else {
    $the_curl_version = curl_version();
    if ( ( version_compare( $the_curl_version['version'], '7.57.0' ) >= 0 ) && ( $the_curl_version['features'] & constant('CURL_VERSION_BROTLI' ) ) ) { // First we check that CURL supports brotli and it is active
        if ( 'gzip' === $home_url_headers['content-encoding'] && $true_cloudflare ) { // If CF active and server has only Gzip
            $results_tasks[] = 'C';
        } elseif ( 'gzip' === $home_url_headers['content-encoding'] ) {
            $results_tasks[] = 'B';
        } else {
            $results_tasks[] = 'A';
        }
    } else {
        $results_tasks[] = 'MAN_CH'; // It's still possible that we are serving Brotli, but we can't know...
    }
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;