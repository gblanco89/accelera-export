<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Accelera Audit API Client
 * 
 * Reusable class to send export data to the Accelera Audit plugin API endpoint
 * 
 * @since 2.0
 */
class Accelera_Audit_API_Client {

    /**
     * API endpoint URL
     * 
     * @var string
     */
    private $api_endpoint;

    /**
     * Domain name (without protocol)
     * 
     * @var string
     */
    private $domain;

    /**
     * Bearer token for authentication
     * 
     * @var string
     */
    private $bearer_token;

    /**
     * Constructor
     * 
     * @param string $bearer_token Optional bearer token for authentication
     */
    public function __construct( $bearer_token = '' ) {
        $this->api_endpoint = rest_url( 'accelera_audit/v1/export/store' );
        $this->domain = $this->extract_domain_from_url( get_site_url() );
        $this->bearer_token = $bearer_token;
    }

    /**
     * Extract domain from URL (remove http/https protocol)
     * 
     * @param string $url The full URL
     * @return string The domain without protocol
     */
    private function extract_domain_from_url( $url ) {
        $parsed_url = parse_url( $url );
        return isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
    }

    /**
     * Send report data to the Accelera Audit API
     * 
     * @param string $report_data The complete report data as a string
     * @return array Response array with 'success' boolean and 'data' or 'error' key
     */
    public function send_report( $report_data ) {
        // Validate inputs
        if ( empty( $this->domain ) ) {
            return array(
                'success' => false,
                'error' => __( 'Unable to determine domain from site URL', 'accelera-export' )
            );
        }

        if ( empty( $report_data ) ) {
            return array(
                'success' => false,
                'error' => __( 'Report data is empty', 'accelera-export' )
            );
        }

        // Prepare payload
        $payload = array(
            'domain' => $this->domain,
            'report_data' => $report_data
        );

        // Prepare request arguments
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( $payload )
        );

        // Add bearer token if provided
        if ( ! empty( $this->bearer_token ) ) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->bearer_token;
        }

        // Log the request (if WP_DEBUG is enabled)
        write_log( 'Accelera Export - Sending data to Audit API: ' . $this->api_endpoint );
        write_log( 'Domain: ' . $this->domain );
        write_log( 'Data size: ' . strlen( $report_data ) . ' bytes' );

        // Make the API request
        $response = wp_remote_post( $this->api_endpoint, $args );

        // Check for errors
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            write_log( 'Accelera Export - API Error: ' . $error_message );
            
            return array(
                'success' => false,
                'error' => sprintf(
                    __( 'API request failed: %s', 'accelera-export' ),
                    $error_message
                )
            );
        }

        // Get response code and body
        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        
        // Clean up the response body - extract JSON if there's HTML noise before it
        $json_start = strpos( $response_body, '{' );
        if ( $json_start !== false && $json_start > 0 ) {
            $response_body = substr( $response_body, $json_start );
        }
        
        $response_data = json_decode( $response_body, true );

        // Log the response
        write_log( 'Accelera Export - API Response Code: ' . $response_code );
        write_log( 'Accelera Export - API Response: ' . $response_body );

        // Check if request was successful (2xx status code)
        if ( $response_code >= 200 && $response_code < 300 ) {
            return array(
                'success' => true,
                'data' => $response_data,
                'response_code' => $response_code
            );
        } else {
            // API returned an error
            $error_message = isset( $response_data['message'] ) 
                ? $response_data['message'] 
                : __( 'Unknown API error', 'accelera-export' );

            return array(
                'success' => false,
                'error' => $error_message,
                'response_code' => $response_code,
                'response_data' => $response_data
            );
        }
    }

    /**
     * Get the domain being used
     * 
     * @return string
     */
    public function get_domain() {
        return $this->domain;
    }

    /**
     * Set a custom bearer token
     * 
     * @param string $token
     */
    public function set_bearer_token( $token ) {
        $this->bearer_token = $token;
    }
}
