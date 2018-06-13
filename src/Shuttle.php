<?php

namespace Shuttle;

use SebastianBergmann\FileIterator\Iterator;
use Shuttle\Event\AbortEvent;
use Shuttle\Event\ActionResultInterface;
use Shuttle\Event\MigrateFailedEvent;
use Shuttle\Event\MigrateProcessedEvent;
use Shuttle\Event\MigrateSkippedEvent;
use Shuttle\Event\PrePersistEvent;
use Shuttle\Event\ReadSourceEvent;
use Shuttle\Event\RevertFailedEvent;
use Shuttle\Event\RevertProcessedEvent;
use Shuttle\Event\RevertSkippedEvent;
use Shuttle\Exception\AlreadyMigratedException;
use Shuttle\Exception\UnmetDependencyException;
use Shuttle\Migrator\MigratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Shuttle
 * @package Shuttle
 */
class Shuttle
{
    /**
     * @var MigratorCollection
     */
    private $migratorCollection;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Shuttle constructor.
     *
     * @param MigratorCollection $collection
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(MigratorCollection $collection, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->migratorCollection = $collection;
        $this->eventDispatcher = $eventDispatcher ?: New EventDispatcher();
    }

    /**
     * @return MigratorCollection
     */
    public function getMigrators(): MigratorCollection
    {
        return $this->migratorCollection;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * Migrate some or all items
     *
     * @param string $migratorName
     * @param iterable|string[] $sourceIds Source IDs to migrate, or null for all
     * @param callable|null $continue A callable that accepts the last action, or null (return TRUE to continue)
     */
    public function migrate(string $migratorName, ?iterable $sourceIds = null, callable $continue = null)
    {
        $migrator = $this->getMigrators()->get($migratorName);
        $continue = $continue ?: function () {
            return true;
        };

        // Build an iterator
        /** @var Iterator $iterator */
        if ($sourceIds) {
            $iterator = function () use ($migrator, $sourceIds) {
                foreach ($sourceIds as $sourceId) {
                    yield $migrator->getSourceItem($sourceId);
                }
            };
        } else {
            $iterator = $migrator->getSourceIterator();
        }

        // Loop
        for ($iterator->rewind(), $lastAction = null; $iterator->valid(); $iterator->next()) {

            if (! $continue) {
                $this->eventDispatcher->dispatch(
                    ShuttleEvents::ABORT,
                    new AbortEvent(ShuttleAction::MIGRATE, (string) $migrator, $lastAction)
                );
                break;
            }

            /** @var SourceItem $sourceItem */
            $sourceItem = $iterator->current();
            $result = $this->migrateItem($migrator, $sourceItem);

            $lastAction = $result;
        }
    }

    /**
     * Revert some or all items
     *
     * @param string $migratorName
     * @param iterable|string[] $sourceIds Source IDs to migrate, or null for all
     * @param callable|null $continue A callable that accepts the last action, or null (return TRUE to continue)
     */
    public function revert(string $migratorName, ?iterable $sourceIds = null, callable $continue = null)
    {
        $migrator = $this->getMigrators()->get($migratorName);
        $continue = $continue ?: function () {
            return true;
        };

        $iterator = $sourceIds ?: function () use ($migrator) {
            foreach ($migrator->getMigrateRecords() as $record) {
                yield $record->getSourceId();
            }
        };

        /** @var string[] $sourceId */
        $lastAction = null;
        foreach ($iterator as $sourceId) {

            if (!$continue()) {
                $this->eventDispatcher->dispatch(
                    ShuttleEvents::ABORT,
                    new AbortEvent(ShuttleAction::REVERT, (string) $migrator, $lastAction)
                );
                break;
            }

            $lastAction = $this->revertItem($migrator, $sourceId);
        }
    }

    /**
     * Migrate an item
     *
     * @param MigratorInterface $migrator
     * @param SourceItem $sourceItem
     * @return ActionResultInterface
     */
    private function migrateItem(MigratorInterface $migrator, SourceItem $sourceItem): ActionResultInterface
    {
        try {
            $this->eventDispatcher->dispatch(
                ShuttleEvents::READ_SOURCE_RECORD,
                new ReadSourceEvent($sourceItem, (string) $migrator)
            );

            $prepared = $migrator->prepare($sourceItem);

            $this->eventDispatcher->dispatch(
                ShuttleEvents::PRE_PERSIST,
                new PrePersistEvent($sourceItem, $prepared, (string) $migrator)
            );

            $destinationId = $migrator->persist($prepared, $sourceItem);
            $result = new MigrateProcessedEvent((string) $migrator, $sourceItem, $destinationId);

        } catch (AlreadyMigratedException $e) {
            $result = new MigrateSkippedEvent(
                (string) $migrator,
                $sourceItem->getId(),
                'Item is already migrated'
            );
        }
        catch (UnmetDependencyException $e) {
            $result = new MigrateFailedEvent(
                (string) $migrator,
                $sourceItem->getId(),
                'Unmet dependency: ' . $e->getMessage(),
                $e
            );
        }
        catch (\Throwable $e) {
            $result = new MigrateFailedEvent(
                (string) $migrator,
                $sourceItem->getId(),
                'An unexpected error occurred',
                $e
            );
        }

        $this->getEventDispatcher()->dispatch(ShuttleEvents::MIGRATE_RESULT, $result);
        return $result;
    }

    /**
     * Revert an item
     *
     * @param MigratorInterface $migrator
     * @param string $sourceId
     * @return ActionResultInterface
     */
    private function revertItem(MigratorInterface $migrator, string $sourceId): ActionResultInterface
    {
        try {
            if ($migrator->isMigrated($sourceId)) {
                $actuallyDeleted = $migrator->remove($sourceId);
                $result = new RevertProcessedEvent((string) $migrator, $sourceId, $actuallyDeleted);

            } else {
                $result = new RevertSkippedEvent((string) $migrator, $sourceId, 'Destination record no longer found');
            }

        }
        catch (\Throwable $e) {
            $result = new RevertFailedEvent((string) $migrator, $sourceId, 'An unexpected error occurred', $e);
        }

        $this->getEventDispatcher()->dispatch(ShuttleEvents::REVERT_RESULT, $result);
        return $result;
    }
}