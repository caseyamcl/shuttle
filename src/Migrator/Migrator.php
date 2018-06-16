<?php

namespace Shuttle\Migrator;

use Shuttle\Exception\AlreadyMigratedException;
use Shuttle\Exception\MissingItemException;
use Shuttle\DestinationInterface;
use Shuttle\Recorder\MigrateRecordInterface;
use Shuttle\Recorder\RecorderInterface;
use Shuttle\SourceIdIterator;
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
        callable $prepare = null
    ) {
    
        $this->source = $source;
        $this->destination = $destination;
        $this->recorder = $recorder;
        $this->prepare = $prepare ?: function (SourceItem $sourceItem) {
            return $sourceItem->getData();
        };
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
     * @return \iterable|\Generator|MigrateRecordInterface[]
     */
    public function getMigratedSourceIdIterator(): iterable
    {
        foreach ($this->recorder->getRecords($this->__toString()) as $record) {
            yield $record->getSourceId();
        }
    }

    /**
     * @return int|null
     */
    public function countMigratedItems(): ?int
    {
        return $this->recorder->countRecords($this->__toString());
    }

    /**
     * Returns TRUE if we can locate the destination ID in the tracker and the destination confirms record existence
     *
     * @param string $sourceId
     * @return bool
     */
    public function isMigrated(string $sourceId): bool
    {
        return (bool) $this->recorder->findRecord($sourceId, $this->__toString());
    }

    /**
     * Get the source ID iterator
     *
     * @return SourceIdIterator|SourceItem[]
     */
    public function getSourceIdIterator(): SourceIdIterator
    {
        return $this->source->getSourceIdIterator();
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
     * @param SourceItem $sourceItem
     * @return string  Destination Id
     */
    public function persist($preparedItem, SourceItem $sourceItem): string
    {
        // Do not allow migrating if already migrated (must manually revert first)
        if ($this->isMigrated($sourceItem->getId())) {
            throw new AlreadyMigratedException(sprintf(
                'Record (type %s) with ID is already migrated: %s',
                (string) $this,
                $sourceItem->getId()
            ));
        }

        $destinationId = $this->destination->persist($preparedItem);
        $this->recordMigrate($sourceItem, $destinationId);
        return $destinationId;
    }

    /**
     * Remove the record
     *
     * @param string $sourceId
     * @return bool  TRUE if record was found and removed, FALSE if not found
     * @throws MissingItemException Throw exception if destination not found
     */
    public function remove(string $sourceId): bool
    {
        $removed = $this->destination->remove($this->getDestinationIdForSourceId($sourceId));
        $this->recordRevert($sourceId);
        return $removed;
    }

    /**
     * Record that a migration has occurred
     *
     * @param SourceItem $sourceItem
     * @param string $destinationId
     * @return MigrateRecordInterface
     */
    protected function recordMigrate(SourceItem $sourceItem, string $destinationId): MigrateRecordInterface
    {
        return $this->recorder->addMigrateRecord($sourceItem, $destinationId, $this->__toString());
    }

    /**
     * @param string $sourceId
     */
    protected function recordRevert(string $sourceId)
    {
        $this->recorder->removeMigrateRecord($sourceId, $this->__toString());
    }

    /**
     * @param string $sourceId
     * @return string
     */
    protected function getDestinationIdForSourceId(string $sourceId): string
    {
        if ($record = $this->recorder->findRecord($sourceId, $this->__toString())) {
            return $record->getDestinationId();
        } else {
            throw new MissingItemException(sprintf(
                'Missing destination ID for item (type: %s) with source ID: %s',
                $this->__toString(),
                $sourceId
            ));
        }
    }
}
