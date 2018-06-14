<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;

/**
 * Class RevertSkippedEvent
 * @package Shuttle\Event
 */
class RevertSkippedEvent extends MigrateSkippedEvent
{
    /**
     * @return string
     */
    public function getAction(): string
    {
        return ShuttleAction::REVERT;
    }
}
