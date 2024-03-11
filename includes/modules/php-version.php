<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Update PHP version
 * Description: Checks the website and tells whether it's using an updated and accepted PHP version.
 *
 * @since 1.0.0
 *
 * @internal 'A' = PHP updated to latest version
 * @internal 'B' = PHP needs update
 */

$temp_results_tasks_auxiliar = '';

if ( version_compare( PHP_VERSION, '8.1.0', '>=' ) ) {
    $results_tasks[] = 'A';
} else {
    $temp_results_tasks_auxiliar = PHP_VERSION;
    $results_tasks[] = 'B';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;
