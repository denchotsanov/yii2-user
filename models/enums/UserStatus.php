<?php

namespace denchotsanov\user\models\enums;

/**
 * Class UserStatus
 *
 */
class UserStatus
{
    const ACTIVE = 1;
    const DELETED = 0;
    /**
     * @var string message category
     */
    public static $messageCategory = 'yii2module.user';
    /**
     * @var array
     */
    public static $list = [
        self::ACTIVE => 'Active',
        self::DELETED => 'Deleted',
    ];
}