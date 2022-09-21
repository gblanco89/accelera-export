<?php
/**
 * This part simply writes all the results to the CSV file
 *
 * @since 1.0.0
 */

$filename = time() . '_' . 'accelera-export.csv';
//$filename = 'test.csv';
$upload_dir = wp_upload_dir();
$file = fopen( $upload_dir['basedir'] . '/' . $filename, 'w');

// Writing task headers
$taskheaders = array( 'Optimize images', 'File cache', 'Theme analysis', 'Minify CSS', 'Minify Javascript', 'Enable GZip compression', 'Update PHP version', 'Combine CSS', 'Combine JS', 'Leverage browser caching', 'Defer parsing of JS', 'Control Heartbeat API interval', 'Clean headers of website', 'Remove Capital P Dangit filter', 'Disable Self Pingbacks', 'External resources optimization', 'Preconnect to external sites', 'Ads analysis', 'Disable WP-Cron', 'Use HTTP/2', 'Apply DNS Prefetch' );
fputcsv( $file, $taskheaders );

// Writing task results
fputcsv( $file, $results_tasks );
fputcsv( $file, $results_tasks_auxiliar );

// New line
fputcsv( $file, array( '' ) );

// Writing extra server info
fputcsv( $file, $extra_server_info );

// New line
fputcsv( $file, array( '' ) );

// Writing DB headers
fputcsv( $file, $dbheaders );

// Writing DB results
for (  $i = 0; $i < count( $dbtitles ); $i++ ) {
    if ( ! is_numeric( $dbtotals[ $i ] ) ) {
        fputcsv( $file, array( $dbtitles[ $i ], $particular_totals[ $i ], $dbtotals[ $i ] ) ); // Non-numeric values: Title of task + total in numbers + percentage
    } else {
        fputcsv( $file, array( $dbtitles[ $i ], $particular_totals[ $i ], format_percentage( $particular_totals[ $i ], $dbtotals[ $i ] ) ) ); // Numeric values: Title of task + total in numbers + percentage
    }
}

// New line
fputcsv( $file, array( '' ) );

// Writing plugin headers
$headers_pluginlist = array( 'S.No', 'Plugin Name', 'Description', 'Author', 'Active/Inactive', 'Current Version' );
fputcsv( $file, $headers_pluginlist );

// Populating plugins list
foreach ( $pluginlist as $data ) {
    fputcsv( $file, explode( '|', $data ) );
}

fclose( $file );

// Final message
$url = $upload_dir['baseurl'] . '/' . $filename;
$message = "Site info exported successfully. Click <a href='" . $url . "' target='_blank'>here</a> to download your CSV. Or click <a href='tools.php?page=accelera-export'>here</a> to go back.";

// Removing other annoying notices
echo '<style>.update-nag, .notice-info:not(.accelera-notice), .notice-warning:not(.accelera-notice), .updated, .error, .is-dismissible, .ngg_admin_notice, .sbi_notice, .notice:not(.accelera-notice) { display: none !important; }</style>';

echo '<div class="wrap"><h1>Accelera Export</h1><div class="notice notice-success accelera-notice"><p>' . $message . '</p></div></div>';

write_log( 'Accelera Export - Step 12 completed' );