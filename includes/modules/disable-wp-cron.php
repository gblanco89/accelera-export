<?php
/**
 * Module Name: Disable WP-Cron
 * Description: Checks whether WP-Cron needs to be replaced with a Real Cron.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Not done
 * @internal 'B' = Done
 */

$temp_results_tasks_auxiliar = '';

if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) { // Just check if the constant is defined
    $results_tasks[] = 'B';
} else {
    $results_tasks[] = 'A';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;