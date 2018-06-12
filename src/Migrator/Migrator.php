<?php

namespace Shuttle\Migrator;

use Shuttle\Migrator\AbstractMigrator;
use Shuttle\DestinationInterface;
use Shuttle\Recorder\MigratorRecordInterface;
use Shuttle\Recorder\RecorderInterface;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class Migrator
 * @package Shuttle\NewShuttle
 */
class Migrator extends AbstractMigrator
{
    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var DestinationInterface
     */
    private $destination;

    /**
     * @var callable
     */
    private $prepare;

    /**
     * @var RecorderInterface
     */
    private $recorder;

    /**
     * AbstractMigrator constructor.
     *
     * @param SourceInterface $source
     * @param DestinationInterface $destination
     * @param RecorderInterface $recorder
     * @param callable $prepare Callback must accept a SourceItem interface and return the prepared record (any type)
     */
    public function __construct(
        SourceInterface $source,
        DestinationInterface $destination,
        RecorderInterface $recorder,
        callable $prepare = null)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->recorder = $recorder;
        $this->prepare = $prepare ?: function(SourceItem $sourceItem) { return $sourceItem->getData(); };
    }

    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int
    {
        return $this->source->countSourceItems();
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws \Exception  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem
    {
        return $this->source->getSourceItem($id);
    }

    /**
     * Get a report of
     *
     * @return \iterable|MigratorRecordInterface[]
     */
    public function getReport(): iterable
    {
        return $this->recorder->findRecords($this->__toString());
    }

    /**
     * @param string $sourceId
     * @return bool
     */
    public function isMigrated(string $sourceId): bool
    {
        return (bool) $this->recorder->findMigrationRecord($sourceId, $this->__toString());
    }

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return iterable|SourceItem[]
     */
    public function getSourceIterator(): iterable
    {
        return $this->source->getSourceIterator();
    }

    /**
     * @param SourceItem $sourceItem
     * @return mixed
     */
    public function prepare(SourceItem $sourceItem)
    {
        return call_user_func($this->prepare, $sourceItem);
    }

    /**
     * @param mixed $preparedItem
     * @return string  Destination Id
     */
    public function persist($preparedItem): string
    {
        return $this->destination->persist($preparedItem);
    }

    /**
     * @param string $sourceId
     * @throws \RuntimeException  Throw exception if destination not found
     */
    public function remove(string $sourceId)
    {
        return $this->destination->remove($this->getDestinationIdForSourceId($sourceId));
    }

    /**
     * Record that a migration has occurred
     *
     * @param SourceItem $sourceItem
     * @param string $destinationId
     * @return MigratorRecordInterface
     */
    public function recordMigrate(SourceItem $sourceItem, string $destinationId): MigratorRecordInterface
    {
        return $this->recorder->recordMigrate($sourceItem, $destinationId, $this->__toString());
    }

    /**
     * @param SourceItem $sourceItem
     * @param string $destinationId
     */
    public function recordRevert(SourceItem $sourceItem, string $destinationId)
    {
        $this->recorder->recordRevert($sourceItem, $destinationId, $this->__toString());
    }

    /**
     * @param string $sourceId
     * @return string
     */
    protected function getDestinationIdForSourceId(string $sourceId): string
    {
        if ($record = $this->recorder->findMigrationRecord($sourceId, $this->__toString())) {
            return $record->getDestinationId();
        }
        else {
            throw new \RuntimeException(
                'Missing destination ID for item (type: %s) with source ID: %s',
                $this->__toString(),
                $sourceId
            );
        }
    }
}