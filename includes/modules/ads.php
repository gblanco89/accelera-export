<?php
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