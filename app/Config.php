<?php

namespace App;

class Config
{
    public static function getLogPatterns(): array
    {
        return [
            'chat' => 'processChat',
            'obtain title' => 'processObtainTitle',
            'formatlog:sendmail' => 'processSendMail',
            'formatlog:rolelogin' => 'processRoleLogin',
            'formatlog:rolelogout' => 'processRoleLogout',
            'formatlog:trade' => 'processTrade',
            'formatlog:task' => 'processTask',
            'formatlog:die' => 'processRoleDie',
            '丢弃包裹' => 'processDropItem',
            '丢弃装备' => 'processDropEquipment',
            '执行了内部命令' => 'processGMCommand',
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
        ];
    }

    public static function getMessages(): array
    {
        return [
            'obtainTitle' => 'The Role ID %d obtained the title ID %d at time %d.',
            'gmCommand' => 'The GM with Role ID %d executed internal command %d.',
            'craftItem' => 'The Role ID %d crafted %d unit(s) of the item ID: %d using recipe ID: %d. Consumed materials: %s.',
            'roleDie' => 'Role %d died. Death type: %d. Attacker: %d.',
            'spendMoney' => 'The Role ID %d spent %d money.',
            'spConsume' => 'The Role ID %d consumed %d sp.',
            'skillLevelUp' => 'The Role ID %d leveled up skill %d to level %d.',
            'processSendMail' => 'Timestamp: %d, The Role ID %d sent mail to role ID %d. Mail ID: %d. Mail size: %d. Money sent: %d. Item ID: %d. Item count: %d. Mail position: %d.',
            'roleLogout' => 'The account ID %d logged out with the role ID %d',
            'roleLogin' => 'The account ID %d logged in with the role ID %d',
            'dropItem' => 'The Role ID %d discarded %d unit(s) of item ID %d',
            'pickupMoney' => 'Role %s picked up %d money',
            'discardMoney' => 'The Role ID %d discarded %d money',
            'buyItem' => 'The Role ID %d bought %d unit(s) of the item ID: %d from a NPC',
            'sellItem' => 'The Role ID %d sold %d unit(s) of the item ID: %d to a NPC',
            'getMoney' => 'The Role ID %d received %d money',
            'trade' => 'Role %d traded with role %d. Money exchanged: %d from role %d and %d from role %d. Role %d traded %s items. And Role %d traded %s items.',
            'dropEquipment' => 'The Role ID %d discarded equipment %d',
            'pickupItem' => 'The Role ID %d picked up %d unit(s) of item %d (discarded by role ID %d)',
            'purchaseFromAuction' => 'The Role ID %d purchased %d item(s) from gshop, spent %d unit(s) of cash, remaining balance: %d',
            'processStartTask' => 'The Role ID %d started the task ID %d (type %d)',
            'processGiveUpTask' => 'The Role ID %d gave up the task ID %d (type %d)',
            'receiveItemFromTask' => 'The Role ID %d received the item ID %d (%d unit(s)) (type %d) from the task ID %d',
            'deliverByAwardData' => 'The Role ID %d completed the task ID %d (type %d) msg: %s, success = %d, gold = %d, exp = %d, sp = %d, reputation = %d',
            'levelUp' => 'The Role ID %d leveled up to level %d. Current money: %s. Playtime: %s.',
        ];
    }

    public static function getMessage(string $messageKey): string
    {
        $messages = self::getMessages();

        if (!array_key_exists($messageKey, $messages)) {
            throw new \Exception('Message key not found: ' . $messageKey . '.');
        }

        return $messages[$messageKey];
    }
}