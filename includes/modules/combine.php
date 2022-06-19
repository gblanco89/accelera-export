<?php
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