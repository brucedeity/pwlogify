<?php

mb_internal_encoding("GB2312");


class Logify
{
    private $logLine;
    private $config;

    public function __construct()
    {
        $this->config = require 'configs.php';
    }

    public function setLogLine(string $logLine)
    {
        $this->logLine = $logLine;
    }

    public function processLogLine()
    {
        foreach ($this->config['log_patterns'] as $pattern => $methodName) {
            if (strpos($this->logLine, $pattern) !== false) {
                echo "Matched pattern {$pattern}\n";

                echo "Calling method {$methodName}\n";
    
                $this->$methodName();
                break;
            }
            else {
                // echo "No match for pattern {$pattern}\n";
            }
        }
    }

    private function appendToLogFile($filename, $data)
    {
        $filePath = "../../logs/{$filename}";
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

    private function parseFormatLogLine()
    {
        $fields = [];
        $matches = [];
        preg_match_all('/(\w+)=([\d\w]+)/', $this->logLine, $matches, PREG_SET_ORDER);
    
        foreach ($matches as $match) {
            if (count($match) == 3) {
                $fields[$match[1]] = $match[2];
            }
        }

        $fields['timestamp'] = date('Y-m-d H:i:s');
    
        return $fields;
    }

    private function logEvent($fields, $messageKey, $outputFilename)
    {
        $message = $this->config['messages'][$messageKey];
        $formattedMessage = sprintf($message, ...array_values($fields));
        
        $fields['message'] = $formattedMessage;
        $fields['timestamp'] = date('Y-m-d H:i:s');
        
        $this->appendToLogFile($outputFilename, $fields);
    }

    private function processDropItem()
    {
        $fields = [];
    
        if (preg_match('/用户(\d+)丢弃包裹(\d+)个(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['itemcount'] = $matches[2];
            $fields['item_id'] = $matches[3];
    
            $this->logEvent($fields, 'dropItem', 'dropItem.json');
        }
    }    

    private function processDropEquipment()
    {
        $fields = [];
    
        if (preg_match('/用户(\d+)丢弃装备(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['item_id'] = $matches[2];
    
            $this->logEvent($fields, 'dropEquipment', 'dropEquipment.json');
        }
    }

    private function processDiscardMoney()
    {
        $fields = [];

        if (preg_match('/用户(\d+)丢弃金钱(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['amount'] = $matches[2];

            $this->logEvent($fields, 'discardMoney', 'discardmoney.json');
        }
    }

    private function processPickupMoney()
    {
        $fields = [];

        if (preg_match('/拣起金钱(\d+)\W+(\w+)/', $this->logLine, $matches)) {
            $fields['role'] = $matches[2];
            $fields['amount'] = $matches[1];

            $this->logEvent($fields, 'pickupMoney', 'pickupmoney.json');
        }
    }

    private function processBuyItem()
    {
        $fields = [];

        if (preg_match('/用户(\d+).*从NPC购买了(\d+)个(\d+)/', $this->logLine, $matches)) {
            $fields['user_id'] = $matches[1];
            $fields['quantity'] = $matches[2];
            $fields['item_id'] = $matches[3];

            $this->logEvent($fields, 'buyItem', 'buyitem.json');
        }
    }

    private function processSellItem()
    {
        $fields = [];

        if (preg_match('/用户(\d+).*卖店(\d+)个(\d+)/', $this->logLine, $matches)) {
            $fields['user_id'] = $matches[1];
            $fields['quantity'] = $matches[2];
            $fields['item_id'] = $matches[3];

            $this->logEvent($fields, 'sellItem', 'sellitem.json');
        }
    }

    private function processGetMoney()
    {
        $fields = [];

        if (preg_match('/用户(\d+).*得到金钱(\d+)/', $this->logLine, $matches)) {
            $fields['user_id'] = $matches[1];
            $fields['amount'] = $matches[2];

            $this->logEvent($fields, 'getMoney', 'getmoney.json');
        }
    }

    private function processPickupItem()
    {
        $fields = [];

        if (preg_match('/用户(\d+)拣起(\d+)个(\d+)\[用户(\d+)丢弃\]/', $this->logLine, $matches)) {
            $fields['pickup_userid'] = $matches[1];
            $fields['itemcount'] = $matches[2];
            $fields['itemcode'] = $matches[3];
            $fields['discard_userid'] = $matches[4];

            $this->logEvent($fields, 'pickupItem', 'pickupItem.json');
        }
    }

    private function processPurchaseFromAuction()
    {
        $fields = [];

        if (preg_match('/用户(\d+)在百宝阁购买(\d+)样物品，花费(\d+)点剩余(\d+)点/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['itemcount'] = $matches[2];
            $fields['cost'] = $matches[3];
            $fields['balance'] = $matches[4];

            $this->logEvent($fields, 'purchaseFromAuction', 'purchaseFromAuction.json');
        }
    }

    private function processSendMail()
    {
        // Process the sendmail log line
    }

    private function processTask()
    {
        $fields = $this->parseFormatLogLine();
    
        if (isset($fields['roleid']) && isset($fields['taskid']) && isset($fields['type'])) {
            switch ($fields['msg']) {
                case 'CheckDeliverTask':
                    $this->logEvent($fields, 'processStartTask', 'processStartTask.json');
                    break;
                case 'GiveUpTask':
                    $this->logEvent($fields, 'processGiveUpTask', 'processGiveUpTask.json');
                    break;
                case 'DeliverItem':
                    preg_match('/Item id = (\d+), Count = (\d+)/', $this->logLine, $matches);
                    $fields['itemid'] = $matches[1];
                    $fields['count'] = $matches[2];
                    $this->logEvent($fields, 'receiveItemFromTask', 'receiveItemFromTask.json');
                    break;
                case 'DeliverByAwardData':
                    preg_match('/success = (\d+), gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/', $this->logLine, $matches);
                    $fields['success'] = $matches[1];
                    $fields['gold'] = $matches[2];
                    $fields['exp'] = $matches[3];
                    $fields['sp'] = $matches[4];
                    $fields['reputation'] = $matches[5];
                    $this->logEvent($fields, 'deliverByAwardData', 'deliverByAwardData.json');
                    break;
                default:
                    // Other task types can be handled here
                    break;
            }
        }
    }
        

    private function processRoleLogin()
    {
        $fields = $this->parseFormatLogLine();
    
        if (isset($fields['userid']) && isset($fields['roleid'])) {

            $this->logEvent($fields, 'roleLogin', 'rolelogin.json');
        }
    }
    

    private function processRoleLogout()
    {
        $fields = $this->parseFormatLogLine();
    
        if (isset($fields['userid']) && isset($fields['roleid'])) {

            $this->logEvent($fields, 'roleLogout', 'rolelogout.json');
        }
    }

    private function processTrade()
    {
        $fields = [];
        if (preg_match('/roleidA=(\d+):roleidB=(\d+):moneyA=(\d+):moneyB=(\d+):objectsA=([^:]*):objectsB=(.*)$/', $this->logLine, $matches)) {
            $fields['roleA_id'] = $matches[1];
            $fields['roleB_id'] = $matches[2];
            $fields['moneyA'] = $matches[3];
            $fields['moneyB'] = $matches[4];
    
            $objectsA = explode(';', $matches[5]);
            $itemsA = [];
            foreach ($objectsA as $object) {
                if (preg_match('/(\d+),(\d+),(\d+)/', $object, $item)) {
                    $itemsA[] = [
                        'item_id' => $item[1],
                        'quantity' => $item[2],
                        'position' => $item[3]
                    ];
                }
            }
            if (!empty($itemsA)) {
                $fields['itemsA'] = $itemsA;
            }
    
            $objectsB = explode(';', $matches[6]);
            $itemsB = [];
            foreach ($objectsB as $object) {
                if (preg_match('/(\d+),(\d+),(\d+)/', $object, $item)) {
                    $itemsB[] = [
                        'item_id' => $item[1],
                        'quantity' => $item[2],
                        'position' => $item[3]
                    ];
                }
            }
            if (!empty($itemsB)) {
                $fields['itemsB'] = $itemsB;
            }
    
            $message = sprintf(
                $this->config['messages']['trade'],
                $fields['roleA_id'],
                $fields['roleB_id'],
                $fields['moneyA'],
                $fields['roleA_id'],
                $fields['moneyB'],
                $fields['roleB_id'],
                $fields['roleA_id'],
                !empty($itemsA) ? count($itemsA) : 0,
                $fields['roleB_id'],
                !empty($itemsB) ? count($itemsB) : 0
            );
    
            $fields['message'] = $message;
            $fields['timestamp'] = date('Y-m-d H:i:s');
            $this->appendToLogFile('trade.json', $fields);
        }
    }
    
    

}

if ($argc > 1) {
    // echo "Processing log line: {$argv[1]}\n";
    $logify = new Logify();
    $logify->setLogLine($argv[1]);
    $logify->processLogLine();
}
else {
    echo "Usage: php logify.php <log line>\n";
}

