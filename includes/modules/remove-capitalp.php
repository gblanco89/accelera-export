<?php
/**
 * Module Name: Remove Capital P Dangit filter
 * Description: Checks the website and tells whether the user needs to remove the Capital P Dangit filter.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done
 */

$temp_results_tasks_auxiliar = '';

if ( has_filter( 'the_content', 'capital_P_dangit' ) ) {
    $results_tasks[] = 'B'; // Not done
} else {
    $results_tasks[] = 'A'; // Done
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;