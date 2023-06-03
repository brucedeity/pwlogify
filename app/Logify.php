<?php

require '../vendor/autoload.php';

// mb_internal_encoding("GB2312");

use App\Config;
use App\LogWriter;

class Logify
{
    private $logLine;
    private $config;

    public function setLogLine(string $logLine): void
    {
        $this->logLine = $logLine;
        
        $this->logWriter = new LogWriter;
    }

    public function processLogLine()
    {
        foreach (Config::getLogPatterns() as $pattern => $methodName) {
            if (!strpos($this->logLine, $pattern) !== false)
                continue;

            echo "Processing log line: {$this->logLine}\n";

            echo 'calling method: ' . $methodName . "\n";
            $this->$methodName();
        }
    }

    private function getFormatLogMatches()
    {
        $fields = [];
        $matches = [];
        preg_match_all('/(\w+)=([\d\w]+)/', $this->logLine, $matches, PREG_SET_ORDER);
    
        foreach ($matches as $match) {
            if (count($match) == 3) {
                $fields[$match[1]] = $match[2];
            }
        }

        // $fields['timestamp'] = date('Y-m-d H:i:s');
    
        return $fields;
    }

    private function processDropItem()
    {
        $fields = [];
    
        if (preg_match('/用户(\d+)丢弃包裹(\d+)个(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['itemcount'] = $matches[2];
            $fields['item_id'] = $matches[3];
    
            $this->logWriter->setOwner($fields['userid']);
            $this->logWriter->logEvent($fields, 'dropItem', 'dropItem.json');

            echo "Processed log line: {$this->logLine}\n";
        }
        else {
            echo "No match found for log line: {$this->logLine}\n";
        }
    }    

    private function processDropEquipment()
    {
        $fields = [];
    
        if (preg_match('/用户(\d+)丢弃装备(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['item_id'] = $matches[2];
    
            $this->logWriter->logEvent($fields, 'dropEquipment', 'dropEquipment.json');
        }
    }

    private function processDiscardMoney()
    {
        $fields = [];

        if (preg_match('/用户(\d+)丢弃金钱(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['amount'] = $matches[2];

            $this->logWriter->logEvent($fields, 'discardMoney', 'discardmoney.json');
        }
    }

    private function processPickupMoney()
    {
        $fields = [];

        if (preg_match('/拣起金钱(\d+)\W+(\w+)/', $this->logLine, $matches)) {
            $fields['role'] = $matches[2];
            $fields['amount'] = $matches[1];

            $this->logWriter->logEvent($fields, 'pickupMoney', 'pickupmoney.json');
        }
    }

    private function processBuyItem()
    {
        $fields = [];

        if (preg_match('/用户(\d+).*从NPC购买了(\d+)个(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['quantity'] = $matches[2];
            $fields['item_id'] = $matches[3];

            $this->logWriter->setOwner($fields['userid']);
            $this->logWriter->logEvent($fields, 'buyItem', 'buyitem.json');
        }
    }

    private function processSellItem()
    {
        $fields = [];

        if (preg_match('/用户(\d+).*卖店(\d+)个(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['quantity'] = $matches[2];
            $fields['item_id'] = $matches[3];

            $this->logWriter->setOwner($fields['userid']);
            $this->logWriter->logEvent($fields, 'sellItem', 'sellitem.json');
        }
    }

    private function processGetMoney()
    {
        $fields = [];

        if (preg_match('/用户(\d+).*得到金钱(\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['amount'] = $matches[2];

            $this->logWriter->setOwner($fields['userid']);
            $this->logWriter->logEvent($fields, 'getMoney', 'getmoney.json');
        }
    }

    private function processUserLevelUp()
    {
        $fields = [];

        if (preg_match('/用户(\d+)升级到(\d+)级金钱(\d+),游戏时间(\d+:\d+:\d+)/', $this->logLine, $matches)) {
            $fields['userid'] = $matches[1];
            $fields['level'] = $matches[2];
            $fields['money'] = $matches[3];
            $fields['playtime'] = $matches[4];

            $this->logWriter->setOwner($fields['userid']);
            $this->logWriter->logEvent($fields, 'userLevelUp', 'userlevelup.json');
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

            $this->logWriter->setOwner($fields['pickup_userid']);
            $this->logWriter->logEvent($fields, 'pickupItem', 'pickupItem.json');
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

            $this->logWriter->setOwner($fields['userid']);
            $this->logWriter->logEvent($fields, 'purchaseFromAuction', 'purchaseFromAuction.json');
        }
    }

    private function processTask()
    {
        $fields = $this->getFormatLogMatches();
    
        if (isset($fields['roleid']) && isset($fields['taskid']) && isset($fields['type'])) {
            switch ($fields['msg']) {
                case 'CheckDeliverTask':
                    $this->logWriter->logEvent($fields, 'processStartTask', 'processStartTask.json');
                    break;
                case 'GiveUpTask':
                    $this->logWriter->logEvent($fields, 'processGiveUpTask', 'processGiveUpTask.json');
                    break;
                case 'DeliverItem':
                    preg_match('/Item id = (\d+), Count = (\d+)/', $this->logLine, $matches);
                    $fields['itemid'] = $matches[1];
                    $fields['count'] = $matches[2];
                    $this->logWriter->logEvent($fields, 'receiveItemFromTask', 'receiveItemFromTask.json');
                    break;
                case 'DeliverByAwardData':
                    preg_match('/success = (\d+), gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/', $this->logLine, $matches);
                    $fields['success'] = $matches[1];
                    $fields['gold'] = $matches[2];
                    $fields['exp'] = $matches[3];
                    $fields['sp'] = $matches[4];
                    $fields['reputation'] = $matches[5];
                    $this->logWriter->logEvent($fields, 'deliverByAwardData', 'deliverByAwardData.json');
                    break;
                default:
                    // Other task types can be handled here
                    break;
            }
        }
    }

    private function processSendMail()
    {
        $fields = $this->getFormatLogMatches();

        if (isset($fields['src'])) {
            $this->logWriter->setOwner($fields['src']);
            $this->logWriter->logEvent($fields, 'processSendMail', 'sendmail.json');
        }
    }
        
    private function processRoleLogin()
    {
        $fields = $this->getFormatLogMatches();
    
        if (isset($fields['userid']) && isset($fields['roleid'])) {

            $this->logWriter->setOwner($fields['roleid']);
            $this->logWriter->logEvent($fields, 'roleLogin', 'rolelogin.json');
        }
    }
    
    private function processRoleLogout()
    {
        $fields = $this->getFormatLogMatches();
    
        if (isset($fields['userid']) && isset($fields['roleid'])) {

            $this->logWriter->setOwner($fields['roleid']);
            $this->logWriter->logEvent($fields, 'roleLogout', 'rolelogout.json');
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
                Config::getMessage('trade'),
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
            
            $this->logWriter->appendToLogFile('trade.json', $fields);
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

