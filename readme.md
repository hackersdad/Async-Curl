Async-Curl (Version 1.0)

Installation / Getting Started

Clone the repository to your local environment:

    sh
    Copy code
    git clone <repository-url>
    cd async-curl

Ensure PHP is installed on your system.

Create a logs.txt file in the async-curl directory for logging errors if it does not exist, and make sure the PHP user has permissions to read and write this file:

Make sure php-curl is installed and enabled:

Ensure that the exec function is not disabled in your php.ini configuration:

This script is designed for Linux servers only and is not compatible with Windows environments.

Using async_curl function in your PHP project:

<?php
    require '/path/to/async-curl/async-curl.php';

    $url = 'https://api.example.com/data';
    $headers = [
        'Authorization: Bearer your_access_token',
        'Content-Type: application/json'
    ];
    $data = [
        'key' => 'value'
    ];
    $options = [
        'follow_redirects' => true,
        'timeout' => 30,
        'ssl_verify' => true
    ];

    $result = async_curl($url, 'POST', $data, $headers, $options);

    if ($result === true) {
        echo "Curl request initiated asynchronously.";
    } else {
        echo "Failed to initiate curl request. Check logs for details.";
    }
?>

Supported Features

    Asynchronous Curl Requests: Execute HTTP requests asynchronously using PHP's exec function to run async-curl-core.php in the background.

    HTTP Methods: Supports various HTTP methods including GET, POST, PUT, DELETE, and PATCH.

    Request Data Formats: Send data in JSON or form-urlencoded formats. Automatically sets headers based on the specified format.

    Custom Headers: Include custom HTTP headers in your requests.

    Request Options: Set options like follow redirects, timeout duration, and SSL verification.

    Error Handling: Logs detailed error messages in logs.txt for troubleshooting failed requests.

Troubleshooting
    
    Common Issues and Solutions:
    
    Empty or Missing logs.txt file: If logs.txt is empty or missing, ensure that PHP has write permissions for the async-curl directory. You can create the file manually and grant appropriate permissions.

    Invalid JSON string provided: If you encounter "Invalid JSON string provided" errors, ensure that JSON strings passed to async-curl-core.php are properly formatted.

    Curl request failed: Check the logs.txt file for detailed error messages. Common causes include network issues, incorrect URLs, or server-side errors.

Notes:

    Ensure proper error handling in your calling scripts to manage responses from async_curl.

    This version of the README includes all the necessary requirements and setup steps to ensure that async-curl works correctly in a Linux environment, including the need for PHP read/write access to the async-curl directory, php-curl installation and enabling, and ensuring the exec function is not disabled.