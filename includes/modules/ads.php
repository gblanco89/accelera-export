<?php
/**
 * Module Name: Ads analysis
 * Description: Checks the website and tells whether there are ads or not.
 *
 * @since 1.0.0
 *
 * @internal 'A' = Ads
 * @internal 'B' = No ads
 *
 */

$temp_results_tasks_auxiliar = '';

/**
 * Checks if the homepage has ads
 *
 * @since 1.0.0
 *
 * @param string $home_url_body A string containing the whole body of the page.
 * @return string 'A' if there are ads.
 * @return string 'B' if there are no ads.
 **/
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