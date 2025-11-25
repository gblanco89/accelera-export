# Accelera Export - Audit API Integration

## Overview
The Accelera Export plugin now automatically sends export data to the Accelera Audit API endpoint before creating the download file.

## How It Works

### Flow
1. User fills in the hosting provider form and clicks "TXT Export" button
2. Plugin collects all site data (themes, plugins, database info, etc.)
3. **Data is first sent to Accelera Audit API** at `/wp-json/accelera_audit/v1/export/store`
4. Then the file is created for download
5. User sees success message with API status

### Files Modified/Created

#### New File: `includes/class-audit-api-client.php`
**Reusable API Client Class** that handles communication with Accelera Audit API.

**Key Features:**
- Automatically extracts domain from WordPress site URL (removes http/https)
- Sends POST request with JSON payload containing `domain` and `report_data`
- Supports Bearer token authentication (optional)
- Comprehensive error handling and logging
- Returns structured response with success status

**Usage Example:**
```php
// Basic usage
$api_client = new Accelera_Audit_API_Client();
$result = $api_client->send_report( $report_data_string );

if ( $result['success'] ) {
    // Success! Access report number: $result['data']['report_number']
} else {
    // Error: $result['error']
}

// With Bearer token
$api_client = new Accelera_Audit_API_Client( 'your-bearer-token-here' );
$result = $api_client->send_report( $report_data_string );

// Or set token later
$api_client->set_bearer_token( 'your-bearer-token-here' );
```

**Available Methods:**
- `__construct( $bearer_token = '' )` - Initialize with optional bearer token
- `send_report( $report_data )` - Send report to API, returns array with 'success' and 'data'/'error'
- `get_domain()` - Get the extracted domain being used
- `set_bearer_token( $token )` - Set/update bearer token for authentication

#### Modified: `accelera-export.php`
- Added `require` statement to load the API client class

#### Modified: `includes/end.php`
- Completely refactored to build report data in memory first
- Sends data to Audit API before creating download file
- Enhanced user feedback showing API status
- Graceful error handling - file still downloads even if API fails

## API Integration Details

### Endpoint
```
POST /wp-json/accelera_audit/v1/export/store
```

### Payload Structure
```json
{
    "domain": "example.com",
    "report_data": "Full text report content..."
}
```

**Domain**: Automatically extracted from WordPress `get_site_url()` with protocol removed
- Example: `https://example.com` becomes `example.com`
- Example: `http://subdomain.example.com` becomes `subdomain.example.com`

**Report Data**: The exact same data that gets written to the download file, including:
- Theme information
- Server information
- Database statistics
- Plugin list
- MU Plugin list
- Hosting provider
- WordPress version
- Table names

### Authentication
The API client supports Bearer token authentication. If the Accelera Audit API requires authentication:

```php
// Set token in constructor
$api_client = new Accelera_Audit_API_Client( 'your-token' );

// Or set it later
$api_client->set_bearer_token( 'your-token' );
```

### Response Handling

**Success Response (HTTP 2xx):**
```php
array(
    'success' => true,
    'data' => array(
        'id' => 123,
        'report_number' => 'REP-20231125-ABC123',
        'domain' => 'example.com',
        'data_size_kb' => 45.23,
        'export_date' => '2023-11-25 10:30:00'
    ),
    'response_code' => 201
)
```

**Error Response:**
```php
array(
    'success' => false,
    'error' => 'Error message here',
    'response_code' => 500,
    'response_data' => array(...) // Full API response if available
)
```

## User Experience

### Success Scenario
When export completes successfully, user sees:
```
✓ Data successfully sent to Accelera Audit system.
Report Number: REP-20231125-ABC123

Site info exported successfully. Click here to download your TXT...
```

### API Failure Scenario
If API call fails, export still completes but shows warning:
```
⚠ Warning: Could not send data to Accelera Audit system.
[Error message details]

The export file was still created successfully. You can manually upload it if needed.

Site info exported successfully. Click here to download your TXT...
```

## Error Handling

The implementation includes comprehensive error handling:
- **Try-catch block** wraps API call
- **Network errors** (timeout, connection issues) are caught
- **API errors** (4xx, 5xx responses) are handled gracefully
- **File download always works** regardless of API status
- **All errors logged** to WordPress debug log when WP_DEBUG is enabled

## Debugging

Enable WordPress debugging to see API activity:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

Log entries will show:
- API endpoint being called
- Domain being sent
- Data size in bytes
- API response code
- Success/failure messages
- Full error details

## Extending the API Client

The `Accelera_Audit_API_Client` class is designed to be reusable. To use it in other plugins or contexts:

```php
// Include the class file
require_once WP_PLUGIN_DIR . '/accelera-export/includes/class-audit-api-client.php';

// Use it anywhere
$api_client = new Accelera_Audit_API_Client();
$your_data = "Your custom report data here...";
$result = $api_client->send_report( $your_data );
```

## Requirements

- WordPress with REST API enabled
- Accelera Audit plugin installed and active
- Network connectivity to the API endpoint

## Notes

- The API call has a 30-second timeout
- Data size is logged for monitoring
- The same data sent to API is written to the download file
- No sensitive data is transmitted (only technical site information)
