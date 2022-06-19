<?php
$temp_results_tasks_auxiliar = '';

if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) { // Just check if the constant is defined
    $results_tasks[] = 'B';
} else {
    $results_tasks[] = 'A';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;