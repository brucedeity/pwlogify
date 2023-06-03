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
            '��������' => 'processDropItem',
            '����װ��' => 'processDropEquipment',
            '�����Ǯ' => 'processPickupMoney',
            '������Ǯ' => 'processDiscardMoney',
            '��NPC������' => 'processBuyItem',
            '����' => 'processSellItem',
            '�õ���Ǯ' => 'processGetMoney',
            '����' => 'processPickupItem',
            '�ڰٱ�����' => 'processPurchaseFromAuction',
            '������' => 'processLevelUp',
            '������Ǯ' => 'processSpendMoney',
            '������sp' => 'processSpConsume',
            '����' => 'processSkillLevelUp',
        ];
    }

    public static function getMessages(): array
    {
        return [
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
            'purchaseFromAuction' => 'Role ID %d purchased %d item(s) from gshop, spent %d unit(s) of cash, remaining balance: %d',
            'processStartTask' => 'Role ID %d started the task ID %d (type %d)',
            'processGiveUpTask' => 'Role ID %d gave up the task ID %d (type %d)',
            'receiveItemFromTask' => 'Role ID %d the item ID %d (%d unit(s)) from the task ID %d (type %d)',
            'deliverByAwardData' => 'Role ID %d completed the task ID %d (type %d) msg: %s, success = %d, gold = %d, exp = %d, sp = %d, reputation = %d',
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