<?php
$temp_results_tasks_auxiliar = '';

if ( has_filter( 'the_content', 'capital_P_dangit' ) ) {
    $results_tasks[] = 'B'; // Not done
} else {
    $results_tasks[] = 'A'; // Done
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;