<?php

namespace ShuttleTest\Fixture;

use Shuttle\Exception\UnmetDependencyException;
use Shuttle\MigrateDestination\ArrayDestination;
use Shuttle\MigrateSource\ArraySource;
use Shuttle\Migrator\Migrator;
use Shuttle\Recorder\ArrayRecorder;
use Shuttle\SourceItem;

/**
 * Class FakeMigrator
 * @package ShuttleTest\Fixture
 */
class TestMigrator extends Migrator
{
    public const NAME = 'Test';
    public const DESCRIPTION = 'Test Migrator';

    // --------------------------------------------------------------

    public const ITEM_SUCCEEDS_ID = 1;
    public const ITEM_ALREADY_PROCESSED_ID = 2;
    public const ITEM_UNMET_DEPENDENCY_ID = 3;
    public const ITEM_PREPARE_EXCEPTION_ID = 4;
    public const ITEM_PERSIST_EXCEPTION_ID = 5;
    public const ITEM_RECORDED_BUT_NOT_IN_DEST_ID = 6;
    public const ITEM_READ_FAIL_ID = 7;

    public const SOURCE_ITEMS = [
        /* PROCESSED */ self::ITEM_SUCCEEDS_ID                 => ['result' => 'succeeds'],
        /* SKIPPED   */ self::ITEM_ALREADY_PROCESSED_ID        => ['result' => 'already-migrated'],
        /* FAILED    */ self::ITEM_UNMET_DEPENDENCY_ID         => ['result' => 'unmet-dependency'],
        /* FAILED    */ self::ITEM_PREPARE_EXCEPTION_ID        => ['result' => 'prepare-exception'],
        /* FAILED    */ self::ITEM_PERSIST_EXCEPTION_ID        => ['result' => 'persist-exception'],
        /* SKIPPED   */ self::ITEM_RECORDED_BUT_NOT_IN_DEST_ID => ['result' => 'recorded-but-not-migrated'],
        /* FAILED    */ self::ITEM_READ_FAIL_ID                => ['result' => 'read-exception']
    ];

    public const DESTINATION_ITEMS = [
        100 => ['already' => 'migrated']
    ];

    /**
     * TestMigrator constructor.
     * @param bool $includeFailedRead
     */
    public function __construct()
    {
        $recorder = new ArrayRecorder();

        $recorder->addMigrateRecord(
            new SourceItem(
                self::ITEM_ALREADY_PROCESSED_ID,
                self::SOURCE_ITEMS[self::ITEM_ALREADY_PROCESSED_ID]
            ),
            '100',
            (string) $this
        );

        $recorder->addMigrateRecord(
            new SourceItem(
                self::ITEM_RECORDED_BUT_NOT_IN_DEST_ID,
                self::SOURCE_ITEMS[self::ITEM_RECORDED_BUT_NOT_IN_DEST_ID]
            ),
            '200',
            (string) $this
        );

        parent::__construct(
            new ArraySource(self::SOURCE_ITEMS),
            new ArrayDestination(self::DESTINATION_ITEMS),
            $recorder,
            [$this, 'prepare']
        );
    }

    public function getSourceItem(string $id): SourceItem
    {
        $item = parent::getSourceItem($id);

        if (($item['result'] ?? '') == 'read-exception') {
            throw new \RuntimeException('Test read exception');
        }

        return $item;
    }

    /**
     * @param SourceItem $item
     * @return array
     */
    public function prepare(SourceItem $item)
    {
        if (($item['result'] ?? '')  == 'prepare-exception') {
            throw new \RuntimeException('Test prepare exception');
        } elseif (($item['result'] ?? '')  == 'unmet-dependency') {
            throw new UnmetDependencyException('Test unmet dependency exception');
        } else {
            return $item->getData();
        }
    }

    /**
     * @param mixed $preparedItem
     * @param SourceItem $sourceItem
     * @return string
     */
    public function persist($preparedItem, SourceItem $sourceItem): string
    {
        if (($sourceItem['result'] ?? '')  == 'persist-exception') {
            throw new \RuntimeException('Test persist exception');
        } else {
            return parent::persist($preparedItem, $sourceItem);
        }
    }
}
