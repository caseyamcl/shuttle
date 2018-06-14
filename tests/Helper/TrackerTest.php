<?php

namespace ShuttleTest\Helper;

use PHPUnit\Framework\TestCase;
use Shuttle\Event\MigrateFailedEvent;
use Shuttle\Event\MigrateProcessedEvent;
use Shuttle\Event\MigrateSkippedEvent;
use Shuttle\Event\RevertFailedEvent;
use Shuttle\Event\RevertProcessedEvent;
use Shuttle\Event\RevertSkippedEvent;
use Shuttle\Helper\Tracker;
use Shuttle\Recorder\MigrateRecordInterface;
use Shuttle\ShuttleAction;
use Shuttle\ShuttleEvents;
use Shuttle\SourceItem;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class TrackerTest
 * @package ShuttleTest\Helper
 */
class TrackerTest extends TestCase
{
    public function testInstantiationSucceeds()
    {
        $obj = new Tracker(ShuttleAction::MIGRATE);
        $this->assertInstanceOf(Tracker::class, $obj);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidActionThrowsException()
    {
        new Tracker("foobar");
    }

    public function testTrackMigrateIncrementsValuesCorrectly()
    {
        $obj = new Tracker(ShuttleAction::MIGRATE);

        foreach ($this->getMigrateRecords() as $rec) {
            $obj->track($rec);
        }

        $this->assertEquals(6, $obj->getProcessedCount());
        $this->assertEquals(3, $obj->getSkippedCount());
        $this->assertEquals(2, $obj->getFailedCount());
        $this->assertEquals(11, $obj->getTotalCount());

        $this->assertEquals(3, $obj->getProcessedCount('test1'));
        $this->assertEquals(2, $obj->getSkippedCount('test1'));
        $this->assertEquals(0, $obj->getFailedCount('test1'));
        $this->assertEquals(5, $obj->getTotalCount('test1'));

        $this->assertEquals(3, $obj->getProcessedCount('test2'));
        $this->assertEquals(1, $obj->getSkippedCount('test2'));
        $this->assertEquals(2, $obj->getFailedCount('test2'));
        $this->assertEquals(6, $obj->getTotalCount('test2'));
    }

    public function testTrackRevertIncrementsValuesCorrectly()
    {
        $obj = new Tracker(ShuttleAction::REVERT);

        foreach ($this->getRevertRecords() as $rec) {
            $obj->track($rec);
        }

        $this->assertEquals(6, $obj->getProcessedCount());
        $this->assertEquals(3, $obj->getSkippedCount());
        $this->assertEquals(2, $obj->getFailedCount());
        $this->assertEquals(11, $obj->getTotalCount());

        $this->assertEquals(3, $obj->getProcessedCount('test1'));
        $this->assertEquals(2, $obj->getSkippedCount('test1'));
        $this->assertEquals(0, $obj->getFailedCount('test1'));
        $this->assertEquals(5, $obj->getTotalCount('test1'));

        $this->assertEquals(3, $obj->getProcessedCount('test2'));
        $this->assertEquals(1, $obj->getSkippedCount('test2'));
        $this->assertEquals(2, $obj->getFailedCount('test2'));
        $this->assertEquals(6, $obj->getTotalCount('test2'));
    }

    public function testEventDispatchingIncrementsValuesCorrectly()
    {
        $dispatcher = new EventDispatcher();
        $obj = new Tracker(ShuttleAction::MIGRATE);
        $dispatcher->addSubscriber($obj);

        foreach ($this->getMigrateRecords() as $migrateRecord) {
            $dispatcher->dispatch(ShuttleEvents::MIGRATE_RESULT, $migrateRecord);
        }

        $this->assertEquals(6, $obj->getProcessedCount());
        $this->assertEquals(3, $obj->getSkippedCount());
        $this->assertEquals(2, $obj->getFailedCount());
        $this->assertEquals(11, $obj->getTotalCount());

        $this->assertEquals(3, $obj->getProcessedCount('test1'));
        $this->assertEquals(2, $obj->getSkippedCount('test1'));
        $this->assertEquals(0, $obj->getFailedCount('test1'));
        $this->assertEquals(5, $obj->getTotalCount('test1'));

        $this->assertEquals(3, $obj->getProcessedCount('test2'));
        $this->assertEquals(1, $obj->getSkippedCount('test2'));
        $this->assertEquals(2, $obj->getFailedCount('test2'));
        $this->assertEquals(6, $obj->getTotalCount('test2'));
    }

    /**
     * Provides:
     *   Total:   6 success / 3 skipped / 2 failed (11 total)
     *   'test1': 3 success / 2 skipped / 0 failed (5 total)
     *   'test2': 3 success / 1 skipped / 2 failed (6 total)
     *
     * @return array
     */
    protected function getMigrateRecords()
    {
        /** @var SourceItem $sourceItemMock */
        $sourceItemMock = $this->getMockBuilder(SourceItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MigrateRecordInterface $recordMock */
        $recordMock = $this->getMockBuilder(MigrateRecordInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            new MigrateProcessedEvent('test1', $sourceItemMock, 'test100'),
            new MigrateProcessedEvent('test1', $sourceItemMock, 'test100'),
            new MigrateProcessedEvent('test1', $sourceItemMock, 'test100'),
            new MigrateProcessedEvent('test2', $sourceItemMock, 'test200'),
            new MigrateProcessedEvent('test2', $sourceItemMock, 'test200'),
            new MigrateProcessedEvent('test2', $sourceItemMock, 'test200'),
            new MigrateSkippedEvent('test1', 1, 'test'),
            new MigrateSkippedEvent('test1', 2, 'test'),
            new MigrateSkippedEvent('test2', 3, 'test'),
            new MigrateFailedEvent('test2', 1, 'test'),
            new MigrateFailedEvent('test2', 2, 'test')
        ];
    }

    /**
     * Provides:
     *   Total:   6 success / 3 skipped / 2 failed (11 total)
     *   'test1': 3 success / 2 skipped / 0 failed (5 total)
     *   'test2': 3 success / 1 skipped / 2 failed (6 total)
     *
     * @return array
     */
    protected function getRevertRecords()
    {
        return [
            new RevertProcessedEvent('test1', 1, true),
            new RevertProcessedEvent('test1', 2, false),
            new RevertProcessedEvent('test1', 3, false),
            new RevertProcessedEvent('test2', 1, true),
            new RevertProcessedEvent('test2', 2, true),
            new RevertProcessedEvent('test2', 3, false),
            new RevertSkippedEvent('test1', 4, 'test'),
            new RevertSkippedEvent('test1', 5, 'test'),
            new RevertSkippedEvent('test2', 4, 'test'),
            new RevertFailedEvent('test2', 6, 'test'),
            new RevertFailedEvent('test2', 7, 'test')
        ];
    }
}
