<?php

namespace ShuttleTest\Migrator;

use PHPUnit\Framework\TestCase;
use Shuttle\Exception\AlreadyMigratedException;
use Shuttle\Migrator\MigratorInterface;
use ShuttleTest\Fixture\TestMigrator;

/**
 * Class MigratorTest
 * @package ShuttleTest\Migrator
 */
class MigratorTest extends TestCase
{
    public function testInstantiationSucceeds()
    {
        $migrator = new TestMigrator();
        $this->assertInstanceOf(MigratorInterface::class, $migrator);
    }

    public function testIterateIdsSucceeds()
    {
        $iterator = (new TestMigrator())->getSourceIdIterator();
        $iterator = ($iterator instanceof \Traversable) ? iterator_to_array($iterator) : $iterator;
        $this->assertEquals(7, count($iterator));
    }


    public function testReadItemsSucceed()
    {
        $migrator = new TestMigrator();
        $iterator = $migrator->getSourceIdIterator();

        $goodCount = 0;
        $badCount = 0;
        foreach ($iterator as $sourceId) {
            try {
                $source = $migrator->getSourceItem($sourceId);
                $goodCount++;
            } catch (\Throwable $e) {
                $badCount++;
            }
        }

        $this->assertEquals(6, $goodCount);
        $this->assertEquals(1, $badCount);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReadThrowsExceptionOnItemSeven()
    {
        $migrator = new TestMigrator();
        $migrator->getSourceItem(7);

    }

    public function testReadGoodItemByIdReturnsSourceItem()
    {
        $migrator = new TestMigrator();
        $item = $migrator->getSourceItem(1);
        $this->assertEquals('1', $item->getId());
        $this->assertEquals(['result' => 'succeeds'], $item->getData());
        $this->assertEquals('succeeds', $item['result']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReadBadItemThrowsException()
    {
        $migrator = new TestMigrator();
        $migrator->getSourceItem(7);
    }

    public function testPrepareSucceedsForGoodItem()
    {
        $migrator = new TestMigrator();
        $item = $migrator->getSourceItem(1);

        $prepared = $migrator->prepare($item);
        $this->assertEquals(['result' => 'succeeds'], $prepared);
    }

    public function testPersistGeneratesExpectedIdForGoodItem()
    {
        $migrator = new TestMigrator();
        $item = $migrator->getSourceItem(1);
        $prepared = $migrator->prepare($item);
        $destinationId = $migrator->persist($prepared, $item);
        $this->assertEquals('200', $destinationId);
    }

    public function testAlreadyMigrated()
    {
        $migrator = new TestMigrator();
        $this->assertTrue($migrator->isMigrated(2));
    }

    /**
     * @expectedException \Shuttle\Exception\UnmetDependencyException
     */
    public function testPrepareThrowsUnmetDependencyForItem()
    {
        $migrator = new TestMigrator();
        $item = $migrator->getSourceItem(3);
        $migrator->prepare($item);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testPrepareThrowsExceptionWhenExpected()
    {
        $migrator = new TestMigrator();
        $item = $migrator->getSourceItem(4);
        $migrator->prepare($item);
    }

    public function testPersistThrowsExceptionWhenExpected()
    {
        $migrator = new TestMigrator();
        $item = $migrator->getSourceItem(5);
        $prepared = $migrator->prepare($item);

        try {
            $migrator->persist($prepared, $item);
            $this->fail('Exception should have been thrown');
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testRecorderReturnsRecordWhenItemMigrated()
    {
        $migrator = new TestMigrator();
        $source = $migrator->getSourceItem(1);
        $prepared = $migrator->prepare($source);
        $migrator->persist($prepared, $source);

        $this->assertTrue($migrator->isMigrated(1));
    }

    /**
     * @expectedException \Shuttle\Exception\AlreadyMigratedException
     */
    public function testMigratorThrowsAlreadyMigratedExceptionIfAttemptingToWriteExistingRecord()
    {
        try {
            $migrator = new TestMigrator();
            $source = $migrator->getSourceItem(1);
            $prepared = $migrator->prepare($source);
            $migrator->persist($prepared, $source);
        } catch (AlreadyMigratedException $e) {
            // Should have no exception here...
        }

        $migrator->persist($prepared, $source); // throw here...
    }

    public function testRemoveReturnsTrueWhenRecordActuallyRemoved()
    {
        $migrator = new TestMigrator();
        $this->assertTrue($migrator->remove(2)); // Item '2' is already in the destination
    }

    public function testRemoveReturnsFalseWhenRecordNotRemoved()
    {
        $migrator = new TestMigrator();
        $this->assertFalse($migrator->remove(6)); // Item '7' is in the recorder, but not actually in destination
    }

    /**
     * @expectedException \Shuttle\Exception\MissingItemException
     */
    public function testRemoveThrowsExceptionWhenRecordNotRecorded()
    {
        $migrator = new TestMigrator();
        $this->assertTrue($migrator->remove(1)); // Item '1' is not already in the destination
    }
}