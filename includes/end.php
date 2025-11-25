<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Helper function to write a section header
 */
function write_section_header( $title ) {
    return "/------------/ {$title} /------------/" . PHP_EOL;
}

// 1) Build report data in memory first
$report_data = '';

// 3) Theme Info (was $results_tasks_auxiliar)
$report_data .= write_section_header( 'Theme Info' );
if ( ! empty( $my_theme ) ) {
    if ( is_array( $my_theme ) ) {
        foreach ( $my_theme as $key => $val ) {
            $report_data .= "{$key}: {$val}" . PHP_EOL;
        }
    } else {
        $report_data .= $my_theme . PHP_EOL;
    }
    $report_data .= PHP_EOL;
}

// 4) Extra Server Info
$report_data .= write_section_header( 'Extra Server Info' );
if ( ! empty( $extra_server_info ) && is_array( $extra_server_info ) ) {
    foreach ( $extra_server_info as $key => $val ) {
        $report_data .= "{$key}: {$val}" . PHP_EOL;
    }
    $report_data .= PHP_EOL;
}

// 5) Database Info
$report_data .= write_section_header( 'Database Info' );
if ( ! empty( $dbheaders ) && is_array( $dbheaders ) ) {
    $report_data .= implode( ' | ', $dbheaders ) . PHP_EOL;
}
for ( $i = 0; $i < count( $dbtitles ); $i++ ) {
    $title    = $dbtitles[ $i ];
    $total    = $particular_totals[ $i ];
    $raw      = $dbtotals[ $i ];
    $percent  = is_numeric( $raw )
        ? format_percentage( $total, $raw )
        : $raw;
    $report_data .= "{$title} | {$total} | {$percent}" . PHP_EOL;
}
$report_data .= PHP_EOL;

// 6) Plugin List
$report_data .= write_section_header( 'Plugin List' );
foreach ( $pluginlist as $line ) {
    $report_data .= implode( ' | ', explode( '|', $line ) ) . PHP_EOL;
}
$report_data .= PHP_EOL;

// 6.1) MU Plugin List
$report_data .= write_section_header( 'MU Plugin List' );
foreach ( $mu_pluginlist as $line ) {
    $report_data .= implode( ' | ', explode( '|', $line ) ) . PHP_EOL;
}
$report_data .= PHP_EOL;

// 7) Hosting Provider
$report_data .= write_section_header( 'Hosting Provider' );
$hosting_provider = isset( $_POST['hosting_provider'] )
    ? sanitize_text_field( $_POST['hosting_provider'] )
    : __( 'Not provided', 'accelera-export' );
$report_data .= $hosting_provider . PHP_EOL . PHP_EOL;

// 8) WordPress Version
$report_data .= write_section_header( 'WordPress Version' );
global $wp_version;
$report_data .= $wp_version . PHP_EOL . PHP_EOL;

// 9) Tables
$report_data .= write_section_header( 'Tables' );
$tables = get_all_tables();
foreach ( $tables as $tbl ) {
    $report_data .= $tbl . PHP_EOL;
}

// ========================================
// SEND DATA TO ACCELERA AUDIT API FIRST
// ========================================
$api_response = null;
$api_error = null;

try {
    // Initialize API client
    $api_client = new Accelera_Audit_API_Client();
    $api_client->set_bearer_token( ACCELERA_EXPORT_AUDIT_APP_API_KEY );
    // Send report data to Audit API
    $api_result = $api_client->send_report( $report_data );
    
    // Debug: Log the entire API result
    write_log( 'Accelera Export - API Result: ' . print_r( $api_result, true ) );
    
    if ( $api_result['success'] ) {
        write_log( 'Accelera Export - Successfully sent data to Audit API' );
        $api_response = $api_result;
        write_log( 'Accelera Export - API Response Data: ' . print_r( $api_response, true ) );
    } else {
        write_log( 'Accelera Export - Failed to send data to Audit API: ' . $api_result['error'] );
        $api_error = $api_result['error'];
    }
} catch ( Exception $e ) {
    write_log( 'Accelera Export - Exception while sending to Audit API: ' . $e->getMessage() );
    $api_error = $e->getMessage();
}

