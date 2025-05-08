<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

// 1) open a .txt file instead of .csv
$filename   = time() . '_accelera-export.txt';
$upload_dir = wp_upload_dir();
$filepath   = $upload_dir['basedir'] . '/' . $filename;
$file       = fopen( $filepath, 'w' );

// 2) Task Results
// write_section( $file, 'Task Results' );
// if ( ! empty( $results_tasks ) && is_array( $results_tasks ) ) {
//     foreach ( $results_tasks as $row ) {
//         if ( is_array( $row ) ) {
//             // join columns with " | "
//             fwrite( $file, implode( ' | ', $row ) . PHP_EOL );
//         } else {
//             fwrite( $file, $row . PHP_EOL );
//         }
//     }
//     fwrite( $file, PHP_EOL );
// }

// 3) Theme Info (was $results_tasks_auxiliar)
write_section( $file, 'Theme Info' );
if ( ! empty( $my_theme ) ) {
    if ( is_array( $my_theme ) ) {
        foreach ( $my_theme as $key => $val ) {
            // if you need key: value format
            fwrite( $file, "{$key}: {$val}" . PHP_EOL );
        }
    } else {
        fwrite( $file, $my_theme . PHP_EOL );
    }
    fwrite( $file, PHP_EOL );
}

// 4) Extra Server Info
write_section( $file, 'Extra Server Info' );
if ( ! empty( $extra_server_info ) && is_array( $extra_server_info ) ) {
    foreach ( $extra_server_info as $key => $val ) {
        fwrite( $file, "{$key}: {$val}" . PHP_EOL );
    }
    fwrite( $file, PHP_EOL );
}

// 5) Database Info
write_section( $file, 'Database Info' );
if ( ! empty( $dbheaders ) && is_array( $dbheaders ) ) {
    fwrite( $file, implode( ' | ', $dbheaders ) . PHP_EOL );
}
for ( $i = 0; $i < count( $dbtitles ); $i++ ) {
    $title    = $dbtitles[ $i ];
    $total    = $particular_totals[ $i ];
    $raw      = $dbtotals[ $i ];
    $percent  = is_numeric( $raw )
        ? format_percentage( $total, $raw )
        : $raw;
    fwrite( $file, "{$title} | {$total} | {$percent}" . PHP_EOL );
}
fwrite( $file, PHP_EOL );

// 6) Plugin List
write_section( $file, 'Plugin List' );
foreach ( $pluginlist as $line ) {
    fwrite( $file, implode( ' | ', explode( '|', $line ) ) . PHP_EOL );
}
fwrite( $file, PHP_EOL );

// 6.1) MU Plugin List
write_section( $file, 'MU Plugin List' );
foreach ( $mu_pluginlist as $line ) {
    fwrite( $file, implode( ' | ', explode( '|', $line ) ) . PHP_EOL );
}
fwrite( $file, PHP_EOL );

// 7) Hosting Provider
write_section( $file, 'Hosting Provider' );
$hosting_provider = isset( $_POST['hosting_provider'] )
    ? sanitize_text_field( $_POST['hosting_provider'] )
    : __( 'Not provided', 'accelera-export' );
fwrite( $file, $hosting_provider . PHP_EOL . PHP_EOL );

// 8) WordPress Version
write_section( $file, 'WordPress Version' );
global $wp_version;
fwrite( $file, $wp_version . PHP_EOL . PHP_EOL );

// 9) Tables
write_section( $file, 'Tables' );
$tables = get_all_tables();
foreach ( $tables as $tbl ) {
    fwrite( $file, $tbl . PHP_EOL );
}

fclose( $file );

// 10) Success message
$url     = $upload_dir['baseurl'] . '/' . $filename;
$message = sprintf(
    __('Site info exported successfully. Click <a href="%s" target="_blank">here</a> to download your TXT (you can right-click the link to save it to your computer). Or click <a href="tools.php?page=accelera-export">here</a> to go back.', 'accelera-export'),
    esc_url( $url )
);

// hide other notices
echo '<style>.update-nag, .notice-info:not(.accelera-notice), .notice-warning:not(.accelera-notice), .updated, .error, .is-dismissible { display:none!important; }</style>';
echo '<div class="wrap"><h1>Accelera Export</h1><div class="notice notice-success accelera-notice"><p>' . $message . '</p></div></div>';

write_log( 'Accelera Export - TXT format completed' );

/**
 * helper to write a section header
 */
function write_section( $file, $title ) {
    fwrite( $file, "/------------/ {$title} /------------/" . PHP_EOL );
}
