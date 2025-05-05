<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Theme analysis
 * Description: Checks the website and tells whether it's using a correct theme and/or
 * user needs to stop using a page builder.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Approved
 * @internal 'B' = Not approved
 * @internal 'C' = Page builder installed
 * @internal 'D' = Good page builder installed
 */

// if ( in_array( true, $pagebuilders ) ) {
//     $results_tasks[] = 'C';
// } elseif ( in_array( true, $good_pagebuilders ) ) {
//     $results_tasks[] = 'D';
// } elseif ( in_array( $my_theme->get( 'Name' ), $goodthemes ) ) {
//     $results_tasks[] = 'A';
// } else {
//     $results_tasks[] = 'B';
// }

// dd($my_theme);

$results_tasks_auxiliar[] = $my_theme;

write_log( 'Accelera Export - Step 6 completed' );