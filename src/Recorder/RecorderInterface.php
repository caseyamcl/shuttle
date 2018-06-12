<?php

namespace Shuttle\Recorder;

use Shuttle\Recorder\MigratorRecordInterface;
use Shuttle\SourceItem;

/**
 * Class RecorderInterface
 * @package Shuttle\NewShuttle
 */
interface RecorderInterface
{
    /**
     * Record a migration action
     *
     * @param SourceItem $source
     * @param string $destinationId
     * @param string $type
     * @return MigratorRecordInterface
     */
    public function recordMigrate(SourceItem $source, string $destinationId, string $type): MigratorRecordInterface;


    /**
     * Record (or remove record) a revert action
     *
     * @param SourceItem $source
     * @param string $destinationId
     * @param string $type
     * @return mixed
     */
    public function recordRevert(SourceItem $source, string $destinationId, string $type);

    /**
     * Find a migration record; returns NULL if an item is not migrated
     *
     * @param string $sourceId
     * @param string $type
     * @return MigratorRecordInterface|null
     */
    public function findMigrationRecord(string $sourceId, string $type): ?MigratorRecordInterface;

    /**
     * Find records for an item type
     *
     * @param string $type
     * @return iterable|MigratorRecordInterface[]
     */
    public function findRecords(string $type): iterable;
}