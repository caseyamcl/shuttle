<?php

namespace Shuttle;

/**
 * Class ShuttleAction
 * @package Shuttle
 */
final class ShuttleAction
{
    const MIGRATE = 'migrate';
    const REVERT = 'revert';

    /**
     * @param string $action
     * @return bool
     */
    public static function isValidAction(string $action): bool
    {
        return in_array($action, [self::MIGRATE, self::REVERT]);
    }
}