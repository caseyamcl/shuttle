<?php

namespace Shuttle\Event;
use Shuttle\ShuttleAction;

/**
 * Class AbortEvent
 * @package Shuttle\Event
 */
class AbortEvent
{
    /**
     * @var null|ActionResultInterface
     */
    private $lastActionResult;

    /**
     * @var string
     */
    private $action;

    /**
     * AbortEvent constructor.
     * @param string $action
     * @param string $migratorName
     * @param null|ActionResultInterface $lastActionResult
     */
    public function __construct(string $action, string $migratorName, ?ActionResultInterface $lastActionResult = null)
    {
        $this->action = ShuttleAction::ensureValidAction($action);
        $this->lastActionResult = $lastActionResult;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return null|ActionResultInterface
     */
    public function getLastActionResult(): ?ActionResultInterface
    {
        return $this->lastActionResult;
    }

    /**
     * @return bool
     */
    public function hasLastAction(): bool
    {
        return (bool) $this->lastActionResult;
    }
}