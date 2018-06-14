<?php

namespace Shuttle\Recorder;

use Shuttle\Recorder\MigrateRecordInterface;
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
     * @return MigrateRecordInterface
     */
    public function addMigrateRecord(SourceItem $source, string $destinationId, string $type): MigrateRecordInterface;


    /**
     * Record (or remove record) a revert action
     *
     * @param string $sourceId
     * @param string $type
     */
    public function removeMigrateRecord(string $sourceId, string $type);

    /**
     * Find a migration record; returns NULL if an item is not migrated
     *
     * @param string $sourceId
     * @param string $type
     * @return MigrateRecordInterface|null
     */
    public function findRecord(string $sourceId, string $type): ?MigrateRecordInterface;

    /**
     * Find records for an item type
     *
     * @param string $type
     * @return iterable|MigrateRecordInterface[]
     */
    public function getRecords(string $type): iterable;

    /**
     * @param string $type
     * @return int|null
     */
    public function countRecords(string $type): ?int;
}
