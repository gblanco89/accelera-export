<?php
$temp_results_tasks_auxiliar = '';

if ( ! get_option( 'default_pingback_flag' ) ) {
    $results_tasks[] = 'A';
} else {
    $results_tasks[] = 'B'; // Unknown
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;