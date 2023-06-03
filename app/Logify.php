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
    
    private function processGMActions()
    {
        if (!preg_match('/GM:(\d+)/', $this->logLine, $matches))
            return;

        $gmActionsMethods = [
            '创建了' => 'handleCreateMonster',
            '试图移动到玩家' => 'handleAttemptMoveToPlayer',
            '将玩家' => 'handleMovePlayer',
            '激活了生成区域' => 'handleActivateTrigger',
            '取消了生成区域' => 'handleCancelTrigger',
            '开启活动' => 'handleStartActivity',
            '关闭活动' => 'handleStopActivity',
            '切换了无敌状态' => 'handleToggleInvincibility',
            '切换了隐身状态' => 'handleToggleInvisibility',
            '丢出了怪物生成器' => 'handleDropMonsterSpawner',
            '用户断线了' => 'handlePlayerDisconnect',
        ];

        foreach ($gmActionsMethods as $pattern => $methodName) {
            if (!strpos($this->logLine, $pattern) !== false)
                $this->$methodName($matches[1]);
        }
    }

    private function handleStartActivity($gmRoleId)
    {
        if (!preg_match('/开启活动(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ];

        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'startActivity', 'gm_startActivity.json');
    }

    private function handleStopActivity($gmRoleId)
    {
        if (!preg_match('/关闭活动(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ];

        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'stopActivity', 'gm_stopActivity.json');
    }

    private function handleToggleInvincibility($gmRoleId)
    {
        if (!preg_match('/切换了无敌状态\(([^)]+)\)/', $this->logLine, $matches))
            return;
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '正常' ? 0 : 1,
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'toggleInvincibility', 'gm_toggleInvincibility.json');
    }    

    private function handleToggleInvisibility($gmRoleId)
    {
        if (!preg_match('/切换了隐身状态\(([^)]+)\)/', $this->logLine, $matches))
            return;
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '现形' ? 0 : 1,
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'toggleInvisibility', 'gm_toggleInvisibility.json');
    }    

    private function handleDropMonsterSpawner($gmRoleId)
    {
        if (!preg_match('/丢出了怪物生成器(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'gmRoleId' => $gmRoleId,
            'monsterSpawnerId' => $matches[1],
        ];

        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'dropMonsterSpawner', 'gm_dropMonsterSpawner.json');
    }

    private function handlePlayerDisconnect($gmRoleId)
    {
        if (!preg_match('/用户断线了\((\d+)\):(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'gmRoleId' => $gmRoleId,
            'disconnectType' => $matches[1],
            'playerId' => $matches[2],
        ];

        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'playerDisconnect', 'gm_playerDisconnect.json');
    }


    private function handleActivateTrigger($gmRoleId)
    {
        if (!preg_match('/激活了生成区域(\d+)/', $this->logLine, $matches))
            return;
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'activateTrigger', 'gm_activateTrigger.json');
    }
    
    private function handleCancelTrigger($gmRoleId)
    {
        if (!preg_match('/取消了生成区域(\d+)/', $this->logLine, $matches))
            return;
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'cancelTrigger', 'gm_cancelTrigger.json');
    }
    
    private function handleCreateMonster($gmRoleId)
    {
        if (!preg_match('/创建了(\d+)个怪物(\d+)\((\d+)\)/', $this->logLine, $matches))
            return;

        $fields = [
            'gmRoleId' => $gmRoleId,
            'monsterCount' => $matches[1],
            'monsterType' => $matches[2],
            'monsterId' => $matches[3],
        ];

        $this->logWriter->setOwner($gmRoleId);
        $this->logWriter->logEvent($fields, 'createMonster', 'gm_createMonster.json');
    }
    
    private function handleAttemptMoveToPlayer($gmRoleId)
    {
        preg_match('/试图移动到玩家(\d+)/', $this->logLine, $matches);
        if (count($matches) != 2) {
            return;
        }
    
        $playerId = $matches[1];
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'playerId' => $playerId,
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'attemptMoveToPlayer', 'gm_attemptMoveToPlayer.json');
    }
    
    private function handleMoveToPlayer($gmRoleId)
    {
        preg_match('/移动到玩家(\d+) at position \((.+)\)/', $this->logLine, $matches);
        if (count($matches) != 3) {
            return;
        }
    
        $playerId = $matches[1];
        $position = explode(',', $matches[2]);
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'playerId' => $playerId,
            'positionX' => floatval($position[0]),
            'positionY' => floatval($position[1]),
            'positionZ' => floatval($position[2]),
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'moveToPlayer', 'gm_moveToPlayer.json');
    }
    
    private function handleMovePlayer($gmRoleId)
    {
        if(!preg_match('/将玩家(\d+)移动过来\((.+)\)/', $this->logLine, $matches))
            return;
    
        $position = explode(',', $matches[2]);
    
        $fields = [
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
            'positionX' => floatval($position[0]),
            'positionY' => floatval($position[1]),
            'positionZ' => floatval($position[2]),
        ];
    
        $this->logWriter->setOwner($fields['gmRoleId']);
        $this->logWriter->logEvent($fields, 'movePlayer', 'gm_movePlayer.json');
    }    

    private function processMine()
    {
        if (!preg_match('/用户(\d+)采集得到(\d+)个(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'itemCount' => $matches[2],
            'itemId' => $matches[3],
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'mine', 'mine.json');
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
        $this->logWriter->logEvent($fields, 'getMoney', 'getMoney.json');
    }

    private function processPetEggHatch()
    {
        if (!preg_match('/用户(\d+)孵化了宠物蛋(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ];

        $this->logWriter->setOwner($fields['userId']);
        $this->logWriter->logEvent($fields, 'petHatch', 'petHatch.json');
    }

    private function processPetEggRestore()
    {
        if (!preg_match('/用户(\d+)还原了宠物蛋(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ];

        $this->logWriter->setOwner($fields['userId']);
        $this->logWriter->logEvent($fields, 'petRestore', 'petRestore.json');
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

    private function processChat()
    {
        if (preg_match('/(Whisper|Chat): src=(-?\d+) (dst=(\d+) )?chl=(\d+) msg=([\w\+=\/]+)/', $this->logLine, $matches)) {
            $chatType = $matches[1];
            $srcRoleId = $matches[2];
            $dstRoleId = $matches[1] === 'Whisper' ? $matches[4] : null;
            $channel = $this->getChannelName($matches[5]);
            $message = $this->decodeBase64Message($matches[6]);

            $fields = [
                'chatType' => $chatType,
                'srcRoleId' => $srcRoleId,
                'dstRoleId' => $dstRoleId,
                'channel' => $channel,
                'message' => $message,
            ];

            $this->logWriter->setOwner($fields['srcRoleId']);
            $this->logWriter->logEvent($fields, null, 'chat.json');
        }
    }

    private function getChannelName($channelId)
    {
        $channelNames = [
            0 => 'Common',
            1 => 'World',
            2 => 'Squad',
            7 => 'Trade',
            9 => 'System'
        ];

        return $channelNames[$channelId] ?? 'unknown';
    }

    private function decodeBase64Message($base64Message)
    {
        $decodedMessage = base64_decode($base64Message);
        return mb_convert_encoding($decodedMessage, 'UTF-8', 'UTF-16LE');
    }

    private function processCraftItem()
    {
        if (!preg_match('/用户(\d+)制造了(\d+)个(\d+), 配方(\d+),/', $this->logLine, $matches))
            return;
    
        $materialsString = substr($this->logLine, strpos($this->logLine, "消耗材料"));
        $materialMatches = [];
        preg_match_all('/(消耗材料|材料)(\d+), 数量(\d+);/', $materialsString, $materialMatches, PREG_SET_ORDER);
    
        $materials = [];
        foreach($materialMatches as $match) {
            $materials[] = "Material {$match[2]}, Quantity {$match[3]}";
        }
        $materialsString = implode("; ", $materials);

        $fields = [
            'roleId' => $matches[1],
            'itemCount' => $matches[2],
            'itemId' => $matches[3],
            'recipeId' => $matches[4],
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

    private function processGMCommand()
    {
        if (!preg_match('/GM:用户(\d+)执行了内部命令(\d+)/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'commandId' => $matches[2],
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'gmCommand', 'gm_executeCommand.json');
    }

    private function processObtainTitle()
    {
        if (!preg_match('/roleid:(\d+) obtain title\[(\d+)\] time\[(\d+)\]/', $this->logLine, $matches))
            return;

        $fields = [
            'roleId' => $matches[1],
            'titleId' => $matches[2],
            'time' => $matches[3]
        ];

        $this->logWriter->setOwner($fields['roleId']);
        $this->logWriter->logEvent($fields, 'obtainTitle', 'obtainTitle.json');
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
    
        if (!isset($fields['roleid']))
            return;

        $this->logWriter->setOwner($fields['roleid']);
        $this->logWriter->logEvent($fields, 'roleLogin', 'rolelogin.json');
    }
    
    private function processRoleLogout()
    {
        $fields = $this->getFormatLogMatches();
    
        if (!isset($fields['roleid']))
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
    $logify = new Logify;
    $logify->setLogLine($argv[1]);
    $logify->processLogLine();
}
else {
    echo "Usage: php logify.php <log line>\n";
}

