<?php

function log_error($message, $params = []) {
    $logFilePath = realpath(dirname(__FILE__)) . '/logs.txt';
    
    // Get the current date and time
    $dateTime = date('Y-m-d H:i:s');

    // Convert parameters array to JSON
    $paramsJson = json_encode($params);

    // Check if the log file is empty and create columns if necessary
    if (!file_exists($logFilePath) || filesize($logFilePath) == 0) {
        $header = "Date and Time | Parameters and Error Details\n";
        $separator = "---------------------------------------------------------------\n";
        file_put_contents($logFilePath, $header . $separator, FILE_APPEND);
    }

    // Construct the log message
    $logMessage = sprintf("Script Log : %s | Message - %s: Parameters - %s\n---------------------------------------------------------------\n", $dateTime, $message, $paramsJson);

    // Append the log message to the log file
    file_put_contents($logFilePath, $logMessage, FILE_APPEND);
}

function async_curl_check_log_file_permission() {
    $filePath = realpath(dirname(__FILE__)) . '/logs.txt';
    if (!file_exists($filePath)) {
        $handle = fopen($filePath, 'w');
        if($handle) {
            fclose($handle);
        }
        else{
            throw new Exception('Log File Absent. Unable to create the log file.');
            return False;
        }
        
    }

    if(is_readable($filePath) && is_writable($filePath)) {
        return True;
    }
    else{
        throw new Exception('Please check read/write permission for log.txt file. Unable to perform read/write action.');
        return False;
    }
           
}


?>
