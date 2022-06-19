<?php
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