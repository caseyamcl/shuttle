<?php

namespace Shuttle\Migrator;

use phpDocumentor\Reflection\DocBlock\Tags\Source;
use Shuttle\Exception\MissingItemException;
use Shuttle\Exception\UnmetDependencyException;
use Shuttle\Recorder\MigrateRecordInterface;
use Shuttle\SourceItem;

/**
 * Class MigratorInterface
 * @package Shuttle\Migrator
 */
interface MigratorInterface
{
    // --------------------------------------------------------------
    // Migrator Metadata

    /**
     * A unique name for the migrator; can be the class name, or some other machine-name-friendly identifier
     * (if a single class is used in multiple instances)
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    // --------------------------------------------------------------
    // Reporting

    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int;

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem;

    /**
     * Iterate over migrated source IDs
     *
     * @return iterable|string[]
     */
    public function getMigratedSourceIdIterator(): iterable;

    /**
     * @param string $sourceId
     * @return bool
     */
    public function isMigrated(string $sourceId): bool;

    // --------------------------------------------------------------
    // Migrator Dependencies Management

    /**
     * @return array|string[]
     */
    public function getDependsOn(): array;

    // --------------------------------------------------------------
    // Migration process

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return iterable|string[]
     */
    public function getSourceIdIterator(): iterable;

    /**
     * @param SourceItem $sourceItem
     * @return mixed
     */
    public function prepare(SourceItem $sourceItem);

    /**
     * @param mixed $preparedItem
     * @param SourceItem $sourceItem
     * @return string  Destination Id
     * @throws UnmetDependencyException  If we try to save an item with an unmet dependency
     */
    public function persist($preparedItem, SourceItem $sourceItem): string;

    /**
     * @param string $sourceId
     * @return bool  TRUE if record was found and removed, FALSE if not found
     */
    public function remove(string $sourceId): bool;
}