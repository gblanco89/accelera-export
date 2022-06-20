<?php
/**
 * Module Name: External resources optimization
 * Description: Checks the website and tells whether the user needs to optimize Google Fonts,
 * Google Analytics and/or delay the parsing of JS.
 *
 * @since 1.0.0
 *
 * @internal 'A' = All good
 * @internal 'B' = GA
 * @internal 'C' = GF
 * @internal 'D' = GA + GF
 * @internal 'E' = GA + WP Rocket
 * @internal 'F' = GA + GF + Flying Scripts
 * @internal 'G' = GF + Flying Scripts
 * @internal 'H' = Flying Scripts
 * @internal 'I' = GF + WP Rocket
 * @internal 'J' = GA + Flying Scripts
 * @internal 'K' = GA + GF + WP Rocket
 * @internal 'L' = WP Rocket
 */

$temp_results_tasks_auxiliar = '';

$gf = $ga = $wpr_delay = false;

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