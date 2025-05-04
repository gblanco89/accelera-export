<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
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
$taskheaders = array( 'Task', 'Status', 'Description' );
fputcsv( $file, $taskheaders );

// Writing task results
// fputcsv( $file, $results_tasks );
// Theme Info
dump($results_tasks_auxiliar);
fputcsv( $file, $results_tasks_auxiliar );

// New line
fputcsv( $file, array( '' ) );

// Writing extra server info
fputcsv( $file, $extra_server_info );

dump($extra_server_info);

// New line
fputcsv( $file, array( '' ) );

// Writing DB headers
fputcsv( $file, $dbheaders );

dump($dbheaders);
// Writing DB results
for (  $i = 0; $i < count( $dbtitles ); $i++ ) {

    dump($dbtitles[ $i ]);
    dump($particular_totals[ $i ]);
    dump($dbtotals[ $i ]);
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

// dd($headers_pluginlist);
// Populating plugins list
foreach ( $pluginlist as $data ) {
    fputcsv( $file, explode( '|', $data ) );
}

// Check if hosting provider is submitted
if ( isset( $_POST['hosting_provider'] ) ) {
    $hosting_provider = sanitize_text_field( $_POST['hosting_provider'] );
} else {
    $hosting_provider = __( 'Not provided', 'accelera-export' );
}

// Write hosting provider
fputcsv( $file, array( __( 'Hosting Provider', 'accelera-export' ), $hosting_provider ) );

global $wp_version;
// Write WP Version
fputcsv( $file, array( __( 'Current WP Version', 'accelera-export' ), $wp_version ) );

$tables = get_all_tables();

// Write tables
foreach ( $tables as $table ) {
    $table_name = $table;
    fputcsv( $file, array( __( 'Table Name', 'accelera-export' ), $table_name ) );
}

fclose( $file );

// Final message
$url = $upload_dir['baseurl'] . '/' . $filename;
$message = sprintf(__('Site info exported successfully. Click <a href="%s" target="_blank">here</a> to download your CSV. Or click <a href="tools.php?page=accelera-export">here</a> to go back.', 'accelera-export'), $url);


// Removing other annoying notices
echo '<style>.update-nag, .notice-info:not(.accelera-notice), .notice-warning:not(.accelera-notice), .updated, .error, .is-dismissible, .ngg_admin_notice, .sbi_notice, .notice:not(.accelera-notice) { display: none !important; }</style>';

echo '<div class="wrap"><h1>Accelera Export</h1><div class="notice notice-success accelera-notice"><p>' . $message . '</p></div></div>';

write_log( 'Accelera Export - Step 12 completed' );