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

    public function getLogLine(): string
    {
        return $this->logLine;
    }

    public function setMethodName(string $methodName): void
    {
        $this->methodName = $methodName;
    }

    public function getBuildMessage(): bool
    {
        return $this->buildMessage;
    }

    public function setBuildMessage(bool $status): void
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
            if (strpos($this->getLogLine(), $pattern) !== false){

                $this->setMethodName($methodName);
                $this->$methodName();

                return true;
            }
        }

        return false;
    }
    
    private function getFieldsFromFormatlog(): void
    {
        $matches = [];
        preg_match_all('/(\w+)=([\d\w]+)/', $this->getLogLine(), $matches, PREG_SET_ORDER);
    
        foreach ($matches as $match) {
            if (count($match) == 3) {
                $this->getLogWriter()->appendToFields([
                    $match[1] = $match[2]
                ]);
            }
        }
    }
    
    private function processGMActions(): void
    {
        if (!preg_match('/GM:(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $gmActionsMethods = [
            '������' => 'handleCreateMonster',
            '��ͼ�ƶ������' => 'handleAttemptMoveToPlayer',
            '�����' => 'handleMovePlayer',
            '��������������' => 'handleActivateTrigger',
            'ȡ������������' => 'handleCancelTrigger',
            '�����' => 'handleStartActivity',
            '�رջ' => 'handleStopActivity',
            '�л����޵�״̬' => 'handleToggleInvincibility',
            '�л�������״̬' => 'handleToggleInvisibility',
            '�����˹���������' => 'handleDropMonsterSpawner',
            '�û�������' => 'handlePlayerDisconnect',
        ];

        foreach ($gmActionsMethods as $pattern => $methodName) {
            if (strpos($this->getLogLine(), $pattern) !== false){

                $this->setMethodName($methodName);
                $this->$methodName($matches[1]);
                return;
            }
        }
    }

    private function handleStartActivity(int $gmRoleId): void
    {
        if (!preg_match('/�����(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ]);

        $this->getLogWriter()->setFileNamePreset('gm');
    }

    private function handleStopActivity(int $gmRoleId): void
    {
        if (!preg_match('/�رջ(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'activityId' => $matches[1],
        ]);

        $this->getLogWriter()->setFileNamePreset('gm');
    }

    private function handleToggleInvincibility(int $gmRoleId): void
    {
        if (!preg_match('/�л����޵�״̬\(([^)]+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '����' ? 0 : 1,
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }    

    private function handleToggleInvisibility(int $gmRoleId): void
    {
        if (!preg_match('/�л�������״̬\(([^)]+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'state' => $matches[1] == '����' ? 0 : 1,
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }    

    private function handleDropMonsterSpawner(int $gmRoleId): void
    {
        if (!preg_match('/�����˹���������(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'monsterSpawnerId' => $matches[1],
        ]);

        $this->getLogWriter()->setFileNamePreset('gm');
    }

    private function handlePlayerDisconnect(int $gmRoleId): void
    {
        if (!preg_match('/�û�������\((\d+)\):(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'disconnectType' => $matches[1],
            'playerId' => $matches[2],
        ]);

        $this->getLogWriter()->setFileNamePreset('gm');
    }

    private function handleActivateTrigger(int $gmRoleId): void
    {
        if (!preg_match('/��������������(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleCancelTrigger(int $gmRoleId): void
    {
        if (!preg_match('/ȡ������������(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'triggerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleCreateMonster(int $gmRoleId): void
    {
        if (!preg_match('/������(\d+)������(\d+)\((\d+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

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
        if (!preg_match('/��ͼ�ƶ������(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'gmRoleId' => $gmRoleId,
            'playerId' => $matches[1],
        ]);
    
        $this->getLogWriter()->setFileNamePreset('gm');
    }
    
    private function handleMoveToPlayer(int $gmRoleId): void
    {
        if(!preg_match('/�ƶ������(\d+) at position \((.+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

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
        if(!preg_match('/�����(\d+)�ƶ�����\((.+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
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
        if (!preg_match('/�û�(\d+)�ɼ��õ�(\d+)��(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemCount' => $matches[2],
            'itemId' => $matches[3],
        ]);
    }

    private function processDropItem(): void
    {
        if (!preg_match('/�û�(\d+)��������(\d+)��(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
        
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }   

    private function processDropEquipment(): void
    {
        if (!preg_match('/�û�(\d+)����װ��(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemId' => $matches[2]
        ]);
    }

    private function processDiscardMoney(): void
    {
        if (!preg_match('/�û�(\d+)������Ǯ(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }

    private function processCreateParty(): void
    {
        if (!preg_match('/�û�(\d+)�����˶���\((\d+),(\d+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'creatorId' => $matches[1],
            'teamId' => $matches[2],
            'teamType' => $matches[3],
        ]);
    }

    private function processjoinParty(): void
    {
        if (!preg_match('/�û�(\d+)��Ϊ��Ա\((\d+),(\d+)\)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'teamId' => $matches[2],
            'teamMemberId' => $matches[3],
        ]);
    }

    private function processSpendMoney(): void
    {
        if (!preg_match('/�û�(\d+)������Ǯ(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spendMoney' => $matches[2],
        ]);
    }

    private function processPickupMoney(): void
    {
        if (!preg_match('/�����Ǯ(\d+)\W+(\w+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[2],
            'amount' => $matches[1]
        ]);
    }

    private function processBuyItem(): void
    {
        if (!preg_match('/�û�(\d+).*��NPC������(\d+)��(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSellItem(): void
    {
        if (!preg_match('/�û�(\d+).*����(\d+)��(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'quantity' => $matches[2],
            'itemId' => $matches[3]
        ]);
    }

    private function processSpConsume(): void
    {
        if (!preg_match('/�û�(\d+)������sp (\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'spAmount' => $matches[2],
        ]);
    }

    private function processSkillLevelUp(): void
    {
        if (!preg_match('/�û�(\d+)����(\d+)�ﵽ(\d+)��/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'skillId' => $matches[2],
            'skillLevel' => $matches[3],
        ]);
    }

    private function processRoleDie(): void
    {
        $this->getFieldsFromFormatlog();

        if (!isset($this->fields['roleid']))
            $this->throwInvalidLogLineException();
    }

    public function processFactionActions(): void
    {
        $factionActions = [
            'create' => 'processCreateFaction',
            'delete' => 'processDeleteFaction',
        ];

        foreach ($factionActions as $pattern => $methodName) {
            if (strpos($this->getLogLine(), $pattern) !== false){
                $this->$methodName();
                $this->throwInvalidLogLineException();
            }
        }
    }

    public function processCreateFaction(): void
    {
        $this->getFieldsFromFormatlog();
    }

    public function processDeleteFaction(): void
    {
        $this->getFieldsFromFormatlog();

        $this->getLogWriter()->logGeneralInfo('deleteFaction', $this->fields);
    }

    private function processGetMoney()
    {
        if (!preg_match('/�û�(\d+).*�õ���Ǯ(\d+)/', $this->getLogLine(), $matches)) 
            $this->throwInvalidLogLineException();
            
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'amount' => $matches[2]
        ]);
    }

    private function pickupTeamMoney()
    {
        if (!preg_match('/�û�(\d+)��Ӽ����û�(\d+)�����Ľ�Ǯ(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'pickupRoleId' => $matches[2],
            'amount' => $matches[3]
        ]);
    }

    private function processPetEggHatch()
    {
        if (!preg_match('/�û�(\d+)�����˳��ﵰ(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ]);
    }

    private function processPetEggRestore()
    {
        if (!preg_match('/�û�(\d+)��ԭ�˳��ﵰ(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'userId' => $matches[1],
            'petEggId' => $matches[2]
        ]);
    }

    private function processLevelUp()
    {
        if (!preg_match('/�û�(\d+)������(\d+)����Ǯ(\d+),��Ϸʱ��(\d+:\d+:\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

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
        if (!preg_match('/�û�(\d+)������(\d+)��(\d+), �䷽(\d+),/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
    
        $materialsString = substr($this->getLogLine(), strpos($this->getLogLine(), "���Ĳ���"));
        $materialMatches = [];
        preg_match_all('/(���Ĳ���|����)(\d+), ����(\d+);/', $materialsString, $materialMatches, PREG_SET_ORDER);
    
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
        if (!preg_match('/�û�(\d+)����(\d+)��(\d+)\[�û�(\d+)����\]/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'pickup_userid' => $matches[1],
            'itemcount' => $matches[2],
            'itemId' => $matches[3],
            'discard_userid' => $matches[4]
        ]);
    }

    private function processPurchaseFromAuction()
    {
        if (!preg_match('/�û�(\d+)�ڰٱ�����(\d+)����Ʒ������(\d+)��ʣ��(\d+)��/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();
            
        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'itemcount' => $matches[2],
            'cost' => $matches[3] / 100,
            'balance' => $matches[4] / 100
        ]);
    }

    private function processGMCommand()
    {
        if (!preg_match('/GM:�û�(\d+)ִ�����ڲ�����(\d+)/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

        $this->getLogWriter()->setFields([
            'roleId' => $matches[1],
            'commandId' => $matches[2],
        ]);
    }

    private function processObtainTitle()
    {
        if (!preg_match('/roleid:(\d+) obtain title\[(\d+)\] time\[(\d+)\]/', $this->getLogLine(), $matches))
            $this->throwInvalidLogLineException();

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
            $this->throwInvalidLogLineException();
            
        $this->getLogWriter()->setOwner($this->fields['roleid']);

        switch ($this->fields['msg']) {
            case 'CheckDeliverTask':
                $this->getLogWriter()->logEvent($this->fields, 'processStartTask', 'startTask.json');
                break;
            case 'GiveUpTask':
                $this->getLogWriter()->logEvent($this->fields, 'processGiveUpTask', 'giveUpTask.json');
                break;
            case 'DeliverItem':
                preg_match('/Item id = (\d+), Count = (\d+)/', $this->getLogLine(), $matches);
                $this->fields['itemid'] = $matches[1];
                $this->fields['count'] = $matches[2];
                $this->getLogWriter()->logEvent($this->fields, 'receiveItemFromTask', 'receiveItemFromTask.json');
                break;
            case 'DeliverByAwardData':
                preg_match('/success = (\d+), gold = (\d+), exp = (\d+), sp = (\d+), reputation = (\d+)/', $this->getLogLine(), $matches);
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
            $this->throwInvalidLogLineException();
    }
        
    private function processRoleLogin()
    {
        $this->getFieldsFromFormatlog();
    
        if (!isset($this->fields['roleid']))
            $this->throwInvalidLogLineException();
    }
    
    private function processRoleLogout()
    {
        $this->getFieldsFromFormatlog();
    
        if (!isset($this->fields['roleid']))
            $this->throwInvalidLogLineException();
    }

    private function processTrade()
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
            throw new Exception("Key {$itemsKey} does not exist in fields array, logline: {$this->getLogLine()}");

        if (!empty($items))
            $this->fields[$itemsKey] = $items;

        return $items;
    }

    public function throwInvalidLogLineException(): void
    {
        throw new Exception(
            sprintf(
                'Unable to process the log line due to incorrect format. Method: %s, Log Line: %s', 
                $this->getMethodName(), 
                $this->getLogLine()
            )
        );
    }

    public function buildLogEvent()
    {
        if (empty($this->getLogWriter()->getFields()))
            return;

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

