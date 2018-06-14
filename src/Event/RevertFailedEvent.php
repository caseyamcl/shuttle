<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;

/**
 * Class RevertFailedEvent
 * @package Shuttle\Event
 */
class RevertFailedEvent extends MigrateFailedEvent
{
    public function getAction(): string
    {
        return ShuttleAction::REVERT;
    }
}
