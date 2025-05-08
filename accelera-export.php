<?php
/*
Plugin Name: Accelera Export
Description: A companion plugin that exports website information for assessment by Accelera.
Version: 2.0
Author: Accelera
Author URI: https://accelerawp.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: accelera-export
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

define( 'ACCELERA_EXPORT_PATH', plugin_dir_path( __FILE__ ) );

// Register settings page
function accelera_register_export_settings_page() {
  add_submenu_page( 'tools.php', 'Accelera Export', 'Accelera Export', 'manage_options', 'accelera-export', 'accelera_export_intro' );
  add_submenu_page( null, 'Accelera Export', 'Accelera Export', 'manage_options', 'accelera-export-txt', 'accelera_export_in_csv' );
}
add_action( 'admin_menu', 'accelera_register_export_settings_page' );

// Add action links to plugin list
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_accelera_export_action_links' );
function add_accelera_export_action_links( $links ) {
	$settings_link = array( '<a href="' . admin_url( 'tools.php?page=accelera-export' ) . '">'.__( 'Export data', 'accelera-export' ).'</a>' );
	return array_merge( $links, $settings_link );
}

// Log
if ( ! function_exists( 'write_log' ) ) {
    function write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}


/**
 * Load plugin textdomain.
 */
add_action( 'init', 'accelera_load_textdomain' );
function accelera_load_textdomain() {
  load_plugin_textdomain( 'accelera-export', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// 1st step
require( ACCELERA_EXPORT_PATH . 'includes/intro.php' );

// 2nd step
function accelera_export_in_csv() {
 

    require( ACCELERA_EXPORT_PATH . 'includes/prep.php' );
    require( ACCELERA_EXPORT_PATH . 'includes/modules/database.php' );
    require( ACCELERA_EXPORT_PATH . 'includes/end.php' );
}
