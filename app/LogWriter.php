<?php

namespace App;

const LOGS_PATH = '../logs';

class LogWriter
{
    private $owner;

    private function buildMessageAndTimestamp(array &$fields, string $messageKey): string
    {
        $message = Config::getMessage($messageKey);

        $fields['timestap'] = date('Y-m-d H:i:s');
        $fields['message'] = sprintf($message, ...array_values($fields));

        return $message;
    }

    public function logEvent(array $fields, $messageKey, string $outputFilename)
    {
        if ($messageKey)
            $message = $this->buildMessageAndTimestamp($fields, $messageKey);
        
        $this->appendToLogFile($outputFilename, $fields);
    }

    public function appendToLogFile(string $filename, array $data)
    {
        if (!isset($this->owner))
        {
            throw new \Exception('Owner not set when trying to write to '. $filename);
        }
    
        $ownerFolder = LOGS_PATH.'/'.$this->owner;

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

    public function logGeneralInfo(string $fileName, array $fields)
    {
        $filePath = LOGS_PATH.'/'.$fileName.'.json';

        $existingData = [];

        $this->buildMessageAndTimestamp($fields, $fileName);

        if (file_exists($filePath)) {
            $fileContents = file_get_contents($filePath);
            if (!empty($fileContents)) {
                $existingData = json_decode($fileContents, true);
            }
        }
    
        $existingData[] = $fields;
        file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
    }
    
    public function setOwner(int $roleId)
    {
        $this->owner = $roleId;
    }
}