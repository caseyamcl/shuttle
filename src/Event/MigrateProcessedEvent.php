<?php

namespace Shuttle\Event;

use Shuttle\Recorder\MigratorRecordInterface;
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
     * @var MigratorRecordInterface
     */
    private $record;

    /**
     * @var SourceItem
     */
    private $sourceItem;

    /**
     * PostMigrateEvent constructor.
     *
     * @param string $migratorName
     * @param SourceItem $sourceItem
     * @param MigratorRecordInterface $record
     */
    public function __construct(string $migratorName, SourceItem $sourceItem, MigratorRecordInterface $record)
    {
        $this->migratorName = $migratorName;
        $this->record = $record;
        $this->sourceItem = $sourceItem;
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
        return $this->record->getSourceId();
    }

    /**
     * @return MigratorRecordInterface
     */
    public function getRecord(): MigratorRecordInterface
    {
        return $this->record;
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
}