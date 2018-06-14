<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;
use Shuttle\SourceItem;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostMigrateEvent
 * @package Shuttle\Event
 */
class MigrateProcessedEvent extends Event implements ActionResultInterface
{
    /**
     * @var string
     */
    private $migratorName;

    /**
     * @var SourceItem
     */
    private $sourceItem;

    /**
     * @var string
     */
    private $destinationId;

    /**
     * PostMigrateEvent constructor.
     *
     * @param string $migratorName
     * @param SourceItem $sourceItem
     * @param string $destinationId
     */
    public function __construct(string $migratorName, SourceItem $sourceItem, string $destinationId)
    {
        $this->migratorName = $migratorName;
        $this->sourceItem = $sourceItem;
        $this->destinationId = $destinationId;
    }

    /**
     * @return string
     */
    public function getMigratorName(): string
    {
        return $this->migratorName;
    }

    /**
     * @return SourceItem
     */
    public function getSourceItem(): SourceItem
    {
        return $this->sourceItem;
    }

    /**
     * @return string
     */
    public function getDestinationId(): string
    {
        return $this->destinationId;
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
    public function getAction(): string
    {
        return ShuttleAction::MIGRATE;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return sprintf(
            'Migrated source item %s (%s) to destination (%s)',
            $this->getMigratorName(),
            $this->getSourceItem()->getId(),
            $this->getDestinationId()
        );
    }
}
