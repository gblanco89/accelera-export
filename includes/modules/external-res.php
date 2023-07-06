<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: External resources optimization
 * Description: Checks the website and tells whether the user needs to optimize Google Fonts,
 * Google Analytics, delay the parsing of JS and/or jQuery is loaded externally.
 * The output is a binary code like "10010"
 *
 * @since 1.0.0
 *
 * @internal 1st = Google Analytics is found
 * @internal 2nd = Google Fonts is found
 * @internal 3rd = The "Flying Scripts" plugin is active
 * @internal 4th = WP Rocket is active and its "Delay JavaScript execution" option is enabled
 * @internal 5th = jQuery is served externally (like from google servers)
 */

$temp_results_tasks_auxiliar = '';

$gf = $ga = $wpr_delay = false;

/*$accelera_homepage_html = '';
$accelera_homepage_response	= wp_remote_get( get_home_url() );
if ( is_array( $accelera_homepage_response ) && ! is_wp_error( $accelera_homepage_response ) ) {
	$accelera_homepage_html = wp_remote_retrieve_body( $accelera_homepage_response );
}*/

/**
 * Checks if there is Google Fonts in the code
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param array $good_cache_plugins An array of good cache plugins to consider.
 * @param mixed $my_theme The current theme.
 * @return bool true if there are Google Fonts loaded.
 * @return bool false if there are no Google Fonts loaded.
 */
function isthere_gf( $home_url_body, $good_cache_plugins, $my_theme ) {
    if ( ( 'Astra' === $my_theme->get( 'Name' ) ) && get_option( 'astra-settings', false )['load-google-fonts-locally'] ) { // If Astra is locally hosting fonts
        return false;
    } elseif ( ( preg_match( "/url[^\"']+fonts\.gstatic\.com[^\"']*\.ttf|fonts\.googleapis\.com\/css|<link[^>]*fonts\.gstatic\.com[^\"']*\.woff2.*?>/", $home_url_body ) ) ||
     ( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-ggfonts_async', false ) ) ) {
        return true;
    } else {
        return false;
    }
   }

/**
 * Checks if there is Google Analytics in the code
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @return bool true if there are Google Analytics loaded.
 * @return bool false if there are no Google Analytics loaded.
 */
function isthere_ga( $home_url_body ) {
    if ( preg_match( "/<script.*googletagmanager\.com\/gtag\/js\?id=[UAG]+-[0-9A-Z-]+(\"|')/s", $home_url_body ) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if the jQuery library is served from external servers
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @return bool true if jQuery is loaded externally
 * @return bool false if jQuery is loaded from same server
 */
function accelera_jquery_is_external( $home_url_body ) {

	if ( empty( $home_url_body ) ) { return false; }

	$pattern = '/<script.*?src=["\'](.*?)["\'].*?>/i';

	if ( preg_match_all( $pattern, $home_url_body, $matches ) ) {

		foreach( $matches[1] as $src ) {
			if ( stripos( $src, 'jquery.' ) !== false ) {

				if ( stripos( $src, get_home_url() ) !== false ) {
					return false;
				}

				return true;
			}
		}
	}

	return false;
}


if ( isthere_gf( $home_url_body, $good_cache_plugins, $my_theme ) ) {
    $gf = true;
}
if ( isthere_ga( $home_url_body ) ) {
    $ga = true;
}
if ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['delay_js'] ) {
    $wpr_delay = true;
}
$jQuery = accelera_jquery_is_external( $home_url_body );

// Let's build the binary string
$ga_out 			= $ga ? '1' : '0'; 							//Google Analytics
$gf_out 			= $gf ? '1' : '0'; 							//Google Fonts
$flyingscripts_out 	= $flyingscripts ? '1' : '0';				//Flying Scripts
$wpr_delay_out 		= $wpr_delay ? '1' : '0';					//WP Rocket is active and its "Delay JavaScript execution" option is enabled
$jQuery_out 		= $jQuery ? '1' : '0';						//jQuery is served externally

$digital_result = $ga_out . $gf_out . $flyingscripts_out . $wpr_delay_out . $jQuery_out;

$results_tasks[] =  '"' . $digital_result . '"'; //added quotes because of Excel views the zeros only as one
$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;


write_log( 'Accelera Export - Step 10 completed' );