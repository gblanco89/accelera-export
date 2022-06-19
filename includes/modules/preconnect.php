<?php
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