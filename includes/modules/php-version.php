<?php
$temp_results_tasks_auxiliar = '';

if ( version_compare( PHP_VERSION, '7.4.0', '>=' ) ) {
    $results_tasks[] = 'A';
} else {
    $temp_results_tasks_auxiliar = PHP_VERSION;
    $results_tasks[] = 'B';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;