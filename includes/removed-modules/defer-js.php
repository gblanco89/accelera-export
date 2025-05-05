<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Defer parsing of JS
 * Description: Checks the website and tells whether the user needs to defer JS files.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Not done
 * @internal 'B' = Done
 * @internal 'C' = Swift Performance doing it
 */
$temp_results_tasks_auxiliar = '';

function async_defer_in_content( $value = 'async' ) {
	$homepage 		= wp_remote_get( get_home_url() );
	$content 		= wp_remote_retrieve_body( $homepage );

	//get all <script> tags that have src attribute, so they are not inline javascript
	$pattern = '/<script\b(?:(?!<\/script>).)*?src=["\'](.*?)["\'].*?><\/script>/i';
	preg_match_all($pattern, $content, $all_script_tags);
	$all_js_files = isset( $all_script_tags[0] ) ? count( $all_script_tags[0] ) : 0;

	$pattern = '/<script\b[^>]*\b'.$value.'\b[^>]*>.*?<\/script>/i';
    preg_match_all($pattern, $content, $matches);
	$results = count( $matches[0] );

	if( $results >= 3 ){

		return 'B';
	}
	elseif( $results / $all_js_files > 0.5 ){

		return 'B';
	}

	return 'A';
}

function async_or_defer_done( $http2_support, $bad_cache_plugins, $accelera_swiftoptions, $good_cache_plugins, $accelera_wprocketoptions, $ao ){

	$Autoptimize_active 		= is_plugin_active('autoptimize/autoptimize.php');
	$Async_JavaScript_active 	= is_plugin_active('async-javascript/async-javascript.php');
	$Swift_active				= is_plugin_active('swift-performance-lite/performance.php');

	$Swift_opts			= get_option( 'swift_performance_options' );
	$Swift_Async		= isset( $Swift_opts['async-scripts'] ) ? $Swift_opts['async-scripts'] : false;


	$Async_Enabled  	= get_option('aj_enabled');
	$Async_Method  		= get_option('aj_method');

	$Autoptimize_Method 	= get_option('aj_autoptimize_method');
	$Autoptimize_opts  		= get_option('autoptimize_extra_settings');
	$Autoptimize_Async  	= $Autoptimize_opts['autoptimize_extra_text_field_3'] ?? false;

	// If WP Rocket/AO/LiteSpeed are already doing that, all good
	if ( $http2_support && $bad_cache_plugins['swift-performance'][0] && $accelera_swiftoptions['merge-scripts'] > 0 && $accelera_swiftoptions['async-scripts'] > 0 ) {
	// If HTTP2 + Swift + Merge Scripts + Async
		return 'C';
	}

	if ( ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['defer_all_js'] ) ||
		( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_defer', false ) > 0 ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_defer_not_aggregate' ) == 'on' ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_aggregate' ) == 'on' && ! get_option( 'autoptimize_js_forcehead' ) ) ) {

			return 'B';
	}

	if( $Autoptimize_active ){

		if( $Async_JavaScript_active ){
			if( $Async_Enabled ){
				return 'B';
			}
		}

		if( $Autoptimize_Async  ){
			return 'B';
		}

	}

	if( $Async_JavaScript_active ){
		if( $Async_Enabled  ){
			return 'B';
		}
	}

	if( $Swift_active ){
		if( $Swift_Async ){
			return 'C';
		}
	}


	$js_defer = async_defer_in_content( 'defer' );
	$js_async = async_defer_in_content( 'async' );

	if( $js_defer == 'B' || $js_async == 'B' ){
		return 'B';
	}
	else{
		return 'A';
	}

}


function async_or_defer_done_auxiliar( $http2_support, $bad_cache_plugins, $accelera_swiftoptions, $good_cache_plugins, $accelera_wprocketoptions, $ao ){

	$Autoptimize_active 		= is_plugin_active('autoptimize/autoptimize.php');
	$Async_JavaScript_active 	= is_plugin_active('async-javascript/async-javascript.php');
	$Swift_active				= is_plugin_active('swift-performance-lite/performance.php');

	$Swift_opts			= get_option( 'swift_performance_options' );
	$Swift_Async		= isset( $Swift_opts['async-scripts'] ) ? $Swift_opts['async-scripts'] : false;

	$Async_Enabled  	= get_option('aj_enabled');
	$Async_Method  		= get_option('aj_method');

	$Autoptimize_Method 	= get_option('aj_autoptimize_method');
	$Autoptimize_opts  		= get_option('autoptimize_extra_settings');
	$Autoptimize_Async  	= $Autoptimize_opts['autoptimize_extra_text_field_3'] ?? false;

	// If WP Rocket/AO/LiteSpeed are already doing that, all good
	if ( $http2_support && $bad_cache_plugins['swift-performance'][0] && $accelera_swiftoptions['merge-scripts'] > 0 && $accelera_swiftoptions['async-scripts'] > 0 ) {
	// If HTTP2 + Swift + Merge Scripts + Async
		return 'HTTP2 + Swift + Merge Scripts + Async';
	}

	if ( ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['defer_all_js'] ) ||
		( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_defer', false ) > 0 ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_defer_not_aggregate' ) == 'on' ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_aggregate' ) == 'on' && ! get_option( 'autoptimize_js_forcehead' ) ) ) {

			return 'Autoptimize | Wp Rocket | Litespeed Cache';
	}

	if( $Autoptimize_active ){

		if( $Async_JavaScript_active ){
			if( $Async_Enabled ){
				return 'Autoptimize & Async JavaScript';
			}
		}

		if( $Autoptimize_Async  ){
			return 'Autoptimize';
		}

	}

	if( $Async_JavaScript_active ){
		if( $Async_Enabled  ){
			return 'Async JavaScript';
		}
	}

	if( $Swift_active ){
		if( $Swift_Async ){
			return 'Swift Performance';
		}
	}


	$js_defer = async_defer_in_content( 'defer' );
	$js_async = async_defer_in_content( 'async' );

	if( $js_defer == 'B' || $js_async == 'B' ){
		if( $js_defer == 'B' ){
			return '"Defer" found in the page body.';
		}
		elseif( $js_async == 'B' ){
			return '"Async" found in the page body.';
		}
	}
	else{
		return '"Async" or "defer" NOT done.';
	}

}

$results_tasks[] 			= async_or_defer_done( $http2_support, $bad_cache_plugins, $accelera_swiftoptions, $good_cache_plugins, $accelera_wprocketoptions, $ao );
$results_tasks_auxiliar[] 	= async_or_defer_done_auxiliar( $http2_support, $bad_cache_plugins, $accelera_swiftoptions, $good_cache_plugins, $accelera_wprocketoptions, $ao );
