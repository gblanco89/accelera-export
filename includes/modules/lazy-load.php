<?php

if ( ! defined( 'ABSPATH' ) ) { exit;
	// Exit if accessed directly
}

/**
 * Module Name: Images Lazy Load
 * Description: Checks the website and tells whether the user needs to lazy load the images.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Lazy Load is done with SPAI, WP Rocket, Autoptimize, Swift Performance, LiteSpeed Cache, Jetpack Boost or Lazy Loader
 * @internal 'B' = Lazy Load is not in place, but one of the previous possibilities can be done because the plugin is active. SPAI and Lazy Loader just need to be activated.
 * @internal 'C' = Lazy Load is not in place and nothing from above is available.
 * @internal 'D' = Lazy load is done but in another way or with another plugin.
 */

class Accelera_Lazy_Load{

	var $spio_plugins 			= ['shortpixel-adaptive-images/short-pixel-ai.php','wp-rocket/wp-rocket.php','autoptimize/autoptimize.php','swift-performance-lite/performance.php','litespeed-cache/litespeed-cache.php','jetpack-boost/jetpack-boost.php','lazy-loading-responsive-images/lazy-load-responsive-images.php'];
	var $result_task 		= 'C';
	var $auxiliar_task 		= '';
	var $spio_active_plugins 	= [];
	var $all_plugins;

	function __construct( $all_plugins ) {
		$this->all_plugins = $all_plugins;
		$this->check_active_plugins();
		$this->results_tasks();
	}

	function check_active_plugins(){

		foreach($this->spio_plugins as $plugin){

			if( is_plugin_active( $plugin ) ){

				$this->spio_active_plugins []= $plugin;
			}

		}
	}

	function plugin_name( $plugin ){

		return $this->all_plugins[ $plugin ][ 'Name' ];
	}

	function results_tasks(){

		foreach($this->spio_active_plugins as $plugin){

			$result_task = $this->process_task( $plugin );

			//if there's an A situation, there is no need to continue
			if( $result_task == 'A' ){

				$this->result_task = 'A';
				$this->auxiliar_task = $this->plugin_name( $plugin );

				return;
			}


			$this->result_task = 'B';
			$this->auxiliar_task = $this->plugin_name( $plugin );
		}

		if( !empty( $this->spio_active_plugins ) ){

			return;
		}

		if( $this->custom_lazy_load() ){

			$this->result_task = 'D';
			$this->auxiliar_task = '';

		}
		else{

			$this->result_task = 'C';
			$this->auxiliar_task = '';
		}

	}

	function process_task( $plugin ){

		switch ($plugin) {
			case 'shortpixel-adaptive-images/short-pixel-ai.php':
				return $this->shortpixel_adaptive_images();

			case 'wp-rocket/wp-rocket.php':
				return $this->wp_rocket();

			case 'autoptimize/autoptimize.php':
				return $this->autoptimize();

			case 'swift-performance-lite/performance.php':
				return $this->swift_performance_lite();

			case 'litespeed-cache/litespeed-cache.php':
				return $this->litespeed_cache();

			case 'jetpack-boost/jetpack-boost.php':
				return $this->jetpack_boost();

			case 'lazy-loading-responsive-images/lazy-load-responsive-images.php':
				return $this->lazy_loading_responsive_images();

		}

	}

	function shortpixel_adaptive_images(){

		return 'A';
	}

	function wp_rocket(){
		$options = get_option( 'wp_rocket_settings' );

		if( $options ){

			if( isset( $options['lazyload'] ) && $options['lazyload'] == "1" ){

				return 'A';
			}

		}

		return 'B';
	}

	function autoptimize(){
		$options = get_option( 'autoptimize_imgopt_settings' );

		if( isset( $options['autoptimize_imgopt_checkbox_field_3'] ) && $options['autoptimize_imgopt_checkbox_field_3'] == "1" ){

			return 'A';
		}

		return 'B';
	}

	function swift_performance_lite(){
		$options = get_option( 'swift_performance_options' );

		if( $options ){

			if( isset( $options['lazy-load-images'] ) && $options['lazy-load-images'] == "1" ){

				return 'A';
			}
		}

		return 'B';
	}

	function litespeed_cache(){
		$option = get_option( 'litespeed.conf.media-lazy' );

		if( isset( $option ) && $option == "1" ){

			return 'A';
		}

		return 'B';
	}

	function jetpack_boost(){

		$option = get_option( 'jetpack_boost_status_lazy-images' );

		if( $option ){

			if( $option == "1" ){

				return 'A';
			}
		}

		return 'B';
	}

	function lazy_loading_responsive_images(){

		return 'A';
	}

	function custom_lazy_load(){


		$homepage 		= wp_remote_get( get_home_url() );
		$content 		= wp_remote_retrieve_body( $homepage );

		$images 		= $this->check_for_lazy( $content );

		if( $images ){

			$lazy_found 	= 0;
			$total_found 	= count ( $images );

			foreach ( $images as $image ) {

			   // Check if there are images that contain "lazy"
			   if( stripos( $image, 'lazy' ) !== false ){
				  $lazy_found++;
			   }
			}

			if( ( $lazy_found * 100 ) / $total_found >= 50 ) {
				return true;
			}
		}
		else{

			$url 			= $this->check_in_pages_and_posts();
			$page 			= wp_remote_get( $url );
			$content 		= wp_remote_retrieve_body( $page );

			$images 		= $this->check_for_lazy( $content );
			$lazy_found 	= 0;
			$total_found 	= count ( $images );

			foreach ( $images as $image ) {

			   // Check if there are images that contain "lazy"
			   if( stripos( $image, 'lazy' ) !== false ){
				  $lazy_found++;
			   }
			}

			if( ( $lazy_found * 100 ) / $total_found >= 50 ) {
				return true;
			}

		}


		return false;
	}

	function check_in_pages_and_posts(){
		$results = [];

		$pattern 		= '/<img[^>]+src=["\']([^"\']+\.jpg|[^"\']+\.jpeg|[^"\']+\.gif|[^"\']+\.png|[^"\']+\.webp|[^"\']+\.bmp)["\'][^>]*>/i';

		$posts = get_posts(
			[
				'numberposts' 	=> 100,
				'post_type'   	=> 'page',
			]
		);

		foreach($posts as $post){
			$content = $post->post_content;

			if ( preg_match_all( $pattern, $content, $images) ) {

				$results[$post->ID] = count( $images[0] );
			}
		}

		$posts = get_posts(
			[
				'numberposts' 	=> 100,
				'post_type'   	=> 'post',
			]
		);

		foreach($posts as $post){
			$content = $post->post_content;

			if ( preg_match_all( $pattern, $content, $images) ) {

				$results[$post->ID] = count( $images[0] );
			}
		}

		if( empty( $results ) ){ return ''; }

		arsort( $results );

		return get_permalink( array_key_first($results) );
	}

	function check_for_lazy( $content = '' ){

		if( empty( $content ) ){ return; }

		$pattern 		= '/<img[^>]+src=["\']([^"\']+\.jpg|[^"\']+\.jpeg|[^"\']+\.gif|[^"\']+\.png|[^"\']+\.webp|[^"\']+\.bmp)["\'][^>]*>/i';

		if ( preg_match_all( $pattern, $content, $images) ) {

			return $images[0];
		}

		return;
	}
}

//Run the class

$accelera_lazy_load = new Accelera_Lazy_Load( $plugins );
$results_tasks[] = $accelera_lazy_load->result_task;
$results_tasks_auxiliar[] = $accelera_lazy_load->auxiliar_task;;

write_log( 'Accelera Export - Step Lazy Load completed' );
