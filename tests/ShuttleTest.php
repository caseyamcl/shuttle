<?php

namespace Shuttle;

use PHPUnit\Framework\TestCase;
use Shuttle\Event\ActionResultInterface;
use Shuttle\Event\RevertProcessedEvent;
use Shuttle\Helper\Tracker;
use ShuttleTest\Fixture\RecordingEventDispatcher;
use ShuttleTest\Fixture\TestMigrator;

/**
 * Shuttle Test
 *
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
        $this->assertEquals(7, $tracker->getTotalCount());
        $this->assertEquals(1, $tracker->getProcessedCount());
        $this->assertEquals(2, $tracker->getSkippedCount());
        $this->assertEquals(4, $tracker->getFailedCount());
    }

    public function testMigrateRunsCorrectlyWithIdIterator()
    {
        $shuttle = $this->createNewShuttle();
        $tracker = Tracker::createAndAttach(ShuttleAction::MIGRATE, $shuttle->getEventDispatcher());

        $shuttle->migrate(TestMigrator::NAME, [TestMigrator::ITEM_SUCCEEDS_ID, 'non-existent-id']);
        $this->assertEquals(2, $tracker->getTotalCount());
        $this->assertEquals(1, $tracker->getProcessedCount()); // Succeeds
        $this->assertEquals(1, $tracker->getFailedCount());    // Read error on 'non-existent-id'
    }

    public function testExpectedEventsAreDispatchedForValidItem()
    {
        $shuttle = $this->createNewShuttle();
        /** @var RecordingEventDispatcher $dispatcher */
        $dispatcher = $shuttle->getEventDispatcher();

        $shuttle->migrate(TestMigrator::NAME, [TestMigrator::ITEM_SUCCEEDS_ID]);

        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::READ_SOURCE_RECORD));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::PRE_PERSIST));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::MIGRATE_RESULT));
    }

    public function testExpectedEventsAreDispatchedForUnmetDependencyItem()
    {
        $shuttle = $this->createNewShuttle();
        /** @var RecordingEventDispatcher $dispatcher */
        $dispatcher = $shuttle->getEventDispatcher();

        $shuttle->migrateItem(TestMigrator::NAME, TestMigrator::ITEM_UNMET_DEPENDENCY_ID);

        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::READ_SOURCE_RECORD));
        $this->assertFalse($dispatcher->eventWasDispatched(ShuttleEvents::PRE_PERSIST));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::MIGRATE_RESULT));

        $this->assertEquals(
            ActionResultInterface::FAILED,
            $dispatcher->findFirstEvent(ShuttleEvents::MIGRATE_RESULT)->getStatus()
        );
    }

    public function testExpectedEventsAreDispatchedForAlreadyMigratedItem()
    {
        $shuttle = $this->createNewShuttle();
        /** @var RecordingEventDispatcher $dispatcher */
        $dispatcher = $shuttle->getEventDispatcher();

        $shuttle->migrateItem(TestMigrator::NAME, TestMigrator::ITEM_ALREADY_PROCESSED_ID);

        $this->assertFalse($dispatcher->eventWasDispatched(ShuttleEvents::READ_SOURCE_RECORD));
        $this->assertFalse($dispatcher->eventWasDispatched(ShuttleEvents::PRE_PERSIST));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::MIGRATE_RESULT));

        $this->assertEquals(
            ActionResultInterface::SKIPPED,
            $dispatcher->findFirstEvent(ShuttleEvents::MIGRATE_RESULT)->getStatus()
        );
    }

    public function testExpectedEventsAreDispatchedForPrepareFailure()
    {
        $shuttle = $this->createNewShuttle();
        /** @var RecordingEventDispatcher $dispatcher */
        $dispatcher = $shuttle->getEventDispatcher();

        $shuttle->migrateItem(TestMigrator::NAME, TestMigrator::ITEM_PREPARE_EXCEPTION_ID);

        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::READ_SOURCE_RECORD));
        $this->assertFalse($dispatcher->eventWasDispatched(ShuttleEvents::PRE_PERSIST));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::MIGRATE_RESULT));

        $this->assertEquals(
            ActionResultInterface::FAILED,
            $dispatcher->findFirstEvent(ShuttleEvents::MIGRATE_RESULT)->getStatus()
        );
    }

    public function testExpectedEventsAreDispatchedForPersistFailure()
    {
        $shuttle = $this->createNewShuttle();
        /** @var RecordingEventDispatcher $dispatcher */
        $dispatcher = $shuttle->getEventDispatcher();

        $shuttle->migrateItem(TestMigrator::NAME, TestMigrator::ITEM_PERSIST_EXCEPTION_ID);

        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::READ_SOURCE_RECORD));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::PRE_PERSIST));
        $this->assertTrue($dispatcher->eventWasDispatched(ShuttleEvents::MIGRATE_RESULT));

        $this->assertEquals(
            ActionResultInterface::FAILED,
            $dispatcher->findFirstEvent(ShuttleEvents::MIGRATE_RESULT)->getStatus()
        );
    }

    public function testRevert()
    {
        $shuttle = $this->createNewShuttle();
        /** @var RecordingEventDispatcher $dispatcher */
        $dispatcher = $shuttle->getEventDispatcher();

        $shuttle->revert(TestMigrator::NAME);

        /** @var RevertProcessedEvent $firstRevertResult */
        /** @var RevertProcessedEvent $secondRevertResult */
        $firstRevertResult  = $dispatcher->findNthEvent(ShuttleEvents::REVERT_RESULT, 0);
        $secondRevertResult = $dispatcher->findNthEvent(ShuttleEvents::REVERT_RESULT, 1);

        $this->assertEquals(RevertProcessedEvent::PROCESSED, $firstRevertResult->getStatus());
        $this->assertEquals(RevertProcessedEvent::PROCESSED, $secondRevertResult->getStatus());

        $this->assertTrue($firstRevertResult->isDeleteOccurred());
        $this->assertFalse($secondRevertResult->isDeleteOccurred());
    }

    public function testRevertRunsCorrectlyWithIdIterator()
    {
        $shuttle = $this->createNewShuttle();
        $tracker = Tracker::createAndAttach(ShuttleAction::REVERT, $shuttle->getEventDispatcher());

        $shuttle->revert(TestMigrator::NAME, [
            TestMigrator::ITEM_ALREADY_PROCESSED_ID,
            TestMigrator::ITEM_SUCCEEDS_ID,
            'non-existent-id']
        );

        $this->assertEquals(3, $tracker->getTotalCount());
        $this->assertEquals(1, $tracker->getProcessedCount()); // Succeeds
        $this->assertEquals(2, $tracker->getSkippedCount());   // skip on 'non-existent-id' and non-migrated ID
    }

    // --------------------------------------------------------------

    /**
     * @return Shuttle
     */
    private function createNewShuttle(): Shuttle
    {
        return new Shuttle(new MigratorCollection([new TestMigrator()]), new RecordingEventDispatcher());
    }
}
