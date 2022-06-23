<?php
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

write_log( 'Accelera Export - Step 1 completed' );


// Preparations - theme and plugin definitions
$goodthemes = array( 'Twenty Nineteen', 'Twenty Twenty', 'Twenty Twenty-One', 'Neve', 'Blocksy', 'Astra', 'OceanWP', 'Storefront', 'Suki', 'Kadence', 'Mesmerize', 'MagazineWP', 'Acabado', 'Extra', 'Genesis', 'GeneratePress', 'Button Theme', 'Basic', 'Meteor V3', 'Catch FSE', 'Twenty Twenty-Two', 'Grigora Blocks', 'Archeo', 'Ona', 'Avant-Garde', 'Skatepark', 'Wabi', 'Livro', 'Bricksy', 'WOWMALL', 'Tove', 'Quadrat', 'Mayland (Blocks)', 'Nutmeg', 'Blockbase', 'Naledi', 'Churel', 'Eksell', 'Hansen', 'GT Basic', 'Phoenix', 'Artpop', 'Empt Lite', 'Blank Canvas' );

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
    'comet-cache' => false,
    'hummingbird-performance' => false,
    'hyper-cache' => false,
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


// Preparations - HTTP headers from homepage
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


// Preparations - Plugin list + checking each plugin against Accelera list
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


// Preparations - Gathering plugins options
$accelera_spaioptions = get_option( 'short_pixel_ai_options', false ); // SPAI
$accelera_wprocketoptions = get_option( 'wp_rocket_settings', false ); // WP Rocket
$accelera_swiftoptions = get_option( 'swift_performance_options', false ); // Swift Performance
$accelera_wpoptimizeoptions = get_option( 'wpo_cache_config', false ); // WP-Optimize
$accelera_jetpackmodules = get_option( 'jetpack_active_modules', false ); // Jetpack modules
$accelera_assetcleanoptions = get_option( 'wpassetcleanup_settings', false ); // Asset CleanUp
$accelera_pfmattersoptions = get_option( 'perfmatters_options', false ); // Perfmatters


// Checks and populating $results_tasks, array of task results
$results_tasks = array();
$results_tasks_auxiliar = array(); //2nd row on CSV
$thedomain = preg_quote( parse_url( get_home_url() )['host'] ); // Website domain
$acc_webserver = strtolower( explode( '/', $_SERVER['SERVER_SOFTWARE'] )[0] ); // Web server

write_log( 'Accelera Export - Step 5 completed' );

// Extra server info
$extra_server_info = array();
if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
    $extra_server_info[] = $_SERVER['SERVER_SOFTWARE'];
}