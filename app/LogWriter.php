<?php

namespace App;

class LogWriter
{
    public static function logEvent($fields, $messageKey, $outputFilename)
    {
        $message = Config::getMessage($messageKey);
        $formattedMessage = sprintf($message, ...array_values($fields));
        
        $fields['message'] = $formattedMessage;
        $fields['timestamp'] = date('Y-m-d H:i:s');
        
        self::appendToLogFile($outputFilename, $fields);
    }

    public static function appendToLogFile($filename, $data)
    {
        $filePath = "../logs/{$filename}";
        $existingData = [];

        if (file_exists($filePath)) {
            $fileContents = file_get_contents($filePath);
            if (!empty($fileContents)) {
                $existingData = json_decode($fileContents, true);
            }
        }

        $existingData[] = $data;
        file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
    }
}