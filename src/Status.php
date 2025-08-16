<?php

namespace Multicaret\Acquaintances;

/**
 * Class Status.
 */
class Status
{
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const DENIED = 'denied';
    const BLOCKED = 'blocked';

    /**
     * Get the status order for sorting
     *
     * @return array
     */
    public static function getOrder(): array
    {
        return [
            self::PENDING => 1,
            self::ACCEPTED => 2,
            self::DENIED => 3,
            self::BLOCKED => 4,
        ];
    }

    /**
     * Get the priority order for a specific status
     *
     * @param string $status
     * @return int
     */
    public static function getOrderPriority(string $status): int
    {
        return self::getOrder()[$status] ?? 999;
    }

    /**
     * Get all statuses in priority order
     *
     * @return array
     */
    public static function getOrderedStatuses(): array
    {
        return [
            self::PENDING,
            self::ACCEPTED,
            self::DENIED,
            self::BLOCKED,
        ];
    }
}
