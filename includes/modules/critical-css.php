<?php

if ( ! defined( 'ABSPATH' ) ) { exit;
	// Exit if accessed directly
}
/**
 * Module Name: Critical CSS
 * Description: Checks the website and tells the status of it regarding critical CSS
 *
 * @since 1.0.0
 *
 * @internal 'A' = Critical CSS is already in place thanks to Jetpack Boost, Critical CSS For WP, LiteSpeed Cache, ShortPixel Critical CSS, Autoptimize, WP Rocket or Swift Perfromance
 * @internal 'B' = Critical CSS is not done yet, but there is a plugin active from the list above that can do this.
 * @internal 'C' = Critical CSS is not done yet and there is no plugin active from the list above that can do this.
 * @internal 'D' = Critical CSS is done, but not thanks to one of the plugins below.
 */

class Accelera_Critical_Css{

	var $plugins 			= ['jetpack-boost/jetpack-boost.php','critical-css-for-wp/critical-css-for-wp.php','litespeed-cache/litespeed-cache.php','swift-performance-lite/performance.php','shortpixel-critical-css/shortpixel-critical-css.php','autoptimize/autoptimize.php','wp-rocket/wp-rocket.php'];
	var $result_task 		= 'C';
	var $auxiliar_task 		= '';
	var $active_plugins 	= [];
	var $all_plugins;

	function __construct( $all_plugins ) {
		$this->all_plugins = $all_plugins;
		$this->check_active_plugins();
		$this->results_tasks();
	}

	function check_active_plugins(){

		foreach($this->plugins as $plugin){

			if( is_plugin_active( $plugin ) ){

				$this->active_plugins []= $plugin;
			}

		}
	}

	function plugin_name( $plugin ){

		return $this->all_plugins[ $plugin ][ 'Name' ];
	}

	function results_tasks(){

		if( empty( $this->active_plugins ) ){

			//check for a custom critical CSS
			if( $this->custom_critical_css() ){

				$this->result_task = 'D';
			}
			else{

				$this->result_task = 'C';
			}

			return;
		}

		foreach($this->active_plugins as $plugin){

			$result_task = $this->process_task( $plugin );

			//if there's an A situation, there is no need to continue
			if( $result_task == 'A' ){

				$this->result_task = 'A';
				$this->auxiliar_task = $this->plugin_name( $plugin );

				return;
			}
		}

		$this->result_task = 'B';
		$this->auxiliar_task = $this->plugin_name( $this->active_plugins[0] );
	}

	function process_task( $plugin ){

		switch ($plugin) {
			case 'jetpack-boost/jetpack-boost.php':
				return $this->jetpack_boost();

			case 'critical-css-for-wp/critical-css-for-wp.php':
				return $this->critical_css_for_wp();

			case 'litespeed-cache/litespeed-cache.php':
				return $this->litespeed_cache();

			case 'swift-performance-lite/performance.php':
				return $this->swift_performance_lite();

			case 'shortpixel-critical-css/shortpixel-critical-css.php':
				return $this->shortpixel_critical_css();

			case 'autoptimize/autoptimize.php':
				return $this->autoptimize();

			case 'wp-rocket/wp-rocket.php':
				return $this->wp_rocket();
		}

	}

	function jetpack_boost(){
		if( get_option( 'jetpack_boost_status_critical-css' ) == "1" ){

			return 'A';
		}

		return 'B';
	}

	function critical_css_for_wp(){

		return 'A';

		/*
		$option = get_option( 'ccfwp_settings' );

		if( $option ){

			if( isset( $option['ccfwp_on_cp_type'] ) && !empty( $option['ccfwp_on_cp_type'] ) ){

				return 'A';
			}
		}

		return 'B';

		*/
	}

	function litespeed_cache(){
		//$license = get_option( 'litespeed.conf.optm-ccss_con' );
		$api_key = get_option( 'litespeed.conf.api_key' );
		$css_async = get_option( 'litespeed.conf.optm-css_async' );

		if( $api_key && $css_async ){

			return 'A';
		}

		return 'B';
	}

	function swift_performance_lite(){
		$options = get_option( 'swift_performance_options' );

		if( $options ){

			if( isset( $options['critical-css'] ) && $options['critical-css'] == "1" && isset( $options['merge-styles'] ) && $options['merge-styles'] == "1" ){

				return 'A';
			}
		}

		return 'B';
	}

	function shortpixel_critical_css(){
		$options = get_option( 'shortpixel_critical_css' );

		if( isset( $options['apikey'] ) && !empty( $options['apikey'] ) ){

			return 'A';
		}

		return 'B';
	}

	function autoptimize(){
		$css_defer = get_option( 'autoptimize_css_defer' );

		if( is_plugin_active( 'autoptimize-pro/autoptimize-pro.php' ) && $css_defer == "on" ){

			return 'A';
		}

		$api_key = get_option( 'autoptimize_ccss_key' );

		if( !empty( $api_key ) && $css_defer == "on" ){

			return 'A';
		}

		return 'B';
	}

	function wp_rocket(){
		$options = get_option( 'wp_rocket_settings' );

		if( $options ){

			if( isset( $options['critical_css'] ) && $options['critical_css'] == "1" ){

				return 'A';
			}

			if( isset( $options['async_css'] ) && $options['async_css'] == "1" ){

				return 'A';
			}
		}

		return 'B';
	}

	function custom_critical_css(){

		//check for custom critical css
		$homepage 	= wp_remote_get( get_home_url() );
		$content 	= wp_remote_retrieve_body( $homepage );

		// Regular expression pattern to match <style> tags
		$pattern = '/<style\b[^>]*>(.*?)<\/style>/s';

		// Perform the matching
		if ( preg_match_all( $pattern, $content, $matches ) ) {
			// $matches[0] contains the complete <style> tags
			foreach ( $matches[0] as $match ) {
			   // Check if there are any styles that contain "critical"
			   if( stripos( $match, 'critical' ) !== false ){
				  return true;
			   }
			}
		}

		return false;

	}
}

//Run the class
$accelera_critical_css = new Accelera_Critical_Css( $plugins );

$results_tasks[] = $accelera_critical_css->result_task;
$results_tasks_auxiliar[] = $accelera_critical_css->auxiliar_task;;

write_log( 'Accelera Export - Step Critical CSS completed' );
