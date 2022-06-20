<?php
/**
 * Module Name: Control Heartbeat API
 * Description: Checks the website and tells whether the user needs to control the WordPress HeartBeat API.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Heartbeat not controlled + no installed plugin can take care of this
 * @internal 'B' = Heartbeat not controlled + installed plugin can take care of this
 * @internal 'C' = Heartbeat controlled
 */

$temp_results_tasks_auxiliar = '';

// Check whether WPRocket/Swift/LiteSpeed/HBbyWPR are installed and taking care of Heartbeat
if ( $good_cache_plugins['rocket'][0] ) {
    if ( $accelera_wprocketoptions['control_heartbeat'] ) {
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'WP Rocket';
    }
} elseif ( $good_cache_plugins['litespeed-cache'][0] ) {
    if ( ( get_option( 'litespeed.conf.misc-heartbeat_front', false ) || get_option( 'litespeed.conf.misc-heartbeat_back', false ) || get_option( 'litespeed.conf.misc-heartbeat_editor', false ) ) > 0 ) {
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'LiteSpeed Cache';
    }
} elseif ( $good_cache_plugins['swift-performance'][0] ) {
    if ( is_array( get_option( 'swift_performance_options', false )['disable-heartbeat'] ) ) {
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'Swift Performance';
    }
} elseif ( $heartbeatplugin ) {
    $results_tasks[] = 'C';
} elseif ( $pfmatters ) {
    if ( ( isset( $accelera_pfmattersoptions['disable_heartbeat'] ) && ! empty( $accelera_pfmattersoptions['disable_heartbeat'] ) ) ||
    ( isset( $accelera_pfmattersoptions['heartbeat_frequency'] ) && ! empty( $accelera_pfmattersoptions['heartbeat_frequency'] ) ) ) {
        $results_tasks[] = 'C';
    } else {
        $results_tasks[] = 'B';
        $temp_results_tasks_auxiliar = 'Perfmatters';
    }
} else { // If no compatible plugin installed
    $results_tasks[] = 'A';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;