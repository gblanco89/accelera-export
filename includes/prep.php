<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Before starting with the checks, this makes all the preparations.
 * Variables, getting some technical info, etc.
 *
 * @since 1.0.0
 */

if ( ! function_exists( 'get_plugins' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'plugins_api' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
}

// Preparations - current theme
if ( is_child_theme() ) {
    $my_theme = wp_get_theme()->parent();
} else {
    $my_theme = wp_get_theme();
}

$pluginlist = array();
$plugins = get_plugins();
$mu_plugins = get_mu_plugins();


if ( $plugins ) {
    $sno = 1;
    foreach ( $plugins as $key => $plugin ) {

        // Adding plugin to list except if it's Accelera Export
        if ( 'accelera-export' !== $plugin['TextDomain'] ) {
            $status = 'Inactive';
            if ( is_plugin_active( $key ) ) {
                $status = 'Active';
            }
            $pluginlist[] = $sno . '|' . $plugin['Name'] . '|' . $plugin['Description'] . '|' . $plugin['Author'] . '|' . $status . '|' . $plugin['Version'];
            $sno++;
        }
    }
}

$mu_pluginlist = [];
// dd($plugins); // Debugging
if ( $mu_plugins ) {
    $sno = 1;
    foreach ( $mu_plugins as $key => $plugin ) {

        // Adding plugin to list except if it's Accelera Export
        if ( 'accelera-export' !== $plugin['TextDomain'] ) {
            $status = 'Inactive';
            if ( is_plugin_active( $key ) ) {
                $status = 'Active';
            }
            $mu_pluginlist[] = $sno . '|' . $plugin['Name'] . '|' . $plugin['Description'] . '|' . $plugin['Author'] . '|' . $status . '|' . $plugin['Version'];
            $sno++;
        }
    }
}


// Checks and populating $results_tasks, array of task results
$results_tasks = array();

$thedomain = preg_quote( parse_url( get_home_url() )['host'] ); // Website domain

$acc_webserver = strtolower( explode( '/', $_SERVER['SERVER_SOFTWARE'] )[0] ); // Web server
if ( 'flywheel' === $acc_webserver ) { // Flywheel modifies the server header into 'flywheel/X.Y.Z'; it's just nginx
    $acc_webserver = 'nginx';
}

// Extra server info
$extra_server_info = array();

if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) { // Web server
    $extra_server_info[] = $_SERVER['SERVER_SOFTWARE'];
}
else {
    $extra_server_info[] = '';
}

$extra_server_info[] = get_home_url(); // Website URL