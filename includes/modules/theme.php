<?php
if ( in_array( true, $pagebuilders ) ) {
    $results_tasks[] = 'C';
} elseif ( in_array( $my_theme->get( 'Name' ), $goodthemes ) ) {
    $results_tasks[] = 'A';
} else {
    $results_tasks[] = 'B';
}

$results_tasks_auxiliar[] = $my_theme;

write_log( 'Accelera Export - Step 6 completed' );