<?php

namespace Shuttle;

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
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
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
     * @param string|MigratorInterface $migrator
     * @param iterable|string[] $sourceIds Source IDs to migrate, or null for all
     * @param callable|null $continue A callable that accepts the last action, or null (return TRUE to continue)
     */
    public function migrate(string $migrator, ?iterable $sourceIds = null, callable $continue = null)
    {
        $migrator = $this->resolveMigrator($migrator);
        $continue = $continue ?: function () {
            return true;
        };

        // Build a source ID iterator
        if (! $sourceIds instanceof SourceIdIterator) {
            $sourceIds = $sourceIds ? new SourceIdIterator($sourceIds) : $migrator->getSourceIdIterator();
        }

        // Loop
        for ($sourceIds->rewind(), $lastAction = null; $sourceIds->valid(); $sourceIds->next()) {
            if (! $continue($lastAction)) {
                $this->eventDispatcher->dispatch(
                    ShuttleEvents::ABORT,
                    new AbortEvent(ShuttleAction::MIGRATE, (string) $migrator, $lastAction)
                );
                break;
            }

            $lastAction = $this->migrateItem($migrator, $sourceIds->current());
        }
    }

    /**
     * Revert some or all items
     *
     * @param string|MigratorInterface $migrator
     * @param iterable|string[] $sourceIds Source IDs to migrate, or null for all
     * @param callable|null $continue A callable that accepts the last action, or null (return TRUE to continue)
     */
    public function revert(string $migrator, ?iterable $sourceIds = null, callable $continue = null)
    {
        $migrator = $this->resolveMigrator($migrator);
        $continue = $continue ?: function () {
            return true;
        };

        if (! $sourceIds instanceof SourceIdIterator) {
            $sourceIds  = $sourceIds ? new SourceIdIterator($sourceIds) : $migrator->getMigratedSourceIdIterator();
        }

        for ($lastAction = null, $sourceIds->rewind(); $sourceIds->valid(); $sourceIds->next()) {
            $sourceId = $sourceIds->current();

            if (!$continue($lastAction)) {
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
     * @param string|MigratorInterface $migrator
     * @param string $sourceItemId
     * @return ActionResultInterface
     */
    public function migrateItem(string $migrator, string $sourceItemId): ActionResultInterface
    {
        $migrator = $this->resolveMigrator($migrator);

        try {
            if ($migrator->isMigrated($sourceItemId)) {
                throw new AlreadyMigratedException();
            }

            $sourceItem = $migrator->getSourceItem($sourceItemId);
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
                $sourceItemId,
                'Item is already migrated'
            );
        } catch (UnmetDependencyException $e) {
            $result = new MigrateFailedEvent(
                (string) $migrator,
                $sourceItemId,
                'Unmet dependency: ' . $e->getMessage(),
                $e
            );
        } catch (\Throwable $e) {
            $result = new MigrateFailedEvent(
                (string) $migrator,
                $sourceItemId,
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
     * @param string|MigratorInterface $migrator
     * @param string $sourceId
     * @return ActionResultInterface
     */
    public function revertItem(string $migrator, string $sourceId): ActionResultInterface
    {
        $migrator = $this->resolveMigrator($migrator);

        try {
            if ($migrator->isMigrated($sourceId)) {
                $actuallyDeleted = $migrator->remove($sourceId);
                $result = new RevertProcessedEvent((string) $migrator, $sourceId, $actuallyDeleted);
            } else {
                $result = new RevertSkippedEvent((string) $migrator, $sourceId, 'Destination record no longer found');
            }
        } catch (\Throwable $e) {
            $result = new RevertFailedEvent((string) $migrator, $sourceId, 'An unexpected error occurred', $e);
        }

        $this->getEventDispatcher()->dispatch(ShuttleEvents::REVERT_RESULT, $result);
        return $result;
    }

    /**
     * @param string|MigratorInterface $migrator
     * @return MigratorInterface
     */
    private function resolveMigrator(string $migrator): MigratorInterface
    {
        return ($migrator instanceof MigratorInterface)
            ? $migrator
            : $this->migratorCollection->get((string) $migrator);
    }
}
