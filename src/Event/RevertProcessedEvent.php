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
     * @var bool
     */
    private $deleteOccurred;

    /**
     * RevertProcessedEvent constructor.
     * @param string $migratorName
     * @param string $sourceId
     * @param bool $deleteOccurred
     */
    public function __construct(string $migratorName, string $sourceId, bool $deleteOccurred)
    {
        $this->migratorName = $migratorName;
        $this->sourceId = $sourceId;
        $this->deleteOccurred = $deleteOccurred;
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

    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * @return bool
     */
    public function isDeleteOccurred(): bool
    {
        return $this->deleteOccurred;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return sprintf('Reverted item (source ID: %s)', $this->getSourceId());
    }
}
