<?php
/**
 * Module Name: Leverage browser caching
 * Description: Checks the website and tells whether there's a need to leverage the browser caching.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Done
 * @internal 'B' = Not done + not NGINX
 * @internal 'C' = Not done + NGINX
 *
 */

$temp_results_tasks_auxiliar = '';

/**
 * Gets a CSS file sample
 *
 * @since 1.0.0
 *
 * @return string The 'style.css' of the theme
 */
function return_first_css() {
    return get_template_directory_uri() . '/style.css';
}

/**
 * Gets a JS file sample
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @param string $thedomain The domain of the website, without http(s) or www.
 * @return string If no JS file found, returns 'vacio'.
 * @return string If one JS file is found, returns the full URL.
 */
function return_first_js( $home_url_body, $thedomain ) {
    preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.js)/i", $home_url_body, $js_file );
    if ( ! empty( $js_file ) ) {
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
 * @return string If no image file found, returns 'vacio'.
 * @return string If one image file is found, returns the full URL.
 */
function return_first_img( $home_url_body, $thedomain ) {
    preg_match( "/(https?:\/\/([^\"']*\.)?{$thedomain}[^\"']*\.(jpg|jpeg|png|gif))/i", $home_url_body, $img_file );
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