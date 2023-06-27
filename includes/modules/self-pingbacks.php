<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Disable self pingbacks
 * Description: Checks the website and tells whether the user needs to disable self pingbacks.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Unknown
 */

$temp_results_tasks_auxiliar = '';

if ( ! get_option( 'default_pingback_flag' ) ) {
    $results_tasks[] = 'A';
} else {
    $results_tasks[] = 'B'; // Unknown
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;