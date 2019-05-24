<?php

namespace denchotsanov\user\models\enums;

/**
 * Class UserStatus
 *
 */
class UserStatus
{
    const STATUS_DELETED = 0;
    const STATUS_PENDING = 9;
    const STATUS_ACTIVE = 10;
    /**
     * @var string message category
     */
    public static $messageCategory = 'denchotsanov.user';
    /**
     * @var array
     */
    public static $list = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_DELETED => 'Deleted',
    ];
}