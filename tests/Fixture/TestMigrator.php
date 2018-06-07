<?php

namespace ShuttleTest\Fixture;

use Shuttle\Migrator\DestinationInterface;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\Migrator\SourceInterface;

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
    public function getSlug(): string
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
    public function listSourceIds(): iterable
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
    public function revert(string $destinationRecordId): bool
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
        return $this->getSlug();
    }
}