<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Use HTTP/2
 * Description: Checks the website and tells whether it's using HTTP/2 or not.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done
 */

$temp_results_tasks_auxiliar = '';

if ( $http2_support ) {
    $results_tasks[] = 'A';
} else {
    $results_tasks[] = 'B';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;