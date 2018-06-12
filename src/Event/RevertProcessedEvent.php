<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostRevertEvent
 * @package Shuttle\Event
 */
class RevertProcessedEvent extends Event implements ActionResultInterface
{
    /**
     * @var string
     */
    private $migratorName;

    /**
     * @var string
     */
    private $sourceId;

    /**
     * RevertProcessedEvent constructor.
     * @param string $migratorName
     * @param string $sourceId
     */
    public function __construct(string $migratorName, string $sourceId)
    {
        $this->migratorName = $migratorName;
        $this->sourceId = $sourceId;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return ShuttleAction::REVERT;
    }

    /**
     * @return string
     */
    public function getMigratorName(): string
    {
        return $this->migratorName;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this::PROCESSED;
    }
}