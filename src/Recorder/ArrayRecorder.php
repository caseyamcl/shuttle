<?php

namespace Shuttle\Recorder;

use Shuttle\SourceItem;

/**
 * Class ArrayRecorder
 * @package ShuttleTest\Fixture
 */
class ArrayRecorder implements RecorderInterface
{
    /**
     * @var array|MigrateRecord[]
     */
    private $records;

    /**
     * Record a migration action
     *
     * @param SourceItem $source
     * @param string $destinationId
     * @param string $type
     * @return MigrateRecordInterface
     */
    public function addMigrateRecord(SourceItem $source, string $destinationId, string $type): MigrateRecordInterface
    {

        $this->records[$type][$source->getId()] = new MigrateRecord($source->getId(), $destinationId, $type);
        return $this->records[$type][$source->getId()];
    }

    /**
     * Record (or remove record) a revert action
     *
     * @param string $sourceId
     * @param string $type
     */
    public function removeMigrateRecord(string $sourceId, string $type)
    {
        unset($this->records[$type][$sourceId]);
    }

    /**
     * Find a migration record; returns NULL if an item is not migrated
     *
     * @param string $sourceId
     * @param string $type
     * @return MigrateRecordInterface|null
     */
    public function findRecord(string $sourceId, string $type): ?MigrateRecordInterface
    {
        return $this->records[$type][$sourceId] ?? null;
    }

    /**
     * Find records for an item type
     *
     * @param string $type
     * @return iterable|MigrateRecordInterface[]
     */
    public function getRecords(string $type): iterable
    {
        return $this->records[$type] ?? [];
    }

    /**
     * @param string $type
     * @return int|null
     */
    public function countRecords(string $type): ?int
    {
        return count($this->records[$type] ?? []);
    }
}