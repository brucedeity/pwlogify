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
            '��������' => 'processDropItem',
            '����װ��' => 'processDropEquipment',
            '�����Ǯ' => 'processPickupMoney',
            '������Ǯ' => 'processDiscardMoney',
            '��NPC������' => 'processBuyItem',
            '����' => 'processSellItem',
            '�õ���Ǯ' => 'processGetMoney',
            '����' => 'processPickupItem',
            '�ڰٱ�����' => 'processPurchaseFromAuction',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'roleLogout' => 'Role ID %d from user %d logged out',
            'roleLogin' => 'Role ID %d from user %d logged in',
            'dropItem' => 'User %d discarded %d of item %d',
            'pickupMoney' => 'Role %s picked up %d money',
            'discardMoney' => 'User %d discarded %d money',
            'buyItem' => 'User %d bought %d of item %d from NPC',
            'sellItem' => 'User %d sold %d of item %d',
            'getMoney' => 'User %d received %d money',
            'trade' => 'Role %d traded with role %d. Money exchanged: %d from role %d and %d from role %d. Role %d traded %s items. And Role %d traded %s items.',
            'dropEquipment' => 'User %d discarded equipment %d',
            'pickupItem' => 'User %d picked up %d of item %d (discarded by user %d)',
            'purchaseFromAuction' => 'User %d purchased %d items from the auction house, spent %d points, remaining balance: %d points',
            'processStartTask' => 'Role ID %d started task with task ID %d (type %d)',
            'processGiveUpTask' => 'Role ID %d gave up task with task ID %d (type %d)',
            'receiveItemFromTask' => 'Role ID %d received item with item ID %d (count %d) from task with task ID %d (type %d)',
            'deliverByAwardData' => 'Role ID %d completed task with task ID %d (type %d), success = %d, gold = %d, exp = %d, sp = %d, reputation = %d',
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