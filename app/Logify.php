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
        if (preg_match('/GM:用户(\d+)/', $this->getLogLine(), $matches)) {
            $gmRoleId = $matches[1];
        } 
        else if (preg_match('/GM:(\d+)/', $this->getLogLine(), $matches)) {
            $gmRoleId = $matches[1];
        } 
        else {
            throw new Exception('Unable to handle GM actions because the log line does not match any of the expected patterns.');
        }
    
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
        $matches = $this->getMatchesFromRegex('/用户(\d+)花掉金钱(\d+)/');

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
        $matches = $this->getMatchesFromRegex('/用户(\d+).*从NPC购买了(\d+)个(\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSellItem(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+).*卖店(\d+)个(\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSpConsume(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)消耗了sp (\d+)/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spAmount' => $matches[2],
        ]);
    }

    private function processSkillLevelUp(): void
    {
        $matches = $this->getMatchesFromRegex('/用户(\d+)技能(\d+)达到(\d+)级/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'skillId' => $matches[2],
            'skillLevel' => $matches[3],
        ]);
    }

    private function processDie(): void
    {
        $this->getAndvalidateFormatLogFields([
            'roleid', 'type', 'attacker'
        ]);
    }

    private function processFactionActions(): void
    {
        $factionActions = [
            'create' => 'processCreateFaction',
            'delete' => 'processDeleteFaction',
            'upgradefaction' => 'processUpgradeFaction',
            'deleterole' => 'processDeleteRoleFromFaction',
            'join' => 'processJoinFaction',
            'promote' => 'processPromoteRoleInFaction',
            'leave' => 'processLeaveFaction'
        ];
    
        foreach ($factionActions as $pattern => $methodName) {
            if (strpos($this->getLogLine(), $pattern) !== false) {
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

    private function processUpgradeFaction(): void
    {
        $this->getAndValidateFormatLogFields([
            'factionid', 'master', 'money', 'level'
        ]);

        $this->getLogWriter()->setOwnerKey('master');
    }

    private function processDeleteRoleFromFaction(): void
    {
        $this->getAndValidateFormatLogFields([
            'roleid', 'factionid', 'role'
        ]);

        $this->getLogWriter()->setOwnerKey('roleid');
    }

    private function processJoinFaction(): void
    {
        $this->getAndValidateFormatLogFields([
            'roleid', 'factionid'
        ]);

        $this->getLogWriter()->setOwnerKey('roleid');
    }

    private function processPromoteRoleInFaction(): void
    {
        $this->getAndValidateFormatLogFields([
            'superior', 'roleid', 'factionid', 'role'
        ]);

        $this->getLogWriter()->setOwnerKey('roleid');
    }

    private function processLeaveFaction(): void
    {
        $this->getAndValidateFormatLogFields([
            'roleid', 'factionid', 'role'
        ]);

        $this->getLogWriter()->setOwnerKey('roleid');
    }

    private function processDeleteFaction(): void
    {
        $this->getFieldsFromFormatlog();
        $this->getLogWriter()->logGeneralInfo('deleteFaction');

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
        $matches = $this->getMatchesFromRegex('/用户(\d+)制造了(\d+)个(\d+), 配方(\d+),/');
    
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
        $matches = $this->getMatchesFromRegex('/用户(\d+)拣起(\d+)个(\d+)(?:\[用户(\d+)丢弃\])?/');
    
        $this->getLogWriter()->setFields([
            'pickup_userid' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3],
            'discard_userid' => isset($matches[4]) ? $matches[4] : null
        ]);
    }

    private function processObtainTitle(): void
    {
        $matches = $this->getMatchesFromRegex('/roleid:(\d+) obtain title\[(\d+)\] time\[(\d+)\]/');

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'titleId' => $matches[2],
            'time' => $matches[3]
        ]);
    }

    private function processSendMail(): void
    {
        $this->getAndvalidateFormatLogFields([
            'timestamp', 'src', 'dst', 'mid', 'size', 'money', 'item', 'count', 'pos'
        ]);

        $this->getLogWriter()->setOwnerKey('src');
    }

    private function processGShopTrade(): void
    {
        $this->getAndValidateFormatLogFields([
            'userid', 'db_magic_number', 'order_id', 'item_id', 'expire',
            'item_count', 'cash_need', 'cash_left', 'guid1', 'guid2'
        ]);
    }
        
    private function processRoleLogin(): void
    {
        $this->getAndvalidateFormatLogFields([
            'userid', 'roleid', 'lineid', 'localsid'
        ]);
    }
    
    private function processRoleLogout(): void
    {
        $this->getAndvalidateFormatLogFields([
            'userid', 'roleid', 'localsid', 'time'
        ]);
    }

    private function processTrade(): void
    {
        $matches = $this->getMatchesFromRegex('/roleidA=(\d+):roleidB=(\d+):moneyA=(\d+):moneyB=(\d+):objectsA=([^:]*):objectsB=(.*)$/');
    
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

        $fields = $this->getLogWriter()->getFields();

        $type = $this->getLogWriter()->getKeyFromFields('msg');

        $customNames = [
            'DeliverItem' => 'receiveItemFromTask',
            'GiveUpTask' => 'giveUpTask',
            'DeliverByAwardData' => 'receiveTaskReward',
            'CheckDeliverTask' => 'startTask',
        ];

        $customName = $customNames[$type] ?? null;

        switch ($type) {
            case 'DeliverByAwardData':
                $matches = $this->getMatchesFromRegex('/gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/');
                
                $gold = $matches[1];
                $exp = $matches[2];
                $sp = $matches[3];
                $reputation = $matches[4];

                if ($gold == 0 && $exp == 0 && $sp == 0 && $reputation == 0)
                    exit;

                $this->getLogWriter()->setFields([
                    'roleid' => $fields['roleid'],
                    'taskid' => $fields['taskid'],
                    'gold' => $gold,
                    'exp' => $exp,
                    'sp' => $sp,
                    'reputation' => $reputation
                ]);
                
                break;
            case 'DeliverItem':
                $matches = $this->getMatchesFromRegex('/Item id = (\d+), Count = (\d+)/');
            
                $this->getLogWriter()->setFields([
                    'roleid' => $fields['roleid'],
                    'itemid' => $matches[1],
                    'count' => $matches[2],
                    'taskid' => $fields['taskid']
                ]);
            
                break;
            case 'GiveUpTask': break;
            case 'CheckDeliverTask': break;
            
            default:
                exit;
        }

        $this->getLogWriter()->setFileName($customName);
        $this->setMethodName($customName);
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
            return null;
        }
    
        $prefixes = ['process', 'handle'];
    
        foreach ($prefixes as $prefix) {
            if (strpos($methodName, $prefix) === 0) {
                $keyName = lcfirst(substr($methodName, strlen($prefix)));
                return $keyName;
            }
        }
        
        return $methodName;
    }     
}

if ($argc > 1) {
    $logify = new Logify;
    $logify->setLogLine($argv[1]);

    if ($logify->processLogLine() === false)
        throw new Exception("Log line not processed because it didntt match any pattern : {$argv[1]}");

    $logify->buildLogEvent();
}
else {
    echo "Usage: php logify.php <log line>\n";
}

