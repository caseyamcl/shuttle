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

    /**
     * @param string $action
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function ensureValidAction(string $action): string
    {
        if (! static::isValidAction($action)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid action: %s (allowed: %s, %s)',
                $action,
                self::MIGRATE,
                self::REVERT
            ));
        }

        return $action;
    }
}