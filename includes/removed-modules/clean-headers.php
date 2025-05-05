<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Clean headers of website
 * Description: Checks the website and tells whether the WordPress headers have been cleaned or not.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done + installed plugin can do it
 * @internal 'C' = Not done + no installed plugin can do it
 */

$temp_results_tasks_auxiliar = '';

/**
 * Checks if the headers of WordPress are clean
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @param bool $pfmatters Whether Perfmatters is activated.
 * @param bool $assetcleanup Whether Asset CleanUp is activated.
 * @return string 'A' if headers are cleaned.
 * @return string 'B' if headers are not cleaned but an installed plugin can do it.
 * @return string 'C' if headers are not cleaned and no installed plugin can do it.
 */
function are_headers_clean( $home_url_body, &$temp_results_tasks_auxiliar, $pfmatters, $assetcleanup ) {
    $cleanstring = "/<meta name=\"generator\" content=\"WordPress|<link rel=\"wlwmanifest/"; // If we see that the wp version is removed or wlwmanifest is not there, means wp headers have been tweaked, means done.
    if ( preg_match( $cleanstring, $home_url_body ) ) {
        if ( $pfmatters ) {
            $temp_results_tasks_auxiliar = 'Perfmatters';
            return 'B';
        } elseif ( $assetcleanup ) {
            $temp_results_tasks_auxiliar = 'Asset CleanUp';
            return 'B';
        } else {
            return 'C';
        }
    } else {
        return 'A';
    }
}
$results_tasks[] = are_headers_clean( $home_url_body, $temp_results_tasks_auxiliar, $pfmatters, $assetcleanup );

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;