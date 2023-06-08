<?php

namespace App;

const LOGS_PATH = '../logs';

class LogWriter
{
    private $owner;
    private $fields = [];
    private $fileName = '';
    private $fileNamePreset = '';
    private $ownerKey = 0;

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function writeToOutput($content): void
    {
        $outputPath = LOGS_PATH.DIRECTORY_SEPARATOR.'output.txt';
        file_put_contents($outputPath, $content);
    }

    public function getKeyFromFields(string $key): string
    {
        if (!isset($this->fields[$key])) {
            throw new \Exception("Key {$key} not found in fields. Current fields: ".json_encode($this->fields, JSON_PRETTY_PRINT));
        }

        return $this->fields[$key];
    }

    public function assertFieldExists(... $keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->fields[$key])) {
                return false;
            }
        }

        return true;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function appendToFields(array $fields): void
    {
        $this->fields = array_merge($this->fields, $fields);
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getOwnerKey(): string
    {
        return $this->ownerKey;
    }

    public function setOwnerKey($key): void
    {
        $this->ownerKey = $key;
    }

    public function setFileNamePreset(string $preset): void
    {
        $this->fileNamePreset = $preset;
    }

    public function getFilenamePreset(): string
    {
        return $this->fileNamePreset;
    }

    private function buildMessageAndTimestamp(string $messageKey): ?string
    {
        $message = Config::getMessage($messageKey);
    
        if (!$message) {
            return null;
        }
    
        $expectedCount = substr_count($message, '%');
        $fieldValues = array_values($this->getFields());
    
        if (count($fieldValues) < $expectedCount)
            throw new \Exception('Error: Insufficient inputs for message '.$messageKey.'. Expected '.$expectedCount.' inputs');
    
        $extraData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => vsprintf($message, $fieldValues)
        ];
    
        $this->appendToFields($extraData);
    
        return $message;
    }
    
    public function buildFileName(string $messageKey): string
    {
        if (!empty($this->getFileName()))
        {
            return $this->getFileName();
        }

        return $this->getFilenamePreset() ? $this->getFilenamePreset().'_'.$messageKey : $messageKey;
    }

    public function logEvent($messageKey): void
    {
        $this->setOwner();
        $this->buildMessageAndTimestamp($messageKey);
        $this->appendToLogFile($this->buildFileName($messageKey));
    }

    private function appendToLogFile(string $filename): void
    {
        if (!isset($this->owner)) {
            throw new \Exception('Owner not set when trying to write to '. $filename);
        }

        $ownerFolder = LOGS_PATH.DIRECTORY_SEPARATOR.$this->owner;

        if (!is_dir($ownerFolder)) {
            mkdir($ownerFolder, 0777, true);
        }

        $filePath = $ownerFolder.DIRECTORY_SEPARATOR.$filename.'.json';

        $this->appendToFile($filePath);
    }

    private function appendToFile(string $filePath): void
    {
        $existingData = file_exists($filePath) && ($fileContents = file_get_contents($filePath))
            ? json_decode($fileContents, true)
            : [];

        $existingData[] = $this->getFields();
        file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
    }

    public function logGeneralInfo(string $fileName): void
    {
        $filePath = LOGS_PATH.DIRECTORY_SEPARATOR.$fileName.'.json';
        $this->buildMessageAndTimestamp($fileName);
        $this->appendToFile($filePath);
    }

    public function setOwner(): void
    {
        $this->owner = $this->ownerKey ? $this->getKeyFromFields($this->ownerKey) : reset($this->fields);
    }    
}
