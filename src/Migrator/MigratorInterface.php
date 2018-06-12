<?php

namespace Shuttle\Migrator;

use Shuttle\Exception\MissingItemException;
use Shuttle\Recorder\MigratorRecordInterface;
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
     * Get a report of
     *
     * @return \iterable|MigratorRecordInterface[]
     */
    public function getReport(): iterable;

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
     * @return iterable|SourceItem[]
     */
    public function getSourceIterator(): iterable;

    /**
     * @param SourceItem $sourceItem
     * @return mixed
     */
    public function prepare(SourceItem $sourceItem);

    /**
     * @param mixed $preparedItem
     * @return string  Destination Id
     */
    public function persist($preparedItem): string;

    /**
     * @param string $sourceId
     * @throws \RuntimeException  Throw exception if destination not found
     */
    public function remove(string $sourceId);

    /**
     * Record that a migration has occurred
     *
     * @param SourceItem $sourceItem
     * @param string $destinationId
     * @return MigratorRecordInterface
     */
    public function recordMigrate(SourceItem $sourceItem, string $destinationId): MigratorRecordInterface;

}