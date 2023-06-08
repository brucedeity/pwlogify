<?php

namespace App;

class Config
{
    public static function getLogPatterns(): array
    {
        return [
            'GM:' => 'processGMActions',
            'chat :' => 'processChat',
            'obtain title' => 'processObtainTitle',
            'formatlog:sendmail' => 'processSendMail',
            'formatlog:rolelogin' => 'processRoleLogin',
            'formatlog:rolelogout' => 'processRoleLogout',
            'formatlog:trade' => 'processTrade',
            'formatlog:task' => 'processTask',
            'formatlog:die' => 'processDie',
            'formatlog:faction' => 'processFactionActions',
            '建立了队伍' => 'processCreateParty',
            '成为队员' => 'processJoinParty',
            '丢弃包裹' => 'processDropItem',
            '丢弃装备' => 'processDropEquipment',
            '拣起金钱' => 'processPickupMoney',
            '丢弃金钱' => 'processDiscardMoney',
            '从NPC购买了' => 'processBuyItem',
            '卖店' => 'processSellItem',
            '得到金钱' => 'processGetMoney',
            '拣起' => 'processPickupItem',
            '在百宝阁购买' => 'processPurchaseFromAuction',
            '升级到' => 'processLevelUp',
            '花掉金钱' => 'processSpendMoney',
            '消耗了sp' => 'processSpConsume',
            '技能' => 'processSkillLevelUp',
            '制造了' => 'processCraftItem',
            '采集得到' => 'processMine',
            '孵化了宠物蛋' => 'processPetEggHatch',
            '还原了宠物蛋' => 'processPetEggRestore',
            '组队拣起用户' => 'pickupTeamMoney'

        ];
    }

    public static function getMessages(): array
    {
        return [
            'DeliverItem' => '',
            'DeliverByAwardData' => '',
            'pickupTeamMoney' => 'Role ID %d picked up money (%d) dropped by Role ID %d they both were in a Party.',
            'deleteFaction' => '(Action type: %s) The faction of ID %d was deleted.',
            'createFaction' => '(Action type: %s) The Role ID %d just created a new faction (faction ID: %d).',
            'createParty' => 'The Role ID %d created team with ID %d (Type: %d)',
            'joinParty' => 'The Role ID %d joined team with ID %d as member %d',
            'petHatch' => 'The Role ID %d hatched the pet egg ID %d.',
            'petRestore' => 'The Role ID %d restored a pet and received the pet egg ID %d.',
            'startActivity' => 'GM %d started the activity %d.',
            'stopActivity' => 'GM %d stopped the activity %d.',
            'toggleInvincibility' => 'GM %d toggled invincibility state. Current state => %d.',
            'toggleInvisibility' => 'GM %d toggled invisibility state. Current state => %d.',
            'dropMonsterSpawner' => 'GM %d dropped monster spawner with ID => %d.',
            'playerDisconnect' => 'Player %d was disconnected. Disconnect type => %d.',
            'activateTrigger' => 'GM %d activated the trigger %d.',
            'cancelTrigger' => 'GM %d canceled the trigger %d.',
            'createMonster' => 'GM %d created %d monster(s) of type %d and ID %d,',
            'attemptMoveToPlayer' => 'GM %d attempted to move to player %d.',
            'moveToPlayer' => 'GM %d moved to player %d at position (%f, %f, %f).',
            'movePlayer' => 'GM %d moved player %d to position (%f, %f, %f).',
            'mine' => 'The Role ID %d mined and obtained %d unit(s) of item ID %d.',
            'obtainTitle' => 'The Role ID %d obtained the title ID %d at time %d.',
            'command' => 'The GM with Role ID %d executed internal command %d.',
            'craftItem' => 'The Role ID %d crafted %d unit(s) of the item ID: %d using recipe ID: %d. Consumed materials: %s.',
            'die' => 'Role %d died. Death type: %d. Attacker: %d.',
            'spendMoney' => 'The Role ID %d spent %d money.',
            'spConsume' => 'The Role ID %d consumed %d sp.',
            'skillLevelUp' => 'The Role ID %d leveled up the skill ID %d to level %d.',
            'sendMail' => 'Timestamp: %d, The Role ID %d just sent a mail to role ID %d. Mail ID: %d. Mail size: %d. Money sent: %d. Item ID: %d. Item count: %d. Mail position: %d.',
            'roleLogout' => 'The account ID %d logged out with the role ID %d',
            'roleLogin' => 'The account ID %d logged in with the role ID %d',
            'dropItem' => 'The Role ID %d discarded %d unit(s) of item ID %d',
            'pickupMoney' => 'Role %s picked up %d money',
            'discardMoney' => 'The Role ID %d discarded %d money',
            'buyItem' => 'The Role ID %d bought %d unit(s) of the item ID: %d from a NPC',
            'sellItem' => 'The Role ID %d sold %d unit(s) of the item ID: %d to a NPC',
            'getMoney' => 'The Role ID %d received %d money',
            'trade' => 'Role %d traded with role %d. Money exchanged: %d from role %d and %d from role %d. Role %d traded %s items. And Role %d traded %s items.',
            'dropEquipment' => 'The Role ID %d discarded his equipment of ID %d',
            'pickupItem' => 'The Role ID %d picked up %d unit(s) of item %d (discarded by role ID %d)',
            'purchaseFromAuction' => 'The Role ID %d purchased %d item(s) from gshop, spent %d unit(s) of cash, remaining balance: %d',
            'startTask' => 'The Role ID %d started the task ID %d',
            'giveUpTask' => 'The Role ID %d gave up the task ID %d',
            'receiveItemFromTask' => 'The Role ID %d received %d unit(s) of the item ID %d from the task ID %d',
            'receiveTaskReward' => 'The Role ID %d completed the task ID %d and received as reward: gold = %d, exp = %d, sp = %d, reputation = %d',
            'levelUp' => 'The Role ID %d leveled up to level %d. Current money: %s. Playtime: %s.',
        ];
    }

    public function messageKeyExists(string $messageKey): bool
    {
        return isset(self::getMessages()[$messageKey]);
    }

    public static function getMessage(string $messageKey)
    {
        $messages = self::getMessages();

        if (!isset($messages[$messageKey])) {
            return;
        }

        return $messages[$messageKey];
    }
}