<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Module Name: Leverage browser caching
 * Description: Checks the website and tells whether there's a need to leverage the browser caching.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done + not NGINX
 * @internal 'C' = Not done + NGINX
 * @internal 'D' = Unknown because some of the assets are inaccessible
 *
 */

$temp_results_tasks_auxiliar = '';

/**
 * Gets a CSS file sample
 *
 * @since 1.0.0
 *
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return string The 'style.css' of the theme.
 */
function return_first_css(&$temp_results_tasks_auxiliar) {
    $temp_results_tasks_auxiliar .= get_template_directory_uri() . '/style.css' . "\n";
    return get_template_directory_uri() . '/style.css';
}

/**
 * Gets a JS file sample
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return array If no JS file found, returns 'vacio'.
 * @return array If one JS file is found, returns the full URL.
 */
function return_first_js( $home_url_body, $thedomain, &$temp_results_tasks_auxiliar ) {
    preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.js)/i", $home_url_body, $js_file );
    if ( ! empty( $js_file ) ) {
        $temp_results_tasks_auxiliar .= $js_file[0] . "\n";
        return $js_file;
    } else {
        return array( 'vacio' );
    }
}

/**
 * Gets an IMG file sample
 *
 * The image file sample can be a JPG, JPEG, PNG or GIF.
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return array If no image file found, returns 'vacio'.
 * @return array If one image file is found, returns the full URL.
 */
function return_first_img( $home_url_body, $thedomain, &$temp_results_tasks_auxiliar ) {
    preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.(jpg|jpeg|png|gif))/i", $home_url_body, $img_file );
    if ( !empty( $img_file ) ) {
        $temp_results_tasks_auxiliar .= $img_file[0] . "\n";
        return $img_file;
    } else {
        return array( 'vacio' );
    }
}

/**
 * Gets an SVG file sample
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @param string &$temp_results_tasks_auxiliar The string containing the second row of tasks (auxiliar).
 * @return array If no SVG file found, returns 'vacio'.
 * @return array If one SVG file is found, returns the full URL.
 */
function return_first_svg( $home_url_body, $thedomain, &$temp_results_tasks_auxiliar ) {
    preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.svg)/i", $home_url_body, $img_file );
    if ( !empty( $img_file ) ) {
        $temp_results_tasks_auxiliar .= $img_file[0] . "\n";
        return $img_file;
    } else {
        return array( 'vacio' );
    }
}

$first_assets = array( return_first_css($temp_results_tasks_auxiliar), return_first_js( $home_url_body, $thedomain, $temp_results_tasks_auxiliar )[0], return_first_img( $home_url_body, $thedomain, $temp_results_tasks_auxiliar )[0], return_first_svg( $home_url_body, $thedomain, $temp_results_tasks_auxiliar )[0] ); // Getting an array of the first asset of each type
$a = 0; //Counter of browser cache too low
$fourofour = 0; // Counter of 404s in resources

// Let's check the actual cache-control for each asset
foreach ( $first_assets as $asset_url ) {
    if ( 'vacio' !== $asset_url ) {
        $response_asset = wp_remote_get( $asset_url );

        $response_code = wp_remote_retrieve_response_code( $response_asset );
        if ( preg_match( "/^2..$/", $response_code ) ) { // Only enter if the response code for the asset is 2XX
            $cachectrl = wp_remote_retrieve_header( $response_asset, 'cache-control' );
            if ( empty( $cachectrl ) ) { // If cache-control is not set, needs to be set
                $a++;
            } else {
                if ( is_array ( $cachectrl ) ) { // If there is more than one cache-control header, it will be an array.
                    foreach ( $cachectrl as $counter ) {
                        if ( preg_match( "/[0-9]+/", $counter, $cache_control_value ) ) {
                            if ( $cache_control_value[0] < 10368000 ) {
                                $a++; // If too low, mark it
                            }
                            break; // Don't check more cache-control headers, because we already found a numeric one.
                        } else {
                        $a++; // If cache-control is not set, needs to be set
                        }
                    }
                }
                elseif ( preg_match( "/[0-9]+/", $cachectrl, $cache_control_value ) ) { // We get the value of cache-control, if it exists
                    if ( $cache_control_value[0] < 10368000 ) {
                        $a++; // If too low, mark it
                    };
                } else {
                    $a++; // If cache-control is not set, needs to be set
                }
            }
        }
        else {
            $fourofour++;
        }
    }
}

if ( $fourofour > 0 ) {
    $results_tasks[] = 'D';
} elseif ( $a > 0 && 'nginx' !== $acc_webserver ) {
    $results_tasks[] = 'B';
} elseif ( $a > 0 && 'nginx' === $acc_webserver ) {
    $results_tasks[] = 'C';
} else {
    $results_tasks[] = 'A';
}

$results_tasks_auxiliar[] = $temp_results_tasks_auxiliar;

write_log( 'Accelera Export - Step 9 completed' );