// ========================================
// NOW CREATE THE FILE FOR DOWNLOAD
// ========================================
$filename   = time() . '_accelera-export.txt';
$upload_dir = wp_upload_dir();
$filepath   = $upload_dir['basedir'] . '/' . $filename;
$file       = fopen( $filepath, 'w' );

// Write the report data to file
fwrite( $file, $report_data );

fclose( $file );

// 10) Success message with API status
$url = $upload_dir['baseurl'] . '/' . $filename;

// Build message based on API response
$download_message = sprintf(
    __('Site info exported successfully. Click <a href="%s" target="_blank" download>here</a> to download your TXT (you can right-click the link to save it to your computer). Or click <a href="tools.php?page=accelera-export">here</a> to go back.', 'accelera-export'),
    esc_url( $url )
);

$api_status_message = '';
if ( $api_response && $api_response['success'] ) {
    $api_status_message = '<p style="color: #46b450;"><strong>✓ Data successfully sent to Accelera Audit system.</strong></p>';
    
    // Check if we have data and report_number (it's nested: data->data->report_number)
    if ( isset( $api_response['data']['data']['report_number'] ) ) {
        $report_number = esc_html( $api_response['data']['data']['report_number'] );
        $api_status_message .= '<div style="margin: 15px 0; padding: 12px; background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px;">';
        $api_status_message .= '<p style="margin: 0 0 8px 0;"><strong>Report Number:</strong></p>';
        $api_status_message .= '<div style="display: flex; align-items: center; gap: 10px;">';
        $api_status_message .= '<input type="text" id="report-number-field" value="' . $report_number . '" readonly style="flex: 1; padding: 8px; font-family: monospace; font-size: 14px; background: #fff; border: 1px solid #8c8f94; border-radius: 3px;" />';
        $api_status_message .= '<button type="button" class="button button-secondary" onclick="copyReportNumber()" style="white-space: nowrap;"><span class="dashicons dashicons-clipboard" style="vertical-align: middle; margin-top: -2px;"></span> Copy</button>';
        $api_status_message .= '</div>';
        $api_status_message .= '<p id="copy-confirmation" style="margin: 8px 0 0 0; color: #46b450; display: none;"><small>✓ Copied to clipboard!</small></p>';
        $api_status_message .= '</div>';
        $api_status_message .= '<script>
        function copyReportNumber() {
            var copyText = document.getElementById("report-number-field");
            
            // Select the text
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            
            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(copyText.value).then(function() {
                    showCopyConfirmation();
                }).catch(function(err) {
                    // Fallback to execCommand
                    fallbackCopy(copyText);
                });
            } else {
                // Fallback for older browsers
                fallbackCopy(copyText);
            }
        }
        
        function fallbackCopy(copyText) {
            try {
                copyText.focus();
                copyText.select();
                var successful = document.execCommand("copy");
                if (successful) {
                    showCopyConfirmation();
                } else {
                    alert("Copy failed. Please manually select and copy the text.");
                }
            } catch (err) {
                alert("Copy failed. Please manually select and copy the text.");
            }
        }
        
        function showCopyConfirmation() {
            var confirmation = document.getElementById("copy-confirmation");
            confirmation.style.display = "block";
            setTimeout(function() {
                confirmation.style.display = "none";
            }, 3000);
        }
        </script>';
    }
} elseif ( $api_error ) {
    $api_status_message = '<p style="color: #dc3232;"><strong>⚠ Warning: Could not send data to Accelera Audit system.</strong><br><small>' . esc_html( $api_error ) . '</small></p>';
    $api_status_message .= '<p><small>The export file was still created successfully. You can manually upload it if needed.</small></p>';
}

// hide other notices
echo '<style>.update-nag, .notice-info:not(.accelera-notice), .notice-warning:not(.accelera-notice), .updated, .error, .is-dismissible { display:none!important; }</style>';
echo '<div class="wrap"><h1>Accelera Export</h1>';
echo '<div class="notice notice-success accelera-notice">';
echo '<p>' . $download_message . '</p>';
echo $api_status_message;
echo '</div></div>';

write_log( 'Accelera Export - TXT format completed' );
