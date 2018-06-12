<?php

namespace Shuttle;

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
     * @param callable|null $continue  A callable that returns TRUE for continue and FALSE to stop
     */
    public function migrate(string $migratorName, ?iterable $sourceIds = null, callable $continue = null)
    {
        $migrator = $this->getMigrators()->get($migratorName);
        $continue = $continue ?: function() { return true; };

        // Build an iterator
        if ($sourceIds) {
            $iterator = function() use ($migrator, $sourceIds) {
                foreach ($sourceIds as $sourceId) {
                    yield $migrator->getSourceItem($sourceId);
                }
            };
        } else {
            $iterator = $migrator->getSourceIterator();
        }

        /** @var SourceItem $sourceItem */
        foreach ($iterator as $sourceItem) {

            if (! $continue()) {
                return;
            }

            $prepared = $migrator->prepare($sourceItem);
            $destinationId = $migrator->persist($prepared);
            $migrator->recordMigrate($sourceItem, $destinationId);
        }
    }

    /**
     * Revert some or all items
     *
     * @param string $migratorName
     * @param iterable|string[] $sourceIds Source IDs to migrate, or null for all
     * @param callable|null $continue  A callable that returns TRUE for continue and FALSE to stop
     */
    public function revert(string $migratorName, ?iterable $sourceIds = null, callable $continue = null)
    {
        $migrator = $this->getMigrators()->get($migratorName);
        $continue = $continue ?: function() { return true; };

        $iterator = $sourceIds ?: function () use ($migrator) {
            foreach ($migrator->getReport() as $record) {
                yield $record->getSourceId();
            }
        };

        /** @var string[] $sourceId */
        foreach ($iterator as $sourceId) {

            if (! $continue()) {
                return;
            }

            $migrator->remove($sourceId);
        }
    }
}