<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Combine CSS/JS
 * Description: Checks the website and tells whether the user needs to combine CSS/JS files
 * based on HTTP/2.
 *
 * @since 1.0.0
 *
 * @internal 'A' = HTTP/1
 * @internal 'B' = HTTP/2
 */

$temp_results_tasks_auxiliar = '';

if ( $http2_support ) {
    $results_tasks[] = 'B';
    $results_tasks[] = 'B';
} else {
    $results_tasks[] = 'A';
    $results_tasks[] = 'A';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;
$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

write_log( 'Accelera Export - Step 8 completed' );