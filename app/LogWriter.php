<?php

namespace App;

class LogWriter
{
    private $owner;

    public function logEvent($fields, $messageKey, $outputFilename)
    {
        if ($messageKey) {
            $message = Config::getMessage($messageKey);
            $formattedMessage = sprintf($message, ...array_values($fields));
    
            $fields['message'] = $formattedMessage;
        }
        
        $fields['timestamp'] = date('Y-m-d H:i:s');
    
        $this->appendToLogFile($outputFilename, $fields);
    }

    public function appendToLogFile($filename, $data)
    {
        if (!$this->owner)
            throw new \Exception('Owner not set when trying to write to '. $filename);
    
        $ownerFolder = "../logs/{$this->owner}";

        if (!is_dir($ownerFolder))
            mkdir($ownerFolder, 0777, true);
    
        $filePath = "{$ownerFolder}/{$filename}";
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
    
    public function setOwner(int $roleId)
    {
        $this->owner = $roleId;
    }
}