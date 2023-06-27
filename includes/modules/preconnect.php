<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Preconnect to external sites
 * Description: Checks the website and tells whether the user needs to preconnect to external sites.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done + installed plugin can take care
 * @internal 'C' = Not done + no installed plugin can take care
 * @internal 'D' = Too many preconnects
 */

$temp_results_tasks_auxiliar = '';

/**
 * Checks if there is a need to apply preconnect
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param bool $ao Whether Autoptimize is installed and active.
 * @param bool $pfmatters Whether Perfmatters is installed and active.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return string 'A' if preconnect is applied.
 * @return string 'B' if preconnect is not applied but an installed plugin can do it.
 * @return string 'C' if preconnect is not applied and no installed plugin can do it.
 * @return string 'D' if there are too many preconnects.
 */
function preconnects_count( $home_url_body, $ao, $pfmatters, &$temp_results_tasks_auxiliar ) {
    $preconnect = 0; // Counter of preconnects
    $preconnectstr = "/rel=preconnect|rel='preconnect'|rel=\"preconnect\"/";
    preg_match_all( "/<link.*>/i", $home_url_body, $linklines ); // Get all <link

    // Loop through all <link and check if they are preconnected
    foreach( $linklines[0] as $linkgs ) {
        if ( preg_match($preconnectstr, $linkgs) ) {
            $preconnect++;
        }
    }

    if ( $preconnect > 3 ) {
        return 'D';
    } elseif ( $preconnect > 0 ) {
        return 'A'; // If >0 and <=3 preconnects, all good
    } else {
        if ( $ao ) {
            $temp_results_tasks_auxiliar = 'Autoptimize';
            return 'B';
        } elseif ( $pfmatters ) {
            $temp_results_tasks_auxiliar = 'Perfmatters';
            return 'B';
        } else {
            return 'C';
        }
    }
}
$results_tasks[] = preconnects_count( $home_url_body, $ao, $pfmatters, $temp_results_tasks_auxiliar );

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;