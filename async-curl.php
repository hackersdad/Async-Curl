<?php 

require_once 'error_handler.php';

function async_curl($url, $method = 'GET', $data = null, $headers = [], $options = [], $dataFormat = 'json') {

    if(!async_curl_check_log_file_permission()){
        return False;
    }

    // Check for the 'exec' function availability as it is disabled for security reasons in some systemss
    $disabled_functions = ini_get('disable_functions');

    // Check if 'exec' is in the list of disabled functions
    if ($disabled_functions) {
        // Split the string into an array and trim any whitespace
        $disabled_functions_array = array_map('trim', explode(',', $disabled_functions));
        
        // Check if 'exec' is in the array
        if (in_array('exec', $disabled_functions_array)) {
            log_error('"exec" function is disabled. Please enable it and try again', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
            throw new Exception('"exec" function is disabled. Please enable it and try again');
            return false;
        }
    }

    // Get the path to the async-curl-core.php script
    $scriptPath = realpath(dirname(__FILE__) . '/async-curl-core.php');

    // Validate the script path
    if (!$scriptPath) {
        log_error('The async-curl-core.php script path is invalid.', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        throw new Exception('The async-curl-core.php script path is invalid.');
    }

    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        log_error('Invalid URL provided.', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        throw new Exception('Invalid URL provided.');
    }

    // Validate HTTP method
    $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
    $method = strtoupper($method);
    if (!in_array($method, $validMethods)) {
        log_error('Invalid HTTP method provided.', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        throw new Exception('Invalid HTTP method provided.');
    }

    // Validate headers
    if (!is_array($headers)) {
        log_error('Headers should be an array.', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        throw new Exception('Headers should be an array.');
    }

    // Validate options
    if (!is_array($options)) {
        log_error('Options should be an array.', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        throw new Exception('Options should be an array.');
    }

    // Validate data format
    $validDataFormats = ['json', 'form'];
    if (!in_array($dataFormat, $validDataFormats)) {
        log_error('Invalid data format provided.', compact('url', 'method', 'data', 'headers', 'options', 'dataFormat'));
        throw new Exception('Invalid data format provided.');
    }

    // Prepare the parameters array
    $params = [
        'url' => $url,
        'method' => $method,
        'data' => $data,
        'headers' => $headers,
        'options' => $options,
        'data_format' => $dataFormat
    ];

    // Encode the parameters as a JSON string
    $jsonParams = json_encode($params,JSON_UNESCAPED_SLASHES);

    // Check if json_encode succeeded
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'Error encoding JSON parameters: ' . json_last_error_msg();
        log_error($error, $params);
        throw new Exception($error);
        return false;
    }

    // Escape any single quotes in the JSON string to avoid breaking the command
    $encoded_parameters = base64_encode($jsonParams);

    $maxCommandLength = 4096;

    // Construct the command
    $commandLine = sprintf('php %s \'%s\' >> %s 2>&1 & echo "done"', $scriptPath, $encoded_parameters, realpath(dirname(__FILE__)) . '/logs.txt');

    // Check if command exceeds max length
    if (strlen($commandLine) > $maxCommandLength) {
        // Write parameters to a temporary file
        $tempFilePath = realpath(dirname(__FILE__)) . '/temp/temp_params_' . uniqid() . '.json';
        if (file_put_contents($tempFilePath, $jsonParams) === false) {
            $error = 'Failed to write parameters to temporary file.';
            log_error($error, $params);
            return false;
        }

        // Update the command to read from the temporary file
        $commandLine = sprintf('php %s --params_file="%s" >> %s 2>&1 & echo "done"', $scriptPath, $tempFilePath, realpath(dirname(__FILE__)) . '/logs.txt');
    }
        
    // Execute the command
    exec($commandLine, $output, $returnVar);

    // Handle execution output and status
    if ($returnVar !== 0) {
        $error = 'Command execution failed with status ' . $returnVar;
        log_error($error, $output);
        return false;
        // throw new Exception($error);
    }

    return true;
}





?>
