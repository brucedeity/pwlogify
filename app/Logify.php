<?php

require '../vendor/autoload.php';

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
    
        return $fields;
    }

    private function processDropItem()
    {
        if (!preg_match('/用户(\d+)丢弃包裹(\d+)个(\d+)/', $this->logLine, $matches))
            return;
        
        $fields = [
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3]
        ];
    
        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'dropItem', 'dropItem.json');
    }   

    private function processDropEquipment()
    {
        if (!preg_match('/用户(\d+)丢弃装备(\d+)/', $this->logLine, $matches))
            return;
    
        $fields = [
            'roleId' => $matches[1],
            'itemId' => $matches[2]
        ];
    
        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'dropEquipment', 'dropEquipment.json');
    }

    private function processDiscardMoney()
    {
        if (!preg_match('/用户(\d+)丢弃金钱(\d+)/', $this->logLine, $matches))
            return;
    
        $fields = [
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ];
    
        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'discardMoney', 'discardmoney.json');
    }

    private function processSpendMoney()
    {
        if (!preg_match('/用户(\d+)花掉金钱(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'spendMoney' => $matches[2],
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'spendMoney', 'spendMoney.json');
    }

    private function processPickupMoney()
    {
        if (!preg_match('/拣起金钱(\d+)\W+(\w+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[2],
            'amount' => $matches[1]
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'pickupMoney', 'pickupmoney.json');
    }

    private function processBuyItem()
    {
        if (!preg_match('/用户(\d+).*从NPC购买了(\d+)个(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'buyItem', 'buyitem.json');
    }

    private function processSellItem()
    {
        if (!preg_match('/用户(\d+).*卖店(\d+)个(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'sellItem', 'sellitem.json');
    }

    private function processSpConsume()
    {
        if (!preg_match('/用户(\d+)消耗了sp (\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'spAmount' => $matches[2],
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'spConsume', 'spConsume.json');
    }

    private function processSkillLevelUp()
    {
        if (!preg_match('/用户(\d+)技能(\d+)达到(\d+)级/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'skillId' => $matches[2],
            'skillLevel' => $matches[3],
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'skillLevelUp', 'skillLevelUp.json');
    }

    private function processRoleDie()
    {
        if (!isset($fields['roleid']))
            return;

        $fields = $this->getFormatLogMatches();

        $this->logWriter->setOwner($fields['roleid']);
        $this->logWriter->logEvent($fields, 'roleDie', 'die.json');
    }

    private function processGetMoney()
    {
        if (!preg_match('/用户(\d+).*得到金钱(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'getMoney', 'getmoney.json');
    }

    private function processLevelUp()
    {
        if (!preg_match('/用户(\d+)升级到(\d+)级金钱(\d+),游戏时间(\d+:\d+:\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'level' => $matches[2],
            'money' => number_format($matches[3], 0, ',', '.'),
            'playtime' => $matches[4]
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'levelUp', 'levelup.json');
    }

    private function processCraftItem()
    {
        if (!preg_match('/用户(\d+)制造了(\d+)个(\d+), 配方(\d+),/', $this->logLine, $matches)) {
            return;
        }
    
        $roleId = $matches[1];
        $itemCount = $matches[2];
        $itemId = $matches[3];
        $recipeId = $matches[4];
    
        $materialsString = substr($this->logLine, strpos($this->logLine, "消耗材料"));
        $materialMatches = [];
        preg_match_all('/(消耗材料|材料)(\d+), 数量(\d+);/', $materialsString, $materialMatches, PREG_SET_ORDER);
    
        $materials = [];
        foreach($materialMatches as $match) {
            $materials[] = "Material {$match[2]}, Quantity {$match[3]}";
        }
        $materialsString = implode("; ", $materials);
    
        $fields = [
            'roleId' => $roleId,
            'itemCount' => $itemCount,
            'itemId' => $itemId,
            'recipeId' => $recipeId,
            'materials' => $materialsString
        ];
    
        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'craftItem', 'craftItem.json');
    }       
    
    private function processPickupItem()
    {
        if (!preg_match('/用户(\d+)拣起(\d+)个(\d+)\[用户(\d+)丢弃\]/', $this->logLine, $matches))
            return;

        $fields = [
            'pickup_userid' => $matches[1],
            'itemcount' => $matches[2],
            'itemcode' => $matches[3],
            'discard_userid' => $matches[4]
        ];

        $this->logWriter->setOwner($fields['pickup_userid']);
        $this->logWriter->logEvent($fields, 'pickupItem', 'pickupItem.json');
    }

    private function processPurchaseFromAuction()
    {
        if (!preg_match('/用户(\d+)在百宝阁购买(\d+)样物品，花费(\d+)点剩余(\d+)点/', $this->logLine, $matches))
            return;
            
        $fields = [
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'cost' => $matches[3] / 100,
            'balance' => $matches[4] / 100
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'purchaseFromAuction', 'gshopBuy.json');
    }

    private function processTask()
    {
        $fields = $this->getFormatLogMatches();

        if (!isset($fields['roleid']) OR !isset($fields['taskid']) OR !isset($fields['type']))
            return;
            
        $this->logWriter->setOwner($fields['roleid']);

        switch ($fields['msg']) {
            case 'CheckDeliverTask':
                $this->logWriter->logEvent($fields, 'processStartTask', 'startTask.json');
                break;
            case 'GiveUpTask':
                $this->logWriter->logEvent($fields, 'processGiveUpTask', 'giveUpTask.json');
                break;
            case 'DeliverItem':
                preg_match('/Item id = (\d+), Count = (\d+)/', $this->logLine, $matches);
                $fields['itemid'] = $matches[1];
                $fields['count'] = $matches[2];
                $this->logWriter->logEvent($fields, 'receiveItemFromTask', 'itemFromTask.json');
                break;
            case 'DeliverByAwardData':
                preg_match('/success = (\d+), gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/', $this->logLine, $matches);
                $fields['success'] = $matches[1];
                $fields['gold'] = $matches[2];
                $fields['exp'] = $matches[3];
                $fields['sp'] = $matches[4];
                $fields['reputation'] = $matches[5];
                $this->logWriter->logEvent($fields, 'deliverByAwardData', 'completeTask.json');
                break;
        }
    }

    private function processSendMail()
    {
        $fields = $this->getFormatLogMatches();

        if (!isset($fields['src']))
            return;

        $this->logWriter->setOwner($fields['src']);
        $this->logWriter->logEvent($fields, 'processSendMail', 'sendmail.json');
    }
        
    private function processRoleLogin()
    {
        $fields = $this->getFormatLogMatches();
    
        if (!isset($fields['roleId']) OR !isset($fields['roleid']))
            return;

        $this->logWriter->setOwner($fields['roleid']);
        $this->logWriter->logEvent($fields, 'roleLogin', 'rolelogin.json');
    }
    
    private function processRoleLogout()
    {
        $fields = $this->getFormatLogMatches();
    
        if (!isset($fields['roleId']) OR !isset($fields['roleid']))
            return;

        $this->logWriter->setOwner($fields['roleid']);
        $this->logWriter->logEvent($fields, 'roleLogout', 'rolelogout.json');
    }

    private function processTrade()
    {
        if (!preg_match('/roleidA=(\d+):roleidB=(\d+):moneyA=(\d+):moneyB=(\d+):objectsA=([^:]*):objectsB=(.*)$/', $this->logLine, $matches))
            return;

        $fields = [
            'roleA_id' => $matches[1],
            'roleB_id' => $matches[2],
            'moneyA' => $matches[3],
            'moneyB' => $matches[4]
        ];

        $objectsA = $this->parseTradeObjets($matches[5], $fields, 'itemsA');
        $objectsB = $this->parseTradeObjets($matches[6], $fields, 'itemsB');

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

    private function parseTradeObjets(string $objects, array &$fields, string $itemsKey)
    {
        $items = [];
        $objects = explode(';', $objects);
        foreach ($objects as $object) {
            if (!preg_match('/(\d+),(\d+),(\d+)/', $object, $item))
                continue;
                
            $items[] = ['itemId' => $item[1], 'quantity' => $item[2], 'position' => $item[3]];
        }

        if (!array_key_exists($itemsKey, $fields))
            throw new Exception("Key {$itemsKey} does not exist in fields array");

        if (!empty($items))
            $fields[$itemsKey] = $items;

        return $items;
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

