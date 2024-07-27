<?php

require_once 'error_handler.php';


// Function to send curl request
function sendCurlRequest($url, $method = 'GET', $data = null, $headers = [], $options = [], $dataFormat = 'json') {
    $ch = curl_init();

    // Set URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set HTTP Method
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            break;
        case 'PUT':
        case 'DELETE':
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            break;
        default:
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            break;
    }

    // Set Headers
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    // Set Data
    if (!empty($data)) {
        if (strtolower($dataFormat) === 'json') {
            $data = json_encode($data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        } elseif (strtolower($dataFormat) === 'form') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/x-www-form-urlencoded']));
            $data = http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    // Set Options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the transfer as a string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $options['follow_redirects'] ?? true); // Follow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 30); // Set timeout

    // Set SSL verification if provided in options
    if (isset($options['ssl_verify'])) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $options['ssl_verify']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $options['ssl_verify'] ? 2 : 0);
    }

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Error Handling
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        log_error($error, compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        return array('status' => "fail", 'error' => $error);
    }

    curl_close($ch);

    return array('status' => "success",'http_code' => $httpCode,'response' => $response);
    // log_error($res, compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
}

// Check if there are command line arguments
if ($argc > 1) {
    $options = getopt("", ["params_file::"]);

    if (isset($options['params_file'])) {
        $paramsFilePath = $options['params_file'];
        if (file_exists($paramsFilePath)) {
            $params = json_decode(file_get_contents($paramsFilePath), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_error('Error decoding JSON parameters from file: ' . json_last_error_msg(), ['file' => $paramsFilePath]);
                return;
            }
            
            unlink($paramsFilePath);

        } else {
            log_error('Parameters file not found.', ['file' => $paramsFilePath]);
            return;
        }
    } else {
        // Read parameters from the command line argument
        $encoded_parameters = $argv[1];
        $jsonParams = base64_decode($encoded_parameters);
        $params = json_decode($jsonParams, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_error('Error decoding JSON parameters: ' . json_last_error_msg());
            return;
        }
    }

    // Extract parameters with validation
    $url = isset($params['url']) ? filter_var($params['url'], FILTER_VALIDATE_URL) : '';
    $method = isset($params['method']) ? strtoupper($params['method']) : 'GET';
    $data = $params['data'] ?? null;
    $headers = isset($params['headers']) && is_array($params['headers']) ? $params['headers'] : [];
    $options = isset($params['options']) && is_array($params['options']) ? $params['options'] : [];
    $dataFormat = isset($params['data_format']) && in_array($params['data_format'], ['json', 'form']) ? $params['data_format'] : 'json';
    
    // Validate essential parameters
    if (empty($url)) {
        $error = 'Invalid or missing URL.';
        log_error($error, $params);
        exit(1);
    }

    if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
        $error = 'Invalid HTTP method.';
        log_error($error, $params);
        exit(1);
    }

    // Call the function
    try {
        $response = sendCurlRequest($url, $method, $data, $headers, $options, $dataFormat);
        if($response['status'] == 'success'){
            log_error(json_encode($response), $params);
        }
        exit(1);
    } catch (Exception $e) {
        $error = 'Curl request failed: ' . $e->getMessage();
        log_error($error, $params);
        exit(1);
    }
} else {
    $error = 'No command line arguments provided.';
    log_error($error);
    exit(1);
}

?>
