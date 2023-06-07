<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config;
use App\LogWriter;

class Logify
{
    private $logLine;
    private $methodName;
    private $buildMessage = true;

    public function setLogLine(string $logLine)
    {        
        $this->logLine = $logLine;
        
        $this->logWriter = new LogWriter;
    }

    public function setMethodName(string $methodName)
    {
        $this->methodName = $methodName;
    }

    public function getBuildMessage(): bool
    {
        return $this->buildMessage;
    }

    public function setBuildMessage(bool $status)
    {
        $this->buildMessage = $status;
    }

    public function getLogWriter(): LogWriter
    {
        return $this->logWriter;
    }

    public function processLogLine(): bool
    {
        foreach (Config::getLogPatterns() as $pattern => $methodName) {
            if (strpos($this->logLine, $pattern) !== false){

                $this->setMethodName($methodName);
                return $this->$methodName();
            }
        }

        return false;
    }
    
    private function getFieldsFromFormatlog()
    {
        $matches = [];
        preg_match_all('/(\w+)=([\d\w]+)/', $this->logLine, $matches, PREG_SET_ORDER);
    
        foreach ($matches as $match) {
            if (count($match) == 3) {
                $this->fields[$match[1]] = $match[2];
            }
        }
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
            if (strpos($this->logLine, $pattern) !== false){
                $this->$methodName($matches[1]);
                return;
            }
        }
    }

    private function handleStartActivity($gmRoleId)
    {
        if (!preg_match('/开启活动(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ]);

        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }

    private function handleStopActivity($gmRoleId)
    {
        if (!preg_match('/关闭活动(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ]);

        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }

    private function handleToggleInvincibility($gmRoleId)
    {
        if (!preg_match('/切换了无敌状态\(([^)]+)\)/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '正常' ? 0 : 1,
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }    

    private function handleToggleInvisibility($gmRoleId)
    {
        if (!preg_match('/切换了隐身状态\(([^)]+)\)/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '现形' ? 0 : 1,
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }    

    private function handleDropMonsterSpawner($gmRoleId)
    {
        if (!preg_match('/丢出了怪物生成器(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'monsterSpawnerId' => $matches[1],
        ]);

        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }

    private function handlePlayerDisconnect($gmRoleId)
    {
        if (!preg_match('/用户断线了\((\d+)\):(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'disconnectType' => $matches[1],
            'playerId' => $matches[2],
        ]);

        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }

    private function handleActivateTrigger($gmRoleId)
    {
        if (!preg_match('/激活了生成区域(\d+)/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }
    
    private function handleCancelTrigger($gmRoleId)
    {
        if (!preg_match('/取消了生成区域(\d+)/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }
    
    private function handleCreateMonster($gmRoleId)
    {
        if (!preg_match('/创建了(\d+)个怪物(\d+)\((\d+)\)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'monsterCount' => $matches[1],
            'monsterType' => $matches[2],
            'monsterId' => $matches[3],
        ]);

        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }
    
    private function handleAttemptMoveToPlayer($gmRoleId)
    {
        if (!preg_match('/试图移动到玩家(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }
    
    private function handleMoveToPlayer($gmRoleId)
    {
        if(!preg_match('/移动到玩家(\d+) at position \((.+)\)/', $this->logLine, $matches))
            return;

        $position = explode(',', $matches[2]);
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
            'positionX' => floatval($position[0]),
            'positionY' => floatval($position[1]),
            'positionZ' => floatval($position[2]),
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }
    
    private function handleMovePlayer($gmRoleId)
    {
        if(!preg_match('/将玩家(\d+)移动过来\((.+)\)/', $this->logLine, $matches))
            return;
    
        $position = explode(',', $matches[2]);
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
            'positionX' => floatval($position[0]),
            'positionY' => floatval($position[1]),
            'positionZ' => floatval($position[2]),
        ]);
    
        $this->setOwnerAndLogEvent($this->fields, __METHOD__, 'gm');
    }    

    private function processMine()
    {
        if (!preg_match('/用户(\d+)采集得到(\d+)个(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemCount' => $matches[2],
            'itemId' => $matches[3],
        ]);
    }

    private function processDropItem()
    {
        if (!preg_match('/用户(\d+)丢弃包裹(\d+)个(\d+)/', $this->logLine, $matches))
            return;
        
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }   

    private function processDropEquipment()
    {
        if (!preg_match('/用户(\d+)丢弃装备(\d+)/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemId' => $matches[2]
        ]);
    }

    private function processDiscardMoney()
    {
        if (!preg_match('/用户(\d+)丢弃金钱(\d+)/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }

    private function processCreateParty()
    {
        if (!preg_match('/用户(\d+)建立了队伍\((\d+),(\d+)\)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'creatorId' => $matches[1],
            'teamId' => $matches[2],
            'teamType' => $matches[3],
        ]);
    }

    private function processjoinParty()
    {
        if (!preg_match('/用户(\d+)成为队员\((\d+),(\d+)\)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'teamId' => $matches[2],
            'teamMemberId' => $matches[3],
        ]);
    }

    private function processSpendMoney()
    {
        if (!preg_match('/用户(\d+)花掉金钱(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spendMoney' => $matches[2],
        ]);
    }

    private function processPickupMoney()
    {
        if (!preg_match('/拣起金钱(\d+)\W+(\w+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[2],
            'amount' => $matches[1]
        ]);
    }

    private function processBuyItem()
    {
        if (!preg_match('/用户(\d+).*从NPC购买了(\d+)个(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSellItem()
    {
        if (!preg_match('/用户(\d+).*卖店(\d+)个(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSpConsume()
    {
        if (!preg_match('/用户(\d+)消耗了sp (\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spAmount' => $matches[2],
        ]);
    }

    private function processSkillLevelUp()
    {
        if (!preg_match('/用户(\d+)技能(\d+)达到(\d+)级/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'skillId' => $matches[2],
            'skillLevel' => $matches[3],
        ]);
    }

    private function processRoleDie()
    {
        $this->getFieldsFromFormatlog();

        if (!isset($this->fields['roleid']))
            return;
    }

    public function processFactionActions()
    {
        $factionActions = [
            'create' => 'processCreateFaction',
            'delete' => 'processDeleteFaction',
        ];

        foreach ($factionActions as $pattern => $methodName) {
            if (strpos($this->logLine, $pattern) !== false){
                $this->$methodName();
                return;
            }
        }
    }

    public function processCreateFaction()
    {
        $this->getFieldsFromFormatlog();
    }

    public function processDeleteFaction()
    {
        $this->getFieldsFromFormatlog();

        $this->getLogWriter()->logGeneralInfo('deleteFaction', $this->fields);
    }

    private function processGetMoney()
    {
        if (!preg_match('/用户(\d+).*得到金钱(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }

    private function pickupTeamMoney()
    {
        if (!preg_match('/用户(\d+)组队拣起用户(\d+)丢弃的金钱(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'pickupRoleId' => $matches[2],
            'amount' => $matches[3]
        ]);
    }

    private function processPetEggHatch()
    {
        if (!preg_match('/用户(\d+)孵化了宠物蛋(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ]);
    }

    private function processPetEggRestore()
    {
        if (!preg_match('/用户(\d+)还原了宠物蛋(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ]);
    }

    private function processLevelUp()
    {
        if (!preg_match('/用户(\d+)升级到(\d+)级金钱(\d+),游戏时间(\d+:\d+:\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'level' => $matches[2],
            'money' => number_format($matches[3], 0, ',', '.'),
            'playtime' => $matches[4]
        ]);
    }

    private function processChat()
    {
        $patterns = [
            ['pattern' => '/Chat: src=(-?\d+) chl=(\d+) msg=([\w\+=\/]+)/', 'channel' => null],
            ['pattern' => '/Whisper: src=(-?\d+) dst=(-?\d+) msg=([\w\+=\/]+)/', 'channel' => 'Whisper'],
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern['pattern'], $this->logLine, $matches)) {
                $this->getLogWriter()->setFields([
                    'srcRoleId' => $matches[1],
                    'channel' => $pattern['channel'] !== null ? $pattern['channel'] : $this->getChannelName($matches[2]),
                    'message' => $this->decodeBase64Message($matches[3]),
                ]);
                
                if (isset($matches[2]) && $pattern['channel'] === 'Whisper') {
                    $this->fields['dstRoleId'] = $matches[2];
                }
                
                return;
            }
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

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemCount' => $matches[2],
            'itemId' => $matches[3],
            'recipeId' => $matches[4],
            'materials' => $materialsString
        ]);
    }
    
    private function processPickupItem()
    {
        if (!preg_match('/用户(\d+)拣起(\d+)个(\d+)\[用户(\d+)丢弃\]/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'pickup_userid' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3],
            'discard_userid' => $matches[4]
        ]);
    }

    private function processPurchaseFromAuction()
    {
        if (!preg_match('/用户(\d+)在百宝阁购买(\d+)样物品，花费(\d+)点剩余(\d+)点/', $this->logLine, $matches))
            return;
            
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'cost' => $matches[3] / 100,
            'balance' => $matches[4] / 100
        ]);
    }

    private function processGMCommand()
    {
        if (!preg_match('/GM:用户(\d+)执行了内部命令(\d+)/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'commandId' => $matches[2],
        ]);
    }

    private function processObtainTitle()
    {
        if (!preg_match('/roleid:(\d+) obtain title\[(\d+)\] time\[(\d+)\]/', $this->logLine, $matches))
            return;

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'titleId' => $matches[2],
            'time' => $matches[3]
        ]);
    }

    private function processTask()
    {
        $this->getFieldsFromFormatlog();

        if (!isset($this->fields['roleid']) OR !isset($this->fields['taskid']) OR !isset($this->fields['type']))
            return;
            
        $this->getLogWriter()->setOwner($this->fields['roleid']);

        switch ($this->fields['msg']) {
            case 'CheckDeliverTask':
                $this->getLogWriter()->logEvent($this->fields, 'processStartTask', 'startTask.json');
                break;
            case 'GiveUpTask':
                $this->getLogWriter()->logEvent($this->fields, 'processGiveUpTask', 'giveUpTask.json');
                break;
            case 'DeliverItem':
                preg_match('/Item id = (\d+), Count = (\d+)/', $this->logLine, $matches);
                $this->fields['itemid'] = $matches[1];
                $this->fields['count'] = $matches[2];
                $this->getLogWriter()->logEvent($this->fields, 'receiveItemFromTask', 'receiveItemFromTask.json');
                break;
            case 'DeliverByAwardData':
                preg_match('/success = (\d+), gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/', $this->logLine, $matches);
                $this->fields['success'] = $matches[1];
                $this->fields['gold'] = $matches[2];
                $this->fields['exp'] = $matches[3];
                $this->fields['sp'] = $matches[4];
                $this->fields['reputation'] = $matches[5];
                $this->getLogWriter()->logEvent($this->fields, 'deliverByAwardData', 'completeTask.json');
                break;
        }
    }

    private function processSendMail()
    {
        $this->getFieldsFromFormatlog();

        if (!isset($this->fields['src']))
            return;
    }
        
    private function processRoleLogin()
    {
        $this->getFieldsFromFormatlog();
    
        if (!isset($this->fields['roleid']))
            return;
    }
    
    private function processRoleLogout()
    {
        $this->getFieldsFromFormatlog();
    
        if (!isset($this->fields['roleid']))
            return;
    }

    private function processTrade()
    {
        if (!preg_match('/roleidA=(\d+):roleidB=(\d+):moneyA=(\d+):moneyB=(\d+):objectsA=([^:]*):objectsB=(.*)$/', $this->logLine, $matches))
            return;
    
        $this->getLogWriter()->setFields([
            'roleA_id' => $matches[1],
            'roleB_id' => $matches[2],
            'moneyA' => $matches[3],
            'moneyB' => $matches[4],
            'itemsA' => [],
            'itemsB' => []
        ]);
    
        $objectsA = $this->parseTradeObjets($matches[5], $this->fields, 'itemsA');
        $objectsB = $this->parseTradeObjets($matches[6], $this->fields, 'itemsB');
    
        $message = sprintf(
            Config::getMessage('trade'),
            $this->fields['roleA_id'],
            $this->fields['roleB_id'],
            $this->fields['moneyA'],
            $this->fields['roleA_id'],
            $this->fields['moneyB'],
            $this->fields['roleB_id'],
            $this->fields['roleA_id'],
            !empty($this->fields['itemsA']) ? count($this->fields['itemsA']) : 0,
            $this->fields['roleB_id'],
            !empty($this->fields['itemsB']) ? count($this->fields['itemsB']) : 0
        );
    
        $this->fields['message'] = $message;
        
        $this->getLogWriter()->setOwner($this->fields['roleA_id']);
        $this->getLogWriter()->appendToLogFile('trade.json', $this->fields);
    }

    private function parseTradeObjets(string $objects, array &$fields, string $itemsKey)
    {
        $items = [];
        $objects = explode(';', $objects);
        foreach ($objects as $object) {
            if (preg_match('/(\d+),(\d+),(\d+)/', $object, $item)){
                $items[] = ['itemId' => $item[1], 'quantity' => $item[2], 'position' => $item[3]];
            }
        }

        if (!array_key_exists($itemsKey, $this->fields))
            throw new Exception("Key {$itemsKey} does not exist in fields array, logline: {$this->logLine}");

        if (!empty($items))
            $this->fields[$itemsKey] = $items;

        return $items;
    }

    public function buildLogEvent()
    {
        // if (empty($this->getLogWriter()->getFields()))
        //     return;

        $this->getLogWriter()->logEvent($this->getMessageKeyName());
    }

    private function getMessageKeyName(): string
    {
        if ($this->getBuildMessage() === false)
            return null;

        $prefixes = ['process', 'handle'];
    
        foreach ($prefixes as $prefix) {
            if (strpos($this->methodName, $prefix) === 0) {
                $keyName = substr($this->methodName, strlen($prefix));
                break;
            }
        }
    
        return lcfirst($keyName);
    }    
}

if ($argc > 1) {
    $logify = new Logify;
    $logify->setLogLine($argv[1]);

    if ($logify->processLogLine() === false)
        throw new Exception("Log line not processed: {$argv[1]}");

    $logify->buildLogEvent();
}
else {
    echo "Usage: php logify.php <log line>\n";
}

