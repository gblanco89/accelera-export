<?php
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