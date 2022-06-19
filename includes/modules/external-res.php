<?php
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