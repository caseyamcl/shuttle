<?php

namespace ShuttleTest\Fixture;

use Shuttle\Exception\MissingItemException;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\Recorder\MigratorRecordInterface;
use Shuttle\SourceItem;

/**
 * Class FakeMigrator
 * @package ShuttleTest\Fixture
 */
class TestMigrator implements MigratorInterface
{
    const DESCRIPTION = 'Test Migrator';

    /**
     * @var string
     */
    private $slug;
    /**
     * @var array
     */
    private $dependsOn;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var DestinationInterface
     */
    private $destination;

    /**
     * TestMigrator constructor.
     *
     * @param string $slug
     * @param array $dependsOn
     * @param SourceInterface|null $source
     * @param DestinationInterface|null $destination
     */
    public function __construct(
        string $slug,
        array $dependsOn = [],
        SourceInterface $source = null,
        DestinationInterface $destination = null
    ) {
        $this->slug = $slug;
        $this->dependsOn = $dependsOn;
        $this->source = $source ?: new ArraySource();
        $this->destination = $destination ?: new ArrayDestination();
    }

    /**
     * @return string  A machine-friendly identifier for the type of record being migrated (e.g. 'posts', 'authors'...)
     */
    public function getName(): string
    {
        return $this->slug;
    }

    /**
     * @return string  A description of the records being migrated
     */
    public function getDescription(): string
    {
        return static::DESCRIPTION;
    }

    /**
     * @return int  Number of records in the source
     */
    public function countSourceItems(): int
    {
        return $this->source->count();
    }

    /**
     * @return iterable|string[]
     */
    public function getSourceIdIterator(): iterable
    {
        return $this->source->listItemIds();
    }

    /**
     * @param string $sourceId
     * @return array
     */
    public function getItemFromSource(string $sourceId): array
    {
        return $this->source->getItem($sourceId);
    }

    /**
     * @param array $source
     * @return mixed
     */
    public function prepareSourceItem(array $source)
    {
        return $source;
    }

    /**
     * @param mixed $record
     * @return string
     */
    public function persistDestinationItem($record): string
    {
        return $this->destination->saveItem($record);
    }

    /**
     * Revert a single record
     *
     * @param string $destinationRecordId
     * @return bool  If the record was actually deleted, return TRUE, else FALSE
     */
    public function removeDestinationItem(string $destinationRecordId): bool
    {
        return $this->destination->deleteItem($destinationRecordId);
    }

    /**
     * Get a list of migrator slugs that should be migrated before this one
     *
     * NOTE: This is not a comprehensive; it does not list transitive dependencies.  Use
     * MigratorCollection::listDependencies() to determine all dependencies for a given migrator
     *
     * @return array|string[]
     */
    public function getDependsOn(): array
    {
        return $this->dependsOn;
    }

    /**
     * This should return the slug
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem
    {
        // TODO: Implement getSourceItem() method.
    }

    /**
     * Get a report of
     *
     * @return \iterable|MigratorRecordInterface[]
     */
    public function getReport(): iterable
    {
        // TODO: Implement getReport() method.
    }

    /**
     * @param string $sourceId
     * @return bool
     */
    public function isMigrated(string $sourceId): bool
    {
        // TODO: Implement isMigrated() method.
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
        // TODO: Implement getSourceIterator() method.
    }

    /**
     * @param SourceItem $sourceItem
     * @return mixed
     */
    public function prepare(SourceItem $sourceItem)
    {
        // TODO: Implement prepare() method.
    }

    /**
     * @param mixed $preparedItem
     * @return string  Destination Id
     */
    public function persist($preparedItem): string
    {
        // TODO: Implement persist() method.
    }

    /**
     * @param string $sourceId
     * @throws \RuntimeException  Throw exception if destination not found
     */
    public function remove(string $sourceId)
    {
        // TODO: Implement remove() method.
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
        // TODO: Implement recordMigrate() method.
    }
}
