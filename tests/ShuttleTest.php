<?php

namespace Shuttle;

use PHPUnit\Framework\TestCase;
use Shuttle\Event\ActionResultInterface;
use Shuttle\Event\PrePersistEvent;
use Shuttle\Event\ReadSourceEvent;
use Shuttle\Helper\Tracker;
use ShuttleTest\Fixture\TestMigrator;

/**
 * Class ShuttleTest
 * @package Shuttle
 */
class ShuttleTest extends TestCase
{
    public function testInstantiationSucceeds()
    {
        $obj = $this->createNewShuttle();
        $this->assertInstanceOf(Shuttle::class, $obj);
    }

    public function testMigrateSucceeds()
    {
        $shuttle = $this->createNewShuttle();
        $tracker = Tracker::createAndAttach(ShuttleAction::MIGRATE, $shuttle->getEventDispatcher());

        $shuttle->migrate(TestMigrator::NAME);
        $this->assertEquals(6, $tracker->getTotalCount());
        $this->assertEquals(1, $tracker->getProcessedCount());
        $this->assertEquals(2, $tracker->getSkippedCount());
        $this->assertEquals(3, $tracker->getFailedCount());
    }

    public function testExpectedEventsAreDispatchedForValidItem()
    {
        $shuttle = $this->createNewShuttle();

        $readSourceRecord = null;
        $prePersistRecord = null;
        $migrateResult = null;

        $shuttle->getEventDispatcher()->addListener(ShuttleEvents::READ_SOURCE_RECORD, function(ReadSourceEvent $event) use (&$readSourceRecord) {
            $readSourceRecord = $event;
        });
        $shuttle->getEventDispatcher()->addListener(ShuttleEvents::PRE_PERSIST, function(PrePersistEvent $event) use (&$prePersistRecord) {
            $prePersistRecord = $event;
        });
        $shuttle->getEventDispatcher()->addListener(ShuttleEvents::MIGRATE_RESULT, function(ActionResultInterface $event) use (&$migrateResult) {
            $migrateResult = $event;
        });

        $shuttle->migrate(TestMigrator::NAME, [1]);

        $this->assertInstanceOf(ReadSourceEvent::class, $readSourceRecord);
        $this->assertInstanceOf(PrePersistEvent::class, $prePersistRecord);
        $this->assertInstanceOf(ActionResultInterface::class, $migrateResult);
    }

    public function testExpectedEventsAreDispatchedForUnmetDependencyItem()
    {

    }

    public function testExpectedEventsAreDispatchedForAlreadyMigratedItem()
    {

    }

    public function testExpectedEventsAreDispatchedForPrepareFailure()
    {

    }

    public function testExpectedEventsAreDispatchedForPersistFailure()
    {

    }

    // --------------------------------------------------------------

    /**
     * @return Shuttle
     */
    private function createNewShuttle(): Shuttle
    {
        return new Shuttle(new MigratorCollection([new TestMigrator(false)]));
    }
}
