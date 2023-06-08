<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config;
use App\LogWriter;

class Logify
{
    private $logLine;
    private $methodName;
    private $buildMessage = true;

    public function setLogLine(string $logLine): void
    {        
        $this->logLine = $logLine;
        
        $this->logWriter = new LogWriter;
    }

    private function getLogLine(): string
    {
        return $this->logLine;
    }

    private function setMethodName(string $methodName): void
    {
        $this->methodName = $methodName;
    }

    private function getMethodName(): string
    {
        return $this->methodName;
    }

    private function getBuildMessage(): bool
    {
        return $this->buildMessage;
    }

    private function setBuildMessage(bool $status): void
    {
        $this->buildMessage = $status;
    }

    private function getLogWriter(): LogWriter
    {
        return $this->logWriter;
    }

    public function processLogLine(): bool
    {
        foreach (Config::getLogPatterns() as $pattern => $methodName) {
            if (strpos($this->getLogLine(), $pattern) !== false){

                $this->setMethodName($methodName);
                $this->$methodName();

                return true;
            }
        }

        return false;
    }
    
    private function processGMActions(): void
    {
        $matches = $this->getMatchesFromRegex('/GM:用户(\d+)/');
    
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
            '执行了内部命令' => 'handleCommand',
        ];
    
        foreach ($gmActionsMethods as $pattern => $methodName) {
            if (strpos($this->getLogLine(), $pattern) !== false){
    
                $this->setMethodName($methodName);
                $this->$methodName($matches[1]);
    
                return;
            }
        }
    }

    private function handleCommand(): void
    {
        $matches = $this->getMatchesFromRegex('/GM:用户(\d+)执行了内部命令(\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'commandId' => $matches[2],
        ]);

        $this->getLogWriter()->setFileNamePreset('gm');
    }

    private function handleStartActivity(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/开启活动(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleStopActivity(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/关闭活动(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleToggleInvincibility(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/切换了无敌状态\(([^)]+)\)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '正常' ? 0 : 1,
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }    
    
    private function handleToggleInvisibility(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/切换了隐身状态\(([^)]+)\)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '现形' ? 0 : 1,
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }    
    
    private function handleDropMonsterSpawner(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/丢出了怪物生成器(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'monsterSpawnerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handlePlayerDisconnect(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/用户断线了\((\d+)\):(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'disconnectType' => $matches[1],
            'playerId' => $matches[2],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleActivateTrigger(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/激活了生成区域(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }    
    
    private function handleCancelTrigger(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/取消了生成区域(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleCreateMonster(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/创建了(\d+)个怪物(\d+)\((\d+)\)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'monsterCount' => $matches[1],
            'monsterType' => $matches[2],
            'monsterId' => $matches[3],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleAttemptMoveToPlayer(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/试图移动到玩家(\d+)/');
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
        
    private function handleMoveToPlayer(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/移动到玩家(\d+).*\((.+)\)/');
    
        $position = explode(',', $matches[2]);
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
            'positionX' => floatval($position[0]),
            'positionY' => floatval($position[1]),
            'positionZ' => floatval($position[2]),
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleMovePlayer(int $gmRoleId): void
    {
        $matches = $this->getMatchesFromRegex('/将玩家(\d+)移动过来\((.+)\)/');
    
        $position = explode(',', $matches[2]);
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
            'positionX' => floatval($position[0]),
            'positionY' => floatval($position[1]),
            'positionZ' => floatval($position[2]),
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }    

    private function processMine(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)采集得到(\d+)个(\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemCount' => $matches[2],
            'itemId' => $matches[3],
        ]);
    }

    private function processDropItem(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)丢弃包裹(\d+)个(\d+)/');
        
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }   

    private function processDropEquipment(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)丢弃装备(\d+)/');
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemId' => $matches[2]
        ]);
    }

    private function processDiscardMoney(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)丢弃金钱(\d+)/');
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }

    private function processCreateParty(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)建立了队伍\((\d+),(\d+)\)/');

        $this->getLogWriter()->setFields([
            'creatorId' => $matches[1],
            'teamId' => $matches[2],
            'teamType' => $matches[3],
        ]);
    }

    private function processjoinParty(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)成为队员\((\d+),(\d+)\)/');

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'teamId' => $matches[2],
            'teamMemberId' => $matches[3],
        ]);
    }

    private function processSpendMoney(): void
    {
        $this->matches = $this->getMatchesFromRegex('/用户(\d+)花掉金钱(\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spendMoney' => $matches[2],
        ]);
    }

    private function processPickupMoney(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)拣起金钱(\d+)/');
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }       

    private function processBuyItem(): void
    {
        if (!preg_match('/用户(\d+).*从NPC购买了(\d+)个(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSellItem(): void
    {
        if (!preg_match('/用户(\d+).*卖店(\d+)个(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSpConsume(): void
    {
        if (!preg_match('/用户(\d+)消耗了sp (\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spAmount' => $matches[2],
        ]);
    }

    private function processSkillLevelUp(): void
    {
        if (!preg_match('/用户(\d+)技能(\d+)达到(\d+)级/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'skillId' => $matches[2],
            'skillLevel' => $matches[3],
        ]);
    }

    private function processDie(): void
    {
        // 2023-06-08 17:09:50 vps.server.com gamed: notice : formatlog:die:roleid=1030:type=4:attacker=-2146409492
        $this->getAndvalidateFormatLogFields([
            'roleid', 'type', 'attacker'
        ]);
    }

    private function processFactionActions(): void
    {
        $factionActions = [
            'create' => 'processCreateFaction',
            'delete' => 'processDeleteFaction',
        ];

        foreach ($factionActions as $pattern => $methodName) {
            if (strpos($this->getLogLine(), $pattern) !== false){

                $this->setMethodName($methodName);
                $this->$methodName();

                break;
            }
        }
    }

    private function processCreateFaction(): void
    {
        $this->getAndvalidateFormatLogFields([
            'type', 'roleid', 'factionid'
        ]);

        $this->getLogWriter()->setOwnerKey('roleid');
    }

    private function processDeleteFaction(): void
    {
        $this->getFieldsFromFormatlog();

        $this->getLogWriter()->logGeneralInfo('deleteFaction');

        // Exit the script because we don't need "buildLogEvent" method to be called
        exit;
    }

    private function processGetMoney(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+).*拣起金钱(\d+)/');
            
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }

    private function pickupTeamMoney(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)组队拣起用户(\d+)丢弃的金钱(\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'pickupRoleId' => $matches[2],
            'amount' => $matches[3]
        ]);
    }

    private function processPetEggHatch(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)孵化了宠物蛋(\d+)/');

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ]);
    }

    private function processPetEggRestore(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)还原了宠物蛋(\d+)/');

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ]);
    }

    private function processLevelUp(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)升级到(\d+)级金钱(\d+),游戏时间(\d+:\d+:\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'level' => $matches[2],
            'money' => number_format($matches[3], 0, ',', '.'),
            'playtime' => $matches[4]
        ]);
    }

    private function processChat(): void
    {
        $patterns = [
            ['pattern' => '/Chat: src=(-?\d+) chl=(\d+) msg=([\w\+=\/]+)/', 'channel' => null],
            ['pattern' => '/Whisper: src=(-?\d+) dst=(-?\d+) msg=([\w\+=\/]+)/', 'channel' => 'Whisper'],
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern['pattern'], $this->getLogLine(), $matches)) {
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

    private function getChannelName($channelId): string
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

    private function decodeBase64Message($base64Message): string
    {
        $decodedMessage = base64_decode($base64Message);
        return mb_convert_encoding($decodedMessage, 'UTF-8', 'UTF-16LE');
    }

    private function processCraftItem(): void
    {
        if (!preg_match('/用户(\d+)制造了(\d+)个(\d+), 配方(\d+),/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $materialsString = substr($this->getLogLine(), strpos($this->getLogLine(), "消耗材料"));
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
    
    private function processPickupItem(): void
    {
        if (!preg_match('/用户(\d+)拣起(\d+)个(\d+)\[用户(\d+)丢弃\]/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'pickup_userid' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3],
            'discard_userid' => $matches[4]
        ]);
    }

    private function processPurchaseFromAuction(): void
    {
        if (!preg_match('/用户(\d+)在百宝阁购买(\d+)样物品，花费(\d+)点剩余(\d+)点/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
            
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'cost' => $matches[3] / 100,
            'balance' => $matches[4] / 100
        ]);
    }

    private function processObtainTitle(): void
    {
        if (!preg_match('/roleid:(\d+) obtain title\[(\d+)\] time\[(\d+)\]/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'titleId' => $matches[2],
            'time' => $matches[3]
        ]);
    }

    private function processSendMail(): void
    {
        // 2023-06-08 16:59:45 vps.server.com gamedbd: notice : formatlog:sendmail:timestamp=28:src=1030:dst=1025:mid=0:size=14:money=0:item=0:count=0:pos=-1
        $this->getAndvalidateFormatLogFields([
            'timestamp', 'src', 'dst', 'mid', 'size', 'money', 'item', 'count', 'pos'
        ]);

        $this->getLogWriter()->setOwnerKey('src');
    }
        
    private function processRoleLogin(): void
    {
        // 2023-06-08 16:47:10 vps.server.com glinkd-1: notice : formatlog:rolelogin:userid=1024:roleid=1030:lineid=1:localsid=17
        $this->getAndvalidateFormatLogFields([
            'userid', 'roleid', 'lineid', 'localsid'
        ]);
    }
    
    private function processRoleLogout(): void
    {
        // 2023-06-08 16:45:55 vps.server.com glinkd-1: notice : formatlog:rolelogout:userid=1024:roleid=1030:localsid=17:time=10550
        $this->getAndvalidateFormatLogFields([
            'userid', 'roleid', 'localsid', 'time'
        ]);
    }

    private function processTrade(): void
    {
        if (!preg_match('/roleidA=(\d+):roleidB=(\d+):moneyA=(\d+):moneyB=(\d+):objectsA=([^:]*):objectsB=(.*)$/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
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

    private function parseTradeObjets(string $objects, array &$fields, string $itemsKey): array
    {
        $items = [];
        $objects = explode(';', $objects);
        foreach ($objects as $object) {
            if (preg_match('/(\d+),(\d+),(\d+)/', $object, $item)){
                $items[] = ['itemId' => $item[1], 'quantity' => $item[2], 'position' => $item[3]];
            }
        }

        if (!array_key_exists($itemsKey, $this->fields))
            throw new Exception("Key {$itemsKey} does not exist in fields array, logline: {$this->getLogLine()}");

        if (!empty($items))
            $this->fields[$itemsKey] = $items;

        return $items;
    }

    private function processTask(): void
    {
        $this->getAndvalidateFormatLogFields([
            'roleid', 'taskid', 'type'
        ]);

        $type = lcfirst($this->getLogWriter()->getKeyFromFields('msg'));

        $this->setMethodName($type);
        $this->setBuildMessage(false);

        if ($type == 'DeliverItem')
        {
            preg_match('/Item id = (\d+), Count = (\d+)/', $this->getLogLine(), $matches);

            $this->getLogWriter()->appendToFields([
                'itemid' => $matches[1],
                'count' => $matches[2]
            ]);
            
            $this->getLogWriter()->setFileName('receiveItemFromTask');
        }
        else if ($type == 'DeliverByAwardData')
        {
            preg_match('/success = (\d+), gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/', $this->getLogLine(), $matches);
                
            $this->getLogWriter()->appendToFields([
                'success' => $matches[1],
                'gold' => $matches[2],
                'exp' => $matches[3],
                'sp' => $matches[4],
                'reputation' => $matches[5]
            ]);

            $this->getLogWriter()->setFileName('receiveTaskReward');
        }
    }

    private function getFieldsFromFormatlog(): void
    {
        $matches = [];
        preg_match_all('/(\w+)=([^:]+)/', $this->getLogLine(), $matches, PREG_SET_ORDER);
        $result = [];
    
        foreach ($matches as $match) {
            if (count($match) == 3) {
                $result[$match[1]] = $match[2];
            }
        }
    
        $this->getLogWriter()->setFields($result);
    }
    
    private function getAndvalidateFormatLogFields(array $expectedKeys): void
    {
        $this->getFieldsFromFormatlog();

        foreach ($expectedKeys as $key) {
            if (!$this->getLogWriter()->assertFieldExists($key)) {
                throw new Exception("Expected key: {$key} not found in fields. Current fields: ".json_encode($this->getLogWriter()->getFields(), JSON_PRETTY_PRINT));
            }
        }
    }

    private function getMatchesFromRegex(string $regex): array
    {
        if (!preg_match($regex, $this->getLogLine(), $matches))
            throw new Exception(sprintf('Unable to process the log line due to incorrect format. Method: %s, Log Line: %s', $this->getMethodName(), $this->getLogLine()));

        return $matches;
    }

    public function buildLogEvent(): void
    {
        if (empty($this->getLogWriter()->getFields()))
            throw new Exception('Unable to build '.$this->getMethodName().' log event, fields array is empty. Log line: '.$this->getLogLine());

        $this->getLogWriter()->logEvent($this->getMessageKeyName());
    }

    private function getMessageKeyName(): ?string
    {
        $methodName = $this->getMethodName();

        if (!$this->getBuildMessage()) {
            // if (Config::messageKeyExists($methodName))
            // {
            //     return $methodName;
            // }

            return $methodName;
        }
    
        $prefixes = ['process', 'handle'];
    
        foreach ($prefixes as $prefix) {
            if (strpos($methodName, $prefix) === 0) {
                $keyName = lcfirst(substr($methodName, strlen($prefix)));
                return $keyName;
            }
        }
    
        throw new Exception('Unable to build message key name, current method name is from type: '.gettype($methodName). ' value: '.json_encode($methodName));
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

