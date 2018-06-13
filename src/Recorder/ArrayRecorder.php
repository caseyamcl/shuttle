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
        $key = $source->getId() . '_' . $type;
        $this->records[$key] = new MigrateRecord($source->getId(), $destinationId, $type);
        return $this->records[$key];
    }

    /**
     * Record (or remove record) a revert action
     *
     * @param string $sourceId
     * @param string $type
     */
    public function removeMigrateRecord(string $sourceId, string $type)
    {
        $key = $sourceId . '_' . $type;
        unset($this->records[$key]);
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
        $key = $sourceId . '_' . $type;
        return (array_key_exists($key, $this->records)) ? $this->records[$key] : null;
    }

    /**
     * Find records for an item type
     *
     * @param string $type
     * @return iterable|MigrateRecordInterface[]
     */
    public function getRecords(string $type): iterable
    {
        return $this->records;
    }
}