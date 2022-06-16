<?php
/*
Plugin Name: Accelera Export
description: Companion app for Accelera Assessment service
Version: 0.15
Author: Accelera
Author URI: https://accelera.autoptimize.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: accelera-export
*/

function accelera_register_export_settings_page() {
  add_submenu_page( 'tools.php', 'Accelera Export', 'Accelera Export', 'manage_options', 'accelera-export', 'accelera_export' );
  add_submenu_page( null, 'Accelera Export', 'Accelera Export', 'manage_options', 'accelera-export-csv', 'accelera_export_in_csv' );
}
add_action( 'admin_menu', 'accelera_register_export_settings_page' );

// Add action links to plugin list
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_accelera_esport_action_links' );
function add_accelera_esport_action_links( $links ) {
	$settings_link = array( '<a href="' . admin_url( 'tools.php?page=accelera-export' ) . '">Export data</a>' );
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


/*
 ██╗███████╗████████╗    ███████╗████████╗███████╗██████╗
███║██╔════╝╚══██╔══╝    ██╔════╝╚══██╔══╝██╔════╝██╔══██╗
╚██║███████╗   ██║       ███████╗   ██║   █████╗  ██████╔╝
 ██║╚════██║   ██║       ╚════██║   ██║   ██╔══╝  ██╔═══╝
 ██║███████║   ██║       ███████║   ██║   ███████╗██║
 ╚═╝╚══════╝   ╚═╝       ╚══════╝   ╚═╝   ╚══════╝╚═╝
*/
function accelera_export() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! function_exists( 'plugins_api' ) ) {
		  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

	// Removing other annoying notices
	echo '<style>.update-nag, .notice-info:not(.accelera-notice), .updated, .error, .is-dismissible, .ngg_admin_notice { display: none !important; }</style>';

	?>
	<div class="wrap">
		<h1>Accelera Export</h1>

		<?php // Checking for conflicting plugins - NitroPack
		if ( in_array( 'nitropack/main.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		?>
		<div class="notice notice-warning accelera-notice">
			<p><?php _e( 'Hey! Looks like you have Nitropack installed and active. Please go to <a href="options-general.php?page=nitropack">Nitropack\'s settings</a> and enable Safe Mode. Then, return here and continue with the process.' ); ?></p>
		</div>
		<?php
		}
		?>

		<div>
			<p>
			<?php _e( 'This is just the first step before exporting your site information. Please follow the next steps:<br />
				<ol>
					<li>Click on the button "CSV Export" below and wait a few seconds until the plugin collects all the information. <strong>Do not refresh or exit this page.</strong></li>
					<li>Download the CSV in the next page.</li>
					<li>Send the CSV, with no modification, to <a href="mailto:support@accelera.site">support@accelera.site</a>.</li>
				</ol>', 'accelera-export' );
			?>
			</p>
		</div>

		<a href="tools.php?page=accelera-export-csv" class="button button-primary">CSV Export</a>
	</div>
	<?php
	}


/*
██████╗ ███╗   ██╗██████╗     ███████╗████████╗███████╗██████╗
╚════██╗████╗  ██║██╔══██╗    ██╔════╝╚══██╔══╝██╔════╝██╔══██╗
 █████╔╝██╔██╗ ██║██║  ██║    ███████╗   ██║   █████╗  ██████╔╝
██╔═══╝ ██║╚██╗██║██║  ██║    ╚════██║   ██║   ██╔══╝  ██╔═══╝
███████╗██║ ╚████║██████╔╝    ███████║   ██║   ███████╗██║
╚══════╝╚═╝  ╚═══╝╚═════╝     ╚══════╝   ╚═╝   ╚══════╝╚═╝
*/
function accelera_export_in_csv() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! function_exists( 'plugins_api' ) ) {
		  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}


	/////**********************************************************************/////
	///// Preparations - current theme
	/////**********************************************************************/////
	if ( is_child_theme() ) {
		$my_theme = wp_get_theme()->parent();
	} else {
		$my_theme = wp_get_theme();
	}

	write_log( 'Accelera Export - Step 1 completed' );


	/////**********************************************************************/////
	///// Preparations - theme and plugin definitions
	/////**********************************************************************/////
	$goodthemes = array( 'Twenty Nineteen', 'Twenty Twenty', 'Twenty Twenty-One', 'Neve', 'Blocksy', 'Astra', 'OceanWP', 'Storefront', 'Suki', 'Kadence', 'Mesmerize', 'MagazineWP', 'Acabado', 'Extra', 'Genesis', 'GeneratePress', 'Button Theme', 'Basic' );

	$spai = $spio = $ao_images = $ao = $pfmatters = $wpoptimize = $heartbeatplugin = $flyingscripts = $jetpack = $assetcleanup = false;

	$bad_image_optimizers = array(
		'wp-smushit' => false,
		'ewww-image-optimizer' => false,
		'optimole-wp' => false,
		'imagify' => false,
		'robin-image-optimizer' => false,
		'resmushit-image-optimizer' => false,
		'warp-imagick' => false,
		'kraken-image-optimizer' => false,
		'optimus' => false,
		'tiny-compress-images' => false,
		'wp-compress' => false,
		'gumlet' => false,
		'flying-images' => false,
		'sirv' => false,
		'imghaste' => false,
		'siteground_image' => false, // Not text-domain, check to be made later
		'litespeed_image' => false, // Not text-domain, check to be made later
		'swift_image' => false, // Not text-domain, check to be made later
		'wp_optimize_image' => false, // Not text-domain, check to be made later
		'jetpack_images' => false, // Not text-domain, check to be made later
	);

	$good_cache_plugins = array(
		'swift-performance' => array( false, 'Swift Performance' ),
		'rocket' => array( false, 'WP Rocket' ),
		'litespeed-cache' => array( false, 'LiteSpeed Cache' ),
		'flying-press' => array( false, 'FlyingPress' ),
		'cache-enabler' => array( false, 'Cache Enabler' ),
	);

	$bad_cache_plugins = array(
		'breeze' => false,
		'w3-total-cache' => false,
		'wphb' => false,
		'nitropack' => false,
		'wp-fastest-cache' => false,
		'cache-enabler' => false,
		'wp-super-cache' => false,
		'a2-optimized' => false,
		'sg-cachepress' => false,
		'wpp' => false,
		'wp_optimize_cache' => false, // Not text-domain, check to be made later
	);

	$pagebuilders = array(
		'elementor' => false,
		'fl-builder' => false,
		'js_composer' => false,
		'thrive-cb' => false,
		'siteorigin-panels' => false,
		'brizy' => false,
		'visualcomposer' => false,
		'cornerstone' => false,
		'divi' => false,
	);
	if ( 'Divi' === $my_theme->get( 'Name' ) ) {
		$pagebuilders['divi'] = true;
	}

	write_log( 'Accelera Export - Step 2 completed' );


	/////**********************************************************************/////
	///// Preparations - HTTP headers from homepage
	/////**********************************************************************/////

	// Preparing curl
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, get_home_url() );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HEADER, 1 );
	curl_setopt( $ch, CURLOPT_ACCEPT_ENCODING, '' );
	if ( ( version_compare( curl_version()['version'], '7.33.0' ) >= 0 ) ) {
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0 );
	}
	curl_setopt( $ch, CURLOPT_FRESH_CONNECT, true );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
	$homeurl_headers_response = curl_exec( $ch ); // Executing curl to get the headers

	if ( ! curl_errno( $ch ) ) { // If there was no error or timeout

		// Return headers seperatly from the Response Body
		$home_url_header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		$home_url_headers_str = substr( $homeurl_headers_response, 0, $home_url_header_size ); // Headers in a single string
		$home_url_body = substr( $homeurl_headers_response, $home_url_header_size );

		// Making curl headers pretty
		$home_url_headers_str = strstr( $home_url_headers_str, "\r\n" ); // Removing response status + The seperator used in the Response Header is CRLF (Aka. \r\n)
		$home_url_headers_str = strtolower( $home_url_headers_str ); // Making string lowercase
		$home_url_headers = explode( "\r\n", $home_url_headers_str ); // Converting string to array key=>value (e.g. content-encoding => gzip)
		$home_url_headers = array_filter( $home_url_headers );
		foreach ( $home_url_headers as $temphead ) {
			$key_value = explode( ': ', $temphead );
			$end_array[ $key_value[0] ] = $key_value[1];
		}
		$home_url_headers = array_filter( $end_array ); // Final homepage headers

		// Finding if HTTP2 also while I'm at it (can't do with wp_remote_get cause it only supports HTTP/1.1)
		if ( 'cloudflare' === $home_url_headers['server'] ) { // If CF is active, we assume there's already HTTP2
			$http2_support = true;
		} elseif ( defined( 'CURL_VERSION_HTTP2' ) && ( curl_version()['features'] & CURL_VERSION_HTTP2 ) !== 0 ) { // If Curl supports HTTP2
			if ( false !== $homeurl_headers_response && strpos( $homeurl_headers_response, 'HTTP/2' ) === 0 ) {
				$http2_support = true;
			} else {
				$http2_support = false;
			}
		} elseif ( 'HTTP/2.0' === $_SERVER['SERVER_PROTOCOL'] ) { // If curl failed, try the PHP constant
			$http2_support = true;
		} else {
			$http2_support = false;
		}

		// Finding whether Cloudflare is configured through an integration
		$true_cloudflare = false;
		if ( 'cloudflare' === $home_url_headers['server'] && 'wp engine' !== $home_url_headers['x-powered-by'] ) { // WP Engine? They don't give access to CF dashboard
			$true_cloudflare = true;
		}
	}
	else throw new Exception( curl_error( $ch ) );

	curl_close( $ch );

	write_log( 'Accelera Export - Step 3 completed' );


	/////**********************************************************************/////
	///// Preparations - Plugin list + checking each plugin against Accelera list
	/////**********************************************************************/////
	$pluginlist = array();
	$plugins = get_plugins();

	if ( $plugins ) {
		$sno = 1;
		foreach ( $plugins as $key=>$plugin ) {

			// Checking image optimizers
			if ( 'shortpixel-adaptive-images' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$spai = true;
			}
			if ( 'shortpixel-image-optimiser' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$spio = true;
			}
			if ( 'autoptimize' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$ao = true;
				$autoptimizeImgopt = get_option( 'autoptimize_imgopt_settings', false ); // This is set by Autoptimize version >= 2.5.0
				if ( $autoptimizeImgopt ) {
					if ( isset( $autoptimizeImgopt['autoptimize_imgopt_checkbox_field_1'] ) && '1' == $autoptimizeImgopt['autoptimize_imgopt_checkbox_field_1'] ) {
						$ao_images = true;
					} else {
						$ao_images = false;
					}
				} else {
					$autoptimizeExtra = get_option( 'autoptimize_extra_settings', false ); // This is set by Autoptimize version <= 2.4.4
					if ( isset( $autoptimizeExtra['autoptimize_extra_checkbox_field_5'] ) && $autoptimizeExtra['autoptimize_extra_checkbox_field_5'] ) {
						$ao_images = true;
					} else {
						$ao_images = false;
					}
				}
			}
			if ( array_key_exists($plugin['TextDomain'], $bad_image_optimizers ) && is_plugin_active( $key ) ) {
				$bad_image_optimizers[ $plugin['TextDomain'] ] = true;
			}

			// Checking cache and other optimization plugins
			if ( array_key_exists( $plugin['TextDomain'], $bad_cache_plugins ) && is_plugin_active( $key ) ) {
				$bad_cache_plugins[ $plugin['TextDomain'] ] = true;
			}
			if ( array_key_exists( $plugin['TextDomain'], $good_cache_plugins ) && is_plugin_active( $key ) ) {
				$good_cache_plugins[ $plugin['TextDomain'] ][0] = true;
			}
			if ( 'perfmatters' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$pfmatters = true;
			}
			if ( 'wp-optimize' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$wpoptimize = true;
			}
			if ( 'heartbeat-control' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$heartbeatplugin = true;
			}
			if ( 'jetpack' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$jetpack = true;
			}
			if ( 'wp-asset-clean-up' === $plugin['TextDomain'] && is_plugin_active( $key ) ) {
				$assetcleanup = true;
			}

			// Checking page builders
			if ( array_key_exists( $plugin['TextDomain'], $pagebuilders ) && is_plugin_active( $key ) ) {
				$pagebuilders[ $plugin['TextDomain'] ] = true;
			}

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

	write_log( 'Accelera Export - Step 4 completed' );


	/////**********************************************************************/////
	///// Preparations - Gathering plugins options
	/////**********************************************************************/////
	$accelera_spaioptions = get_option( 'short_pixel_ai_options', false ); // SPAI
	$accelera_wprocketoptions = get_option( 'wp_rocket_settings', false ); // WP Rocket
	$accelera_swiftoptions = get_option( 'swift_performance_options', false ); // Swift Performance
	$accelera_wpoptimizeoptions = get_option( 'wpo_cache_config', false ); // WP-Optimize
	$accelera_jetpackmodules = get_option( 'jetpack_active_modules', false ); // Jetpack modules
	$accelera_assetcleanoptions = get_option( 'wpassetcleanup_settings', false ); // Asset CleanUp
	$accelera_pfmattersoptions = get_option( 'perfmatters_options', false ); // Perfmatters


	/////**********************************************************************/////
	///// Checks and populating $results_tasks, array of task results
	/////**********************************************************************/////
	$results_tasks = array();
	$results_tasks_auxiliar = array(); //2nd row on CSV
    $thedomain = preg_quote( parse_url( get_home_url() )['host'] ); // Website domain
	$acc_webserver = strtolower( explode( '/', $_SERVER['SERVER_SOFTWARE'] )[0] ); // Web server

	write_log( 'Accelera Export - Step 5 completed' );


	/*
	  _____                                          _   _           _          _   _
	 |_   _|                                        | | (_)         (_)        | | (_)
	   | |  _ __ ___   __ _  __ _  ___    ___  _ __ | |_ _ _ __ ___  _ ______ _| |_ _  ___  _ __
	   | | | '_ ` _ \ / _` |/ _` |/ _ \  / _ \| '_ \| __| | '_ ` _ \| |_  / _` | __| |/ _ \| '_ \
	  _| |_| | | | | | (_| | (_| |  __/ | (_) | |_) | |_| | | | | | | |/ / (_| | |_| | (_) | | | |
	 |_____|_| |_| |_|\__,_|\__, |\___|  \___/| .__/ \__|_|_| |_| |_|_/___\__,_|\__|_|\___/|_| |_|
	                         __/ |            | |
	                        |___/             |_|
	*/
	$temp_results_tasks_auxiliar = '';

	// First check for plugins that are not completely image optimizers
	if ( $bad_cache_plugins['sg-cachepress'] && get_option( 'siteground_optimizer_compression_level', false ) > 0 ) { // If SG + Compression level > 0
		$bad_image_optimizers['siteground_image'] = true;
	}
	if ( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.img_optm-auto', false ) > 0 ) { // If LSC + Autocompression > 0
		$bad_image_optimizers['litespeed_image'] = true;
	}
	if ( $good_cache_plugins['swift-performance'][0] && $accelera_swiftoptions['optimize-uploaded-images'] > 0 ) { // If Swift + Autocompression > 0
		$bad_image_optimizers['swift_image'] = true;
	}
	if ( $wpoptimize && get_option( 'wp-optimize-autosmush', false ) > 0 ) {
		$bad_image_optimizers['wp_optimize_image'] = true;
	}
	if ( $jetpack && in_array( 'photon', $accelera_jetpackmodules ) ) {
		$bad_image_optimizers['jetpack_images'] = true;
	}

	if ( in_array( true, $bad_image_optimizers ) ) {
        $results_tasks[] = 'E';
        foreach ( $bad_image_optimizers as $key => $value ) {
            if ( true === $value ) {
				$temp_results_tasks_auxiliar .= "$key\n";
			}
        }
    } elseif ( $spai && $spio && $ao_images ) {
		$results_tasks[] = 'F';
	} elseif ( ( $spai && $spio ) || ( $ao_images && $spio ) ) {
		$results_tasks[] = 'C';
	} elseif ( $spio ) {
		$results_tasks[] = 'A';
	} elseif ( $spai || $ao_images ) {
		$results_tasks[] = 'B';
	} else {
		$results_tasks[] = 'D';
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  ______ _ _                       _
	 |  ____(_) |                     | |
	 | |__   _| | ___    ___ __ _  ___| |__   ___
	 |  __| | | |/ _ \  / __/ _` |/ __| '_ \ / _ \
	 | |    | | |  __/ | (_| (_| | (__| | | |  __/
	 |_|    |_|_|\___|  \___\__,_|\___|_| |_|\___|
	*/
	$temp_results_tasks_auxiliar = '';

	// First check for plugins that are not completely cache plugins
	if ( $wpoptimize && $accelera_wpoptimizeoptions['enable_page_caching'] > 0 ) {
		$bad_cache_plugins['wp_optimize_cache'] = true;
	}

	if ( defined('WPE_CACHE_PLUGIN_BASE') ) { // WP Engine?
		$results_tasks[] = 'C';
		$temp_results_tasks_auxiliar = 'WP Engine';
	} elseif ( in_array( true, $bad_cache_plugins ) ) { // Bad cache plugin?
		$results_tasks[] = 'D';
		foreach ( $bad_cache_plugins as $key => $value ) {
            if ( true === $value ) {
				$temp_results_tasks_auxiliar .= "$key\n";
			}
        }
	} else { // Good cache plugin
		foreach ( $good_cache_plugins as $key => $value ) {
			if ( true === $value[0] ) {
				$temp_results_tasks_auxiliar = "$value[1]";
				$results_tasks[] = 'A';
				break; // As soon as we have a good plugin, it's enough
			}
		}
	}

	if ( '' == $temp_results_tasks_auxiliar ) {
		$results_tasks[] = 'B';
	} // The string will be empty if we didn't find any good or bad cache plugin

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  _______ _                                             _           _
	 |__   __| |                                           | |         (_)
	    | |  | |__   ___ _ __ ___   ___    __ _ _ __   __ _| |_   _ ___ _ ___
	    | |  | '_ \ / _ \ '_ ` _ \ / _ \  / _` | '_ \ / _` | | | | / __| / __|
	    | |  | | | |  __/ | | | | |  __/ | (_| | | | | (_| | | |_| \__ \ \__ \
	    |_|  |_| |_|\___|_| |_| |_|\___|  \__,_|_| |_|\__,_|_|\__, |___/_|___/
	                                                           __/ |
	                                                          |___/
	*/
	if ( in_array( true, $pagebuilders ) ) {
		$results_tasks[] = 'C';
	} elseif ( in_array( $my_theme->get( 'Name' ), $goodthemes ) ) {
		$results_tasks[] = 'A';
	} else {
		$results_tasks[] = 'B';
	}

	$results_tasks_auxiliar[] = $my_theme;

	write_log( 'Accelera Export - Step 6 completed' );

	/*
	   _____  _____ _____             _       _  __ _           _   _
	  / ____|/ ____/ ____|           (_)     (_)/ _(_)         | | (_)
	 | |    | (___| (___    _ __ ___  _ _ __  _| |_ _  ___ __ _| |_ _  ___  _ __
	 | |     \___ \\___ \  | '_ ` _ \| | '_ \| |  _| |/ __/ _` | __| |/ _ \| '_ \
	 | |____ ____) |___) | | | | | | | | | | | | | | | (_| (_| | |_| | (_) | | | |
	  \_____|_____/_____/  |_| |_| |_|_|_| |_|_|_| |_|\___\__,_|\__|_|\___/|_| |_|
	*/
	$temp_results_tasks_auxiliar = '';

	function how_many_unminified_css_files( $home_url_body, $thedomain, $lines_per_file ) {
		$unminimized_css_files = 0;

		// Find all local css files (except those .min.css). Regex looks for http(s)://subdomains.domain.com/whatever/blabla.(!min).css
		if ( preg_match_all( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*(?<!\.min)\.css)/", $home_url_body, $css_files ) ) {
			// Loop through all the local CSS files and count how many lines they have
			foreach( $css_files[0] as $css_file ) {
				$linecount = 0;
				if ( $handle = fopen( $css_file, 'r' ) ) { // Open the CSS file
					while ( !feof( $handle ) ) { // Count the number of lines
						$line = fgets( $handle );
						$linecount++;
					}
					fclose( $handle ); // Close the CSS file
					if ( $linecount > $lines_per_file ) { // If the CSS file has more lines than we deem appropriate, we'll consider it not minified
						$unminimized_css_files++;
					}
				}
			}
		}
		return $unminimized_css_files; // Return the number of files that we don't think are minified
	}

	if( $true_cloudflare ) {
		$results_tasks[] = 'D';
	} elseif ( //If SPAI, WP Rocket, AO or LiteSpeed are already minimizing
		( $spai && $accelera_spaioptions->settings->areas->parse_css_files ) ||
		( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['minify_css'] ) ||
		( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-css_min', false ) > 0 ) ||
		( $ao && get_option( 'autoptimize_css' ) == 'on' ) ||
		( $assetcleanup && strpos( $accelera_assetcleanoptions, '"minify_loaded_css":"1"' ) !== false ) ) {
		$results_tasks[] = 'C';
	} elseif ( how_many_unminified_css_files( $home_url_body, $thedomain, 4 ) > 0 ) { // Unminified
		if ( $ao ) {
			$results_tasks[] = 'B';
			$temp_results_tasks_auxiliar = 'Autoptimize';
		} elseif ( $good_cache_plugins['rocket'][0] ) {
			$results_tasks[] = 'B';
			$temp_results_tasks_auxiliar = 'WP Rocket';
		} elseif ( $good_cache_plugins['litespeed-cache'][0] ) {
			$results_tasks[] = 'B';
			$temp_results_tasks_auxiliar = 'LiteSpeed Cache';
		} elseif ( $good_cache_plugins['flying-press'][0] ) {
			$results_tasks[] = 'B';
			$temp_results_tasks_auxiliar = 'FlyingPress';
		} elseif ( $spai ) {
			$results_tasks[] = 'B';
			$temp_results_tasks_auxiliar = 'ShortPixel Adaptive Images';
		} elseif ( $assetcleanup ) {
			$results_tasks[] = 'B';
			$temp_results_tasks_auxiliar = 'Asset CleanUp';
		} else {
			$results_tasks[] = 'A';
		}
	} else { // Minified, either because there's nothing to minimize or because of a bad plugin
		if ( $spai || $good_cache_plugins['rocket'][0] || $ao || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) { // Ideally we would check if there's a bad plugin minimizing, to do
			$results_tasks[] = 'C';
		} else {
			$results_tasks[] = 'E';
		}
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	write_log( 'Accelera Export - Step 7 completed' );

	/*
	       _  _____             _       _  __ _           _   _
	      | |/ ____|           (_)     (_)/ _(_)         | | (_)
	      | | (___    _ __ ___  _ _ __  _| |_ _  ___ __ _| |_ _  ___  _ __
	  _   | |\___ \  | '_ ` _ \| | '_ \| |  _| |/ __/ _` | __| |/ _ \| '_ \
	 | |__| |____) | | | | | | | | | | | | | | | (_| (_| | |_| | (_) | | | |
	  \____/|_____/  |_| |_| |_|_|_| |_|_|_| |_|\___\__,_|\__|_|\___/|_| |_|
	*/
	$temp_results_tasks_auxiliar = '';

	function how_many_unminified_js_files( $home_url_body, $thedomain, $lines_per_file ) {
		$unminimized_js_files = 0;

		// Find all local js files (except those .min.js). Regex looks for http(s)://subdomains.domain.com/whatever/blabla.(!min).js
		if ( preg_match_all( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*(?<!\.min)\.js)/", $home_url_body, $js_files ) ) {
			// Loop through all the local js files and count how many lines they have
			foreach ( $js_files[0] as $js_file ) {
				$linecount = 0;
				if ( $handle = fopen( $js_file, 'r' ) ) { // Open the js file
					while ( !feof( $handle ) ) { // Count the number of lines
						$line = fgets( $handle );
						$linecount++;
					}
					fclose( $handle ); // Close the js file
					if ( $linecount > $lines_per_file ) { // If the js file has more lines than we deem appropriate, we'll consider it not minified
						$unminimized_js_files++;
					}
				}
			}
		}
		return $unminimized_js_files; // Return the number of files that we don't think are minified
	}

	if ( $true_cloudflare ) {
		$results_tasks[] = 'D';
	} elseif ( // If WP Rocket, AO or LiteSpeed are already minimizing
		( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['minify_js'] ) ||
		( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_min', false ) > 0 ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' ) ||
		( $assetcleanup && strpos( $accelera_assetcleanoptions, '"minify_loaded_js":"1"' ) !== false ) ) {
		$results_tasks[] = 'C';
	} elseif ( how_many_unminified_js_files( $home_url_body, $thedomain, 4 ) > 0 ) { // Unminified
		if ( $ao || $good_cache_plugins['rocket'][0] || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) {
			$results_tasks[] = 'B';
		} else {
			$results_tasks[] = 'A';
		}
	}
	else { // Minified, either because there's nothing to minimize or because of a bad plugin
		if ( $ao || $good_cache_plugins['rocket'][0] || $good_cache_plugins['litespeed-cache'][0] || $good_cache_plugins['flying-press'][0] || $assetcleanup ) { // Ideally we would check if there's a bad plugin minimizing, to do
			$results_tasks[] = 'C';
		} else {
			$results_tasks[] = 'E';
		}
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  ____            _   _ _     _______     _                                                     _
	 |  _ \          | | | (_)   / / ____|   (_)                                                   (_)
	 | |_) |_ __ ___ | |_| |_   / / |  __ _____ _ __     ___ ___  _ __ ___  _ __  _ __ ___  ___ ___ _  ___  _ __
	 |  _ <| '__/ _ \| __| | | / /| | |_ |_  / | '_ \   / __/ _ \| '_ ` _ \| '_ \| '__/ _ \/ __/ __| |/ _ \| '_ \
	 | |_) | | | (_) | |_| | |/ / | |__| |/ /| | |_) | | (_| (_) | | | | | | |_) | | |  __/\__ \__ \ | (_) | | | |
	 |____/|_|  \___/ \__|_|_/_/   \_____/___|_| .__/   \___\___/|_| |_| |_| .__/|_|  \___||___/___/_|\___/|_| |_|
	                                           | |                         | |
	                                           |_|                         |_|
	*/
	$temp_results_tasks_auxiliar = '';

	if ( 'br' === $home_url_headers['content-encoding'] ) {
		$results_tasks[] = 'D';
	} else {
		$the_curl_version = curl_version();
		if ( ( version_compare( $the_curl_version['version'], '7.57.0' ) >= 0 ) && ( $the_curl_version['features'] & constant('CURL_VERSION_BROTLI' ) ) ) { // First we check that CURL supports brotli and it is active
			if ( 'gzip' === $home_url_headers['content-encoding'] && $true_cloudflare ) { // If CF active and server has only Gzip
				$results_tasks[] = 'C';
			} elseif ( 'gzip' === $home_url_headers['content-encoding'] ) {
				$results_tasks[] = 'B';
			} else {
				$results_tasks[] = 'A';
			}
		} else {
			$results_tasks[] = 'MAN_CH'; // It's still possible that we are serving Brotli, but we can't know...
		}
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  _    _           _       _         _____  _    _ _____                       _
	 | |  | |         | |     | |       |  __ \| |  | |  __ \                     (_)
	 | |  | |_ __   __| | __ _| |_ ___  | |__) | |__| | |__) | __   _____ _ __ ___ _  ___  _ __
	 | |  | | '_ \ / _` |/ _` | __/ _ \ |  ___/|  __  |  ___/  \ \ / / _ \ '__/ __| |/ _ \| '_ \
	 | |__| | |_) | (_| | (_| | ||  __/ | |    | |  | | |       \ V /  __/ |  \__ \ | (_) | | | |
	  \____/| .__/ \__,_|\__,_|\__\___| |_|    |_|  |_|_|        \_/ \___|_|  |___/_|\___/|_| |_|
	        | |
	        |_|
	*/
	$temp_results_tasks_auxiliar = '';

	if ( version_compare( PHP_VERSION, '7.4.0', '>=' ) ) {
		$results_tasks[] = 'A';
	} else {
		$temp_results_tasks_auxiliar = PHP_VERSION;
		$results_tasks[] = 'B';
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	   _____  _____ _____                       _     _
	  / ____|/ ____/ ____|                     | |   (_)
	 | |    | (___| (___     ___ ___  _ __ ___ | |__  _ _ __   ___
	 | |     \___ \\___ \   / __/ _ \| '_ ` _ \| '_ \| | '_ \ / _ \
	 | |____ ____) |___) | | (_| (_) | | | | | | |_) | | | | |  __/
	  \_____|_____/_____/   \___\___/|_| |_| |_|_.__/|_|_| |_|\___|
	*/
	$temp_results_tasks_auxiliar = '';

	if ( $http2_support ) {
		$results_tasks[] = 'B';
		$results_tasks[] = 'B';
	} else {
		$results_tasks[] = 'A';
		$results_tasks[] = 'A';
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;
	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	write_log( 'Accelera Export - Step 8 completed' );


	/*
	  _                                           _                                                      _     _
	 | |                                         | |                                                    | |   (_)
	 | |     _____   _____ _ __ __ _  __ _  ___  | |__  _ __ _____      _____  ___ _ __    ___ __ _  ___| |__  _ _ __   __ _
	 | |    / _ \ \ / / _ \ '__/ _` |/ _` |/ _ \ | '_ \| '__/ _ \ \ /\ / / __|/ _ \ '__|  / __/ _` |/ __| '_ \| | '_ \ / _` |
	 | |___|  __/\ V /  __/ | | (_| | (_| |  __/ | |_) | | | (_) \ V  V /\__ \  __/ |    | (_| (_| | (__| | | | | | | | (_| |
	 |______\___| \_/ \___|_|  \__,_|\__, |\___| |_.__/|_|  \___/ \_/\_/ |___/\___|_|     \___\__,_|\___|_| |_|_|_| |_|\__, |
	                                  __/ |                                                                             __/ |
	                                 |___/                                                                             |___/
	*/
	$temp_results_tasks_auxiliar = '';

	// Get a sample of CSS
	function return_first_css() {
		return get_template_directory_uri() . '/style.css'; // We return the style.css of the theme, always present
	}

	// Get a sample of JS
	function return_first_js( $home_url_body, $thedomain ) {
		preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.js)/i", $home_url_body, $js_file ); // Find and return first local js file
		if ( ! empty( $js_file ) ) {
			return $js_file;
		} else {
			return array( 'vacio' );
		}
	}

	//Get a sample of IMG
	function return_first_img( $home_url_body, $thedomain ) {
		preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.(jpg|jpeg|png|gif))/i", $home_url_body, $img_file ); // Find and return first local img file
		if ( !empty( $img_file ) ) {
			return $img_file;
		} else {
			return array( 'vacio' );
		}
	}

	$first_assets = array( return_first_css(), return_first_js( $home_url_body, $thedomain )[0], return_first_img( $home_url_body, $thedomain )[0] ); // Getting an array of the first asset of each type
	$a = 0; //Counter of browser cache too low

	// Let's check the actual cache-control for each asset
	foreach ( $first_assets as $asset_url ) {
		if ( 'vacio' !== $asset_url ) {
			$response_asset = wp_remote_get( $asset_url );
			$cachectrl = wp_remote_retrieve_header( $response_asset, 'cache-control' );
			if ( empty( $cachectrl ) ) { // If cache-control is not set, needs to be set
				$a++;
			} else {
				if ( preg_match( "/[0-9]+/", $cachectrl, $cache_control_value ) ) { // We get the value of cache-control, if it exists
					if ( $cache_control_value[0] < 10368000 ) {
						$a++; // If too low, mark it
					};
				} else {
					$a++; // If cache-control is not set, needs to be set
				}
			}
		}
	}

	if ( $a > 0 && 'nginx' !== $acc_webserver ) {
		$results_tasks[] = 'B';
	} elseif ( $a > 0 && 'nginx' === $acc_webserver ) {
		$results_tasks[] = 'C';
	} else {
		$results_tasks[] = 'A';
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	write_log( 'Accelera Export - Step 9 completed' );

	/*
	  _____        __                                 _                      __        _  _____
	 |  __ \      / _|                               (_)                    / _|      | |/ ____|
	 | |  | | ___| |_ ___ _ __   _ __   __ _ _ __ ___ _ _ __   __ _    ___ | |_       | | (___
	 | |  | |/ _ \  _/ _ \ '__| | '_ \ / _` | '__/ __| | '_ \ / _` |  / _ \|  _|  _   | |\___ \
	 | |__| |  __/ ||  __/ |    | |_) | (_| | |  \__ \ | | | | (_| | | (_) | |   | |__| |____) |
	 |_____/ \___|_| \___|_|    | .__/ \__,_|_|  |___/_|_| |_|\__, |  \___/|_|    \____/|_____/
	                            | |                            __/ |
	                            |_|                           |___/
	*/
	$temp_results_tasks_auxiliar = '';

	function arejs_deferred( $home_url_body, $thedomain ) {
		$deferred = 0; // Counter of deferred
		$deferstring = "/defer='defer'|defer=\"defer\"/";

		// Find all local js files
		preg_match_all( "/<script.*{$thedomain}.*\.js.*<\/script>/i", $home_url_body, $js_files );

		// Loop through all the local js files and check if they are deferred
		foreach ( $js_files[0] as $js_file ) {
            if ( preg_match( $deferstring, $js_file ) ) {
				$deferred++;
			}
		}
		if ( count( $js_files[0] ) - $deferred <= 3 ) {
			return 'B'; // Return if files are deferred
		} else {
			return 'A';
		}
	}

	// If WP Rocket/AO/LiteSpeed are already doing that, all good
	if ( ( $good_cache_plugins['rocket'][0] && $accelera_wprocketoptions['defer_all_js'] ) ||
		( $good_cache_plugins['litespeed-cache'][0] && get_option( 'litespeed.conf.optm-js_defer', false ) > 0 ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_defer_not_aggregate' ) == 'on' ) ||
		( $ao && get_option( 'autoptimize_js' ) == 'on' && get_option( 'autoptimize_js_aggregate' ) == 'on' && ! get_option( 'autoptimize_js_forcehead' ) ) ) {
			$results_tasks[] = 'B';
	} else {
		$results_tasks[] = arejs_deferred( $home_url_body, $thedomain );
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	   _____            _             _   _    _                 _   _                _
	  / ____|          | |           | | | |  | |               | | | |              | |
	 | |     ___  _ __ | |_ _ __ ___ | | | |__| | ___  __ _ _ __| |_| |__   ___  __ _| |_
	 | |    / _ \| '_ \| __| '__/ _ \| | |  __  |/ _ \/ _` | '__| __| '_ \ / _ \/ _` | __|
	 | |___| (_) | | | | |_| | | (_) | | | |  | |  __/ (_| | |  | |_| |_) |  __/ (_| | |_
	  \_____\___/|_| |_|\__|_|  \___/|_| |_|  |_|\___|\__,_|_|   \__|_.__/ \___|\__,_|\__|
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

	/*
	   _____ _                    _                    _                        __                _         _ _
	  / ____| |                  | |                  | |                      / _|              | |       (_) |
	 | |    | | ___  __ _ _ __   | |__   ___  __ _  __| | ___ _ __ ___    ___ | |_  __      _____| |__  ___ _| |_ ___
	 | |    | |/ _ \/ _` | '_ \  | '_ \ / _ \/ _` |/ _` |/ _ \ '__/ __|  / _ \|  _| \ \ /\ / / _ \ '_ \/ __| | __/ _ \
	 | |____| |  __/ (_| | | | | | | | |  __/ (_| | (_| |  __/ |  \__ \ | (_) | |    \ V  V /  __/ |_) \__ \ | ||  __/
	  \_____|_|\___|\__,_|_| |_| |_| |_|\___|\__,_|\__,_|\___|_|  |___/  \___/|_|     \_/\_/ \___|_.__/|___/_|\__\___|
	*/
	$temp_results_tasks_auxiliar = '';

	function are_headers_clean( $home_url_body, &$temp_results_tasks_auxiliar, $pfmatters, $assetcleanup ) {
		$cleanstring = "/<meta name=\"generator\" content=\"WordPress|<link rel=\"wlwmanifest/"; // If we see that the wp version is removed or wlwmanifest is not there, means wp headers have been tweaked, means done.
		if ( preg_match( $cleanstring, $home_url_body ) ) {
			if ( $pfmatters ) {
				$temp_results_tasks_auxiliar = 'Perfmatters';
				return 'B';
			} elseif ( $assetcleanup ) {
				$temp_results_tasks_auxiliar = 'Asset CleanUp';
				return 'B';
			} else {
				return 'C';
			}
		} else {
			return 'A';
		}
	}
	$results_tasks[] = are_headers_clean( $home_url_body, $temp_results_tasks_auxiliar, $pfmatters, $assetcleanup );

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  _____                                                 _ _        _   _____    _____                    _ _      __ _ _ _
	 |  __ \                                               (_) |      | | |  __ \  |  __ \                  (_) |    / _(_) | |
	 | |__) |___ _ __ ___   _____   _____    ___ __ _ _ __  _| |_ __ _| | | |__) | | |  | | __ _ _ __   __ _ _| |_  | |_ _| | |_ ___ _ __
	 |  _  // _ \ '_ ` _ \ / _ \ \ / / _ \  / __/ _` | '_ \| | __/ _` | | |  ___/  | |  | |/ _` | '_ \ / _` | | __| |  _| | | __/ _ \ '__|
	 | | \ \  __/ | | | | | (_) \ V /  __/ | (_| (_| | |_) | | || (_| | | | |      | |__| | (_| | | | | (_| | | |_  | | | | | ||  __/ |
	 |_|  \_\___|_| |_| |_|\___/ \_/ \___|  \___\__,_| .__/|_|\__\__,_|_| |_|      |_____/ \__,_|_| |_|\__, |_|\__| |_| |_|_|\__\___|_|
	                                                 | |                                                __/ |
	                                                 |_|                                               |___/
	*/
	$temp_results_tasks_auxiliar = '';

    if ( has_filter( 'the_content', 'capital_P_dangit' ) ) {
		$results_tasks[] = 'B'; // Not done
	} else {
		$results_tasks[] = 'A'; // Done
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	   _____      _  __         _             _                _              _ _           _     _          _
	  / ____|    | |/ _|       (_)           | |              | |            | (_)         | |   | |        | |
	 | (___   ___| | |_   _ __  _ _ __   __ _| |__   __ _  ___| | _____    __| |_ ___  __ _| |__ | | ___  __| |
	  \___ \ / _ \ |  _| | '_ \| | '_ \ / _` | '_ \ / _` |/ __| |/ / __|  / _` | / __|/ _` | '_ \| |/ _ \/ _` |
	  ____) |  __/ | |   | |_) | | | | | (_| | |_) | (_| | (__|   <\__ \ | (_| | \__ \ (_| | |_) | |  __/ (_| |
	 |_____/ \___|_|_|   | .__/|_|_| |_|\__, |_.__/ \__,_|\___|_|\_\___/  \__,_|_|___/\__,_|_.__/|_|\___|\__,_|
	                     | |             __/ |
	                     |_|            |___/
	*/
	$temp_results_tasks_auxiliar = '';

	if ( ! get_option( 'default_pingback_flag' ) ) {
		$results_tasks[] = 'A';
	} else {
		$results_tasks[] = 'B'; // Unknown
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  ______      _                        _                                                             _   _           _          _   _
	 |  ____|    | |                      | |                                                           | | (_)         (_)        | | (_)
	 | |__  __  _| |_ ___ _ __ _ __   __ _| |  _ __ ___  ___  ___  _   _ _ __ ___ ___  ___    ___  _ __ | |_ _ _ __ ___  _ ______ _| |_ _  ___  _ __
	 |  __| \ \/ / __/ _ \ '__| '_ \ / _` | | | '__/ _ \/ __|/ _ \| | | | '__/ __/ _ \/ __|  / _ \| '_ \| __| | '_ ` _ \| |_  / _` | __| |/ _ \| '_ \
	 | |____ >  <| ||  __/ |  | | | | (_| | | | | |  __/\__ \ (_) | |_| | | | (_|  __/\__ \ | (_) | |_) | |_| | | | | | | |/ / (_| | |_| | (_) | | | |
	 |______/_/\_\\__\___|_|  |_| |_|\__,_|_| |_|  \___||___/\___/ \__,_|_|  \___\___||___/  \___/| .__/ \__|_|_| |_| |_|_/___\__,_|\__|_|\___/|_| |_|
	                                                                                              | |
	                                                                                              |_|
	*/
	$temp_results_tasks_auxiliar = '';

	$gf = $ga = $wpr_delay = false;

	// Check if we see fonts in the code
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

	// Check if we see analytics in the code
	function isthere_ga( $home_url_body ) {
		if ( preg_match( "/<script.*googletagmanager\.com\/gtag\/js\?id=[UAG]+-[0-9A-Z-]+(\"|')/", $home_url_body ) ) {
			return true;
		} else {
			return false;
		}
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

	if ( $wpr_delay || $flyingscripts ) {
		if ( $gf && $ga ) {
			$results_tasks[] = 'D';
		} elseif ( $gf ) {
			$results_tasks[] = 'C';
		} elseif ( $ga ) {
			$results_tasks[] = 'B';
		} else {
			$results_tasks[] = 'A';
		}
	} elseif ( $good_cache_plugins['rocket'][0] ) {
		if ( $gf && $ga ) {
			$results_tasks[] = 'K';
		} elseif ( $gf ) {
			$results_tasks[] = 'I';
		} elseif ( $ga ) {
			$results_tasks[] = 'E';
		} else {
			$results_tasks[] = 'L';
		}
	} else {
		if ( $gf && $ga ) {
			$results_tasks[] = 'F';
		} elseif ( $gf ) {
			$results_tasks[] = 'G';
		} elseif ( $ga ) {
			$results_tasks[] = 'J';
		} else {
			$results_tasks[] = 'H';
		}
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	write_log( 'Accelera Export - Step 10 completed' );

	/*
	  _____                                          _     _                    _                        _       _ _
	 |  __ \                                        | |   | |                  | |                      | |     (_) |
	 | |__) | __ ___  ___ ___  _ __  _ __   ___  ___| |_  | |_ ___     _____  _| |_ ___ _ __ _ __   __ _| |  ___ _| |_ ___  ___
	 |  ___/ '__/ _ \/ __/ _ \| '_ \| '_ \ / _ \/ __| __| | __/ _ \   / _ \ \/ / __/ _ \ '__| '_ \ / _` | | / __| | __/ _ \/ __|
	 | |   | | |  __/ (_| (_) | | | | | | |  __/ (__| |_  | || (_) | |  __/>  <| ||  __/ |  | | | | (_| | | \__ \ | ||  __/\__ \
	 |_|   |_|  \___|\___\___/|_| |_|_| |_|\___|\___|\__|  \__\___/   \___/_/\_\\__\___|_|  |_| |_|\__,_|_| |___/_|\__\___||___/
	*/
	$temp_results_tasks_auxiliar = '';

	function preconnects_count( $home_url_body, $ao, $pfmatters, &$temp_results_tasks_auxiliar ) {
		$preconnect = 0; // Counter of preconnects
		$preconnectstr = "/rel=preconnect|rel='preconnect'|rel=\"preconnect\"/";
		preg_match_all( "/<link.*>/i", $home_url_body, $linklines ); // Get all <link

		// Loop through all <link and check if they are preconnected
		foreach( $linklines[0] as $linkgs ) {
			if ( preg_match($preconnectstr, $linkgs) ) {
				$preconnect++;
			}
		}

		if ( $preconnect > 3 ) {
			return 'D';
		} elseif ( $preconnect > 0 ) {
			return 'A'; // If >0 and <=3 preconnects, all good
		} else {
			if ( $ao ) {
				$temp_results_tasks_auxiliar = 'Autoptimize';
				return 'B';
			} elseif ( $pfmatters ) {
				$temp_results_tasks_auxiliar = 'Perfmatters';
				return 'B';
			} else {
				return 'C';
			}
		}
	}
	$results_tasks[] = preconnects_count( $home_url_body, $ao, $pfmatters, $temp_results_tasks_auxiliar );

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	              _                         _           _
	     /\      | |                       | |         (_)
	    /  \   __| |___    __ _ _ __   __ _| |_   _ ___ _ ___
	   / /\ \ / _` / __|  / _` | '_ \ / _` | | | | / __| / __|
	  / ____ \ (_| \__ \ | (_| | | | | (_| | | |_| \__ \ \__ \
	 /_/    \_\__,_|___/  \__,_|_| |_|\__,_|_|\__, |___/_|___/
	                                           __/ |
	                                          |___/
	*/
	$temp_results_tasks_auxiliar = '';

	function are_there_ads( $home_url_body ) {
		$cleanstring = "/pagead2\.googlesyndication\.com\/pagead\/js\/adsbygoogle\.js|amazon-adsystem\.com|securepubads\.g.doubleclick\.net|ads\.adthrive\.com/"; // If we see that the Ads JS is loaded, means there are ads
		if ( preg_match( $cleanstring, $home_url_body ) ) {
			return 'A';
		} else {
			return 'B';
		}
	}
	$results_tasks[] = are_there_ads( $home_url_body );

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  _____  _           _     _       __          _______         _____
	 |  __ \(_)         | |   | |      \ \        / /  __ \       / ____|
	 | |  | |_ ___  __ _| |__ | | ___   \ \  /\  / /| |__) |_____| |     _ __ ___  _ __
	 | |  | | / __|/ _` | '_ \| |/ _ \   \ \/  \/ / |  ___/______| |    | '__/ _ \| '_ \
	 | |__| | \__ \ (_| | |_) | |  __/    \  /\  /  | |          | |____| | | (_) | | | |
	 |_____/|_|___/\__,_|_.__/|_|\___|     \/  \/   |_|           \_____|_|  \___/|_| |_|
	*/
	$temp_results_tasks_auxiliar = '';

	if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) { // Just check if the constant is defined
		$results_tasks[] = 'B';
	} else {
		$results_tasks[] = 'A';
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  _    _            _    _ _______ _______ _____ ___
	 | |  | |          | |  | |__   __|__   __|  __ \__ \
	 | |  | |___  ___  | |__| |  | |     | |  | |__) | ) |
	 | |  | / __|/ _ \ |  __  |  | |     | |  |  ___/ / /
	 | |__| \__ \  __/ | |  | |  | |     | |  | |    / /_
	  \____/|___/\___| |_|  |_|  |_|     |_|  |_|   |____|
	*/
	$temp_results_tasks_auxiliar = '';

	if ( $http2_support ) {
		$results_tasks[] = 'A';
	} else {
		$results_tasks[] = 'B';
	}

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	/*
	  _____  _   _  _____                   __     _       _    ___
	 |  __ \| \ | |/ ____|                 / _|   | |     | |  |__ \
	 | |  | |  \| | (___    _ __  _ __ ___| |_ ___| |_ ___| |__   ) |
	 | |  | | . ` |\___ \  | '_ \| '__/ _ \  _/ _ \ __/ __| '_ \ / /
	 | |__| | |\  |____) | | |_) | | |  __/ ||  __/ || (__| | | |_|
	 |_____/|_| \_|_____/  | .__/|_|  \___|_| \___|\__\___|_| |_(_)
	                       | |
	                       |_|
	*/
	$temp_results_tasks_auxiliar = '';

	function dnsprefetch_count( $home_url_body, $good_cache_plugins, &$temp_results_tasks_auxiliar ) {
		$dnsprefetch = 0; // Counter of dns-prefetch
		$dnsprefetchtr = "/rel='dns-prefetch'|rel=\"dns-prefetch\"/";
		preg_match_all( "/<link.*>/i", $home_url_body, $linklines );

		foreach( $linklines[0] as $linkgs ) {
			if ( preg_match( $dnsprefetchtr, $linkgs ) ) {
				$dnsprefetch++;
			}
		}

		if ( $dnsprefetch <= 2 ) { // If only <=2 dns-prefetch, needs to be done
			if ( $good_cache_plugins['rocket'][0] ) {
				$temp_results_tasks_auxiliar = 'WP Rocket';
				return 'B';
			} elseif ( $good_cache_plugins['swift-performance'][0] ) {
				$temp_results_tasks_auxiliar = 'Swift Performance';
				return 'B';
			} elseif ( $good_cache_plugins['litespeed-cache'][0] ) {
				$temp_results_tasks_auxiliar = 'LiteSpeed Cache';
				return 'B';
			} else {
				return 'C';
			}
		} else {
			return 'A';
		}
	}
	$results_tasks[] = dnsprefetch_count( $home_url_body, $good_cache_plugins, $temp_results_tasks_auxiliar );

	$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

	write_log( 'Accelera Export - Step 11 completed' );


	/////**********************************************************************/////
	///// Extra server info
	/////**********************************************************************/////
    $extra_server_info = array();
    if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
        $extra_server_info[] = $_SERVER['SERVER_SOFTWARE'];
    }

	/////**********************************************************************/////
	///// Database export - helper functions
	/////**********************************************************************/////

	// Percentage calculation function
	function format_percentage( $current, $total ) {
		if ( is_numeric( $current ) || is_numeric( $total ) ) {
			return ( $total > 0 ? round( ( $current / $total ) * 100, 2 ) : 0 ) . '%';
		} else {
			return $total;
		}
	}

	// Check if Persisent Object Cache is required
	function accelera_object_cache_check() {
		$result = 'A';

		if ( wp_using_ext_object_cache() ) {
			return $result;
		}

		if ( ! acc_should_suggest_persistent_object_cache() ) {
			$result = 'C';
			return $result;
		}

		return 'B';
	}

	// Determine whether to suggest using a persistent object cache
	function acc_should_suggest_persistent_object_cache() {
		global $wpdb;

		if ( is_multisite() ) {
			return true;
		}

		// Thresholds used to determine whether to suggest the use of a persistent object cache.
		$thresholds = array(
				'alloptions_count' => 500,
				'alloptions_bytes' => 100000,
				'comments_count'   => 1000,
				'options_count'    => 1000,
				'posts_count'      => 1000,
				'terms_count'      => 1000,
				'users_count'      => 1000,
		);

		$alloptions = wp_load_alloptions();

		if ( $thresholds['alloptions_count'] < count( $alloptions ) ) {
			return true;
		}
		if ( $thresholds['alloptions_bytes'] < strlen( serialize( $alloptions ) ) ) {
			return true;
		}

		$table_names = implode( "','", array( $wpdb->comments, $wpdb->options, $wpdb->posts, $wpdb->terms, $wpdb->users ) );

		// With InnoDB the `TABLE_ROWS` are estimates, which are accurate enough and faster to retrieve than individual `COUNT()` queries.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT TABLE_NAME AS 'table', TABLE_ROWS AS 'rows', SUM(data_length + index_length) as 'bytes'
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME IN ('$table_names')
				GROUP BY TABLE_NAME;",
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				DB_NAME
			),
			OBJECT_K
		);

		$threshold_map = array(
			'comments_count' => $wpdb->comments,
			'options_count'  => $wpdb->options,
			'posts_count'    => $wpdb->posts,
			'terms_count'    => $wpdb->terms,
			'users_count'    => $wpdb->users,
		);

		foreach ( $threshold_map as $threshold => $table ) {
			if ( $thresholds[ $threshold ] <= $results[ $table ]->rows ) {
				return true;
			}
		}

		return false;
	}

	// Building and executing queries
	function acc_queries( &$variables_db ) {

		global $wpdb;

		// Counting db totals
		$variables_db['posts_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts" );
		$variables_db['postmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta" );
		$variables_db['commentmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->commentmeta" );
		$variables_db['usersmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta" );
		$variables_db['termmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->termmeta" );
		$variables_db['termrelation_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->term_relationships" );
		$variables_db['options_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options" );

		// Particular items totals
		$variables_db['revisions'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
		$variables_db['orphaned_postmeta'] = $wpdb->get_var( "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)" );
		$variables_db['orphaned_commentmeta'] = $wpdb->get_var( "SELECT COUNT(meta_id) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)" );
		$variables_db['orphaned_usermeta'] = $wpdb->get_var( "SELECT COUNT(umeta_id) FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT ID FROM $wpdb->users)" );
		$variables_db['orphaned_termmeta'] = $wpdb->get_var( "SELECT COUNT(meta_id) FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms)" );
		$variables_db['oembed'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE meta_key LIKE(%s)", '%_oembed_%' ) );
		$variables_db['acc_objectcache'] = accelera_object_cache_check();
		$variables_db['orphaned_termrelation'] = $wpdb->get_var( "SELECT COUNT(object_id) FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy != 'link_category' AND tr.object_id NOT IN (SELECT ID FROM $wpdb->posts)" ); // phpcs:ignore

		// Duplicated postmeta
		$query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(meta_id) AS count FROM $wpdb->postmeta GROUP BY post_id, meta_key, meta_value HAVING count > %d", 1 ) );
		if ( is_array( $query ) ) {
			$variables_db['duplicated_postmeta'] = array_sum( array_map( 'intval', $query ) );
		}

		// Duplicated commentmeta
		$query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(meta_id) AS count FROM $wpdb->commentmeta GROUP BY comment_id, meta_key, meta_value HAVING count > %d", 1 ) );
		if ( is_array( $query ) ) {
			$variables_db['duplicated_commentmeta'] = array_sum( array_map( 'intval', $query ) );
		}

		// Duplicated usermeta
		$query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(umeta_id) AS count FROM $wpdb->usermeta GROUP BY user_id, meta_key, meta_value HAVING count > %d", 1 ) );
		if ( is_array( $query ) ) {
			$variables_db['duplicated_usermeta'] = array_sum( array_map( 'intval', $query ) );
		}

		// Duplicated termmeta
		$query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(meta_id) AS count FROM $wpdb->termmeta GROUP BY term_id, meta_key, meta_value HAVING count > %d", 1 ) );
		if ( is_array( $query ) ) {
			$variables_db['duplicated_termmeta'] = array_sum( array_map( 'intval', $query ) );
		}

		// Autoloads size (in KB)
		$autoloads_result = $wpdb->get_results("SELECT SUM(LENGTH(option_value)/1024.0) as autoload_size FROM $wpdb->options WHERE autoload='yes'");
		foreach( $autoloads_result as $object=>$uno ){
			$variables_db['autoloads'] = round( $uno->autoload_size );
		}
	}


	/////**********************************************************************/////
	///// Database export - building arrays to put in CSV
	/////**********************************************************************/////
	$dbheaders = array( 'Details', 'Count', '% of' );
	$dbtitles = array( 'Revisions', 'Orphaned Post Meta', 'Duplicated Post Meta', 'oEmbed Caches In Post Meta', 'Orphaned Comment Meta', 'Duplicated Comment Meta', 'Orphaned User Meta', 'Duplicated User Meta', 'Orphaned Term Meta', 'Duplicated Term Meta', 'Orphaned Term Relationship', 'Object Cache', 'Optimize Tables', 'Autoloads' );
	$variables_db = array(
		'revisions' => '-',
		'orphaned_postmeta' => '-',
		'duplicated_postmeta' => '-',
		'oembed' => '-',
		'orphaned_commentmeta' => '-',
		'duplicated_commentmeta' => '-',
		'orphaned_usermeta' => '-',
		'duplicated_usermeta' => '-',
		'orphaned_termmeta' => '-',
		'duplicated_termmeta' => '-',
		'orphaned_termrelation' => '-',
		'acc_objectcache' => '-',
		'autoloads' => '-',
		'posts_total' => '-',
		'postmeta_total' => '-',
		'commentmeta_total' => '-',
		'usersmeta_total' => '-',
		'termmeta_total' => '-',
		'termrelation_total' => '-',
	); //Default as '-' in case we skip db check

	// Only execute the queries if we chose to not skip the db checks
	if ( isset( $_REQUEST['skip'] ) ) {
		if ( $_REQUEST['skip'] !== 'db' ) {
			acc_queries( $variables_db );
		}
	} else {
		acc_queries( $variables_db );
	}

	//Building arrays to parse later in the CSV
	$particular_totals = array(
		$variables_db['revisions'],
		$variables_db['orphaned_postmeta'],
		$variables_db['duplicated_postmeta'],
		$variables_db['oembed'],
		$variables_db['orphaned_commentmeta'],
		$variables_db['duplicated_commentmeta'],
		$variables_db['orphaned_usermeta'],
		$variables_db['duplicated_usermeta'],
		$variables_db['orphaned_termmeta'],
		$variables_db['duplicated_termmeta'],
		$variables_db['orphaned_termrelation'],
		$variables_db['acc_objectcache'],
		'To do',
		$variables_db['autoloads'],
	);
	$dbtotals = array(
		$variables_db['posts_total'],
		$variables_db['postmeta_total'],
		$variables_db['postmeta_total'],
		$variables_db['postmeta_total'],
		$variables_db['commentmeta_total'],
		$variables_db['commentmeta_total'],
		$variables_db['usersmeta_total'],
		$variables_db['usersmeta_total'],
		$variables_db['termmeta_total'],
		$variables_db['termmeta_total'],
		$variables_db['termrelation_total'],
		'-',
		'-',
		'-',
	);


	/////**********************************************************************/////
	///// Time to write to file
	/////**********************************************************************/////
	$filename = time() . '_' . 'accelera-export.csv';
	//$filename = 'test.csv';
	$upload_dir = wp_upload_dir();
	$file = fopen( $upload_dir['basedir'] . '/' . $filename, 'w');

	// Writing task headers
	$taskheaders = array( 'Optimize images', 'File cache', 'Theme analysis', 'Minify CSS', 'Minify Javascript', 'Enable GZip compression', 'Update PHP version', 'Combine CSS', 'Combine JS', 'Leverage browser caching', 'Defer parsing of JS', 'Control Heartbeat API interval', 'Clean headers of website', 'Remove Capital P Dangit filter', 'Disable Self Pingbacks', 'External resources optimization', 'Preconnect to external sites', 'Ads analysis', 'Disable WP-Cron', 'Use HTTP/2', 'Apply DNS Prefetch' );
	fputcsv( $file, $taskheaders );

	// Writing task results
	fputcsv( $file, $results_tasks );
	fputcsv( $file, $results_tasks_auxiliar );

	// New line
	fputcsv( $file, array( '' ) );

	// Writing extra server info
	fputcsv( $file, $extra_server_info );

	// New line
	fputcsv( $file, array( '' ) );

	// Writing DB headers
	fputcsv( $file, $dbheaders );

	// Writing DB results
	for (  $i = 0; $i < count( $dbtitles ); $i++ ) {
		if ( ! is_numeric( $dbtotals[ $i ] ) ) {
			fputcsv( $file, array( $dbtitles[ $i ], $particular_totals[ $i ], $dbtotals[ $i ] ) ); // Non-numeric values: Title of task + total in numbers + percentage
		} else {
			fputcsv( $file, array( $dbtitles[ $i ], $particular_totals[ $i ], format_percentage( $particular_totals[ $i ], $dbtotals[ $i ] ) ) ); // Numeric values: Title of task + total in numbers + percentage
		}
	}

	// New line
	fputcsv( $file, array( '' ) );

	// Writing plugin headers
	$headers_pluginlist = array( 'S.No', 'Plugin Name', 'Description', 'Author', 'Active/Inactive', 'Current Version' );
	fputcsv( $file, $headers_pluginlist );

	// Populating plugins list
	foreach ( $pluginlist as $data ) {
		fputcsv( $file, explode( '|', $data ) );
	}

	fclose( $file );

	// Final message
	$url = $upload_dir['baseurl'] . '/' . $filename;
	$message = "Site info exported successfully. Click <a href='" . $url . "' target='_blank'>here</a> to download your CSV. Or click <a href='tools.php?page=accelera-export'>here</a> to go back.";
	echo '<div class="wrap"><h1>Accelera Export</h1><div class="notice notice-success"><p>' . $message . '</p></div></div>';

	write_log( 'Accelera Export - Step 12 completed' );
}
?>