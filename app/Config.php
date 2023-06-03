<?php

namespace App;

class Config
{
    public static function getLogPatterns(): array
    {
        return [
            'formatlog:sendmail' => 'processSendMail',
            'formatlog:rolelogin' => 'processRoleLogin',
            'formatlog:rolelogout' => 'processRoleLogout',
            'formatlog:trade' => 'processTrade',
            'formatlog:task' => 'processTask',
            'formatlog:die' => 'processRoleDie',
            '丢弃包裹' => 'processDropItem',
            '丢弃装备' => 'processDropEquipment',
            '拣起金钱' => 'processPickupMoney',
            '丢弃金钱' => 'processDiscardMoney',
            '从NPC购买了' => 'processBuyItem',
            '卖店' => 'processSellItem',
            '得到金钱' => 'processGetMoney',
            '拣起' => 'processPickupItem',
            '在百宝阁购买' => 'processPurchaseFromAuction',
            '升级到' => 'processUserLevelUp',
            '花掉金钱' => 'processSpendMoney',
            '消耗了sp' => 'processSpConsume',
            '技能' => 'processSkillLevelUp',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'roleDie' => 'Role %d died. Death type: %d. Attacker: %d.',
            'spendMoney' => 'User %d spent %d money.',
            'spConsume' => 'User %d consumed %d sp.',
            'skillLevelUp' => 'User %d leveled up skill %d to level %d.',
            'processSendMail' => 'Timestamp: %d, User %d sent mail to user %d. Mail ID: %d. Mail size: %d. Money sent: %d. Item ID: %d. Item count: %d. Mail position: %d.',
            'roleLogout' => 'The account ID %d logged out with the role ID %d',
            'roleLogin' => 'The account ID %d logged in with the role ID %d',
            'dropItem' => 'User %d discarded %d of item %d',
            'pickupMoney' => 'Role %s picked up %d money',
            'discardMoney' => 'User %d discarded %d money',
            'buyItem' => 'User %d bought %d unit(s) of the item ID: %d from a NPC',
            'sellItem' => 'User %d sold %d unit(s) of the item ID: %d to a NPC',
            'getMoney' => 'User %d received %d money',
            'trade' => 'Role %d traded with role %d. Money exchanged: %d from role %d and %d from role %d. Role %d traded %s items. And Role %d traded %s items.',
            'dropEquipment' => 'User %d discarded equipment %d',
            'pickupItem' => 'User %d picked up %d of item %d (discarded by user %d)',
            'purchaseFromAuction' => 'Role ID %d purchased %d item(s) from gshop, spent %d unit(s) of cash, remaining balance: %d',
            'processStartTask' => 'Role ID %d started the task ID %d (type %d)',
            'processGiveUpTask' => 'Role ID %d gave up the task ID %d (type %d)',
            'receiveItemFromTask' => 'Role ID %d received item with item ID %d (count %d) from the task ID %d (type %d)',
            'deliverByAwardData' => 'Role ID %d completed the task ID %d (type %d) msg: %s, success = %d, gold = %d, exp = %d, sp = %d, reputation = %d',
            'userLevelUp' => 'User %d leveled up to level %d. Current money: %d. Playtime: %s.',
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