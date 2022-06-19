<?php
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