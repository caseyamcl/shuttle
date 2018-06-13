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

    public const SOURCE_ITEMS = [
        /* PROCESSED */ 1 => ['result' => 'succeeds'],
        /* SKIPPED   */ 2 => ['result' => 'already-migrated'],
        /* FAILED    */ 3 => ['result' => 'unmet-dependency'],
        /* FAILED    */ 4 => ['result' => 'prepare-exception'],
        /* FAILED    */ 5 => ['result' => 'persist-exception'],
        /* SKIPPED   */ 6 => ['result' => 'recorded-but-not-migrated'],

        /* FAILED */ 7 => ['result' => 'read-exception']
    ];

    public const DESTINATION_ITEMS = [
        100 => ['already' => 'migrated']
    ];

    /**
     * TestMigrator constructor.
     * @param bool $includeFailedRead
     */
    public function __construct(bool $includeFailedRead = true)
    {
        $recorder = new ArrayRecorder();
        $recorder->addMigrateRecord(new SourceItem(2, self::SOURCE_ITEMS[2]), '100', (string) $this);
        $recorder->addMigrateRecord(new SourceItem(6, self::SOURCE_ITEMS[6]), '200', (string) $this);

        $sourceArray = self::SOURCE_ITEMS;
        if (! $includeFailedRead) {
            array_pop($sourceArray);
        }


        parent::__construct(
            new ArraySource($sourceArray),
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
     * @return iterable|\Generator
     */
    public function getSourceIterator(): iterable
    {
        foreach (parent::getSourceIterator() as $item) {
            if (($item['result'] ?? '') == 'read-exception') {
                throw new \RuntimeException('Test read exception');
            }
            else {
                yield $item;
            }
        }
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
        }
        else {
            return parent::persist($preparedItem, $sourceItem);
        }
    }
}
