<?php
/**
 * Shuttle
 *
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/phpoaipmh
 * @package caseyamcl/shuttle
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

namespace Shuttle\Migrator;

use Shuttle\Migrator\Event\MigrateFailedResult;
use Shuttle\Migrator\Event\MigratePrePersistEvent;
use Shuttle\Migrator\Event\MigratePrePrepareEvent;
use Shuttle\Migrator\Event\MigrateResult;
use Shuttle\Migrator\Event\MigrateResultInterface;
use Shuttle\Migrator\Event\RevertFailedResult;
use Shuttle\Migrator\Event\RevertResult;
use Shuttle\Recorder\RecorderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Migrate Service
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigrateService
{
    /**
     * @var RecorderInterface
     */
    private $recorder;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor
     *
     * @param RecorderInterface        $recorder
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(RecorderInterface $recorder, EventDispatcherInterface $dispatcher = null)
    {
        $this->recorder = $recorder;
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @return RecorderInterface
     */
    public function getRecorder(): RecorderInterface
    {
        return $this->recorder;
    }

    /**
     * Migrate records
     *
     * @param MigratorInterface $migrator
     * @param array $sourceIds Optionally limit to specified IDs of source records
     * @param bool $clobber  Revert the item and re-migrate it if it is already migrated
     * @return \Generator|MigrateResultInterface[]
     */
    public function migrateItems(MigratorInterface $migrator, array $sourceIds = [], bool $clobber = false): \Generator
    {
        $iterator = ( ! empty($ids)) ? new \ArrayIterator($sourceIds) : $migrator->getSourceIdIterator();

        foreach ($iterator as $sourceId) {
            yield $this->migrate($migrator, $sourceId, $clobber);
        }
    }

    /**
     * Revert migrated records
     *
     * @param MigratorInterface $migrator
     * @param array $sourceIds Optionally limit to specified IDs of source records (yes, SOURCE records)
     * @return \Generator|MigrateResultInterface[]
     */
    public function revertItems(MigratorInterface $migrator, array $sourceIds = []): \Generator
    {
        $iterator = ( ! empty($sourceIds))
            ? new \ArrayIterator($sourceIds)
            : $this->getRecorder()->listMigratedSourceIds($migrator->getName());

        foreach ($iterator as $sourceId) {
            yield $this->revert($migrator, $sourceId);
        }
    }

    /**
     * Migrate a record
     *
     * @param MigratorInterface $migrator
     * @param string $sourceItemId
     * @param bool $clobber
     * @return MigrateResultInterface
     */
    public function migrate(
        MigratorInterface $migrator,
        string $sourceItemId,
        bool $clobber = false
    ): MigrateResultInterface {

        // Clobber?
        if ($clobber && $this->recorder->isMigrated($migrator, $sourceItemId)) {
            $revertResult = $this->revert($migrator, $sourceItemId);

            if ($revertResult->getStatus() == MigrateResultInterface::FAILED) {
                $result = new MigrateFailedResult(
                    $migrator->getName(),
                    $sourceItemId,
                    sprintf(
                        'Item ($type %s) with ID %s failed, because revert failed with message: (%s)',
                        $migrator->getName(),
                        $sourceItemId,
                        $revertResult->getMessage()
                    )
                );
            }
        }

        if (! isset($result)) {
            $result = $this->doMigrate($migrator, $sourceItemId);
        }

        $this->dispatcher->dispatch(Events::MIGRATE, $result);
        $this->dispatcher->dispatch(Events::REVERT_OR_MIGRATE, $result);

        return $result;
    }

    /**
     * Revert a record
     *
     * @param MigratorInterface $migrator
     * @param string            $sourceItemId (yes, SOURCE record ID)
     * @return RevertResult
     */
    public function revert(MigratorInterface $migrator, string $sourceItemId): MigrateResultInterface
    {
        if (! $destinationId = $this->recorder->findDestinationId($migrator->getName(), $sourceItemId)) {
            $result = new RevertResult(
                $migrator->getName(),
                $sourceItemId,
                RevertResult::SKIPPED,
                '',
                sprintf("Item (type %s) with source ID %s was not migrated", $migrator->getName(), $sourceItemId)
            );
        } else {
            try {
                $isDeleted = $migrator->removeDestinationItem($destinationId);
                $this->recorder->removeMigratedMark($migrator->getName(), $destinationId);

                $result = new RevertResult(
                    $migrator->getName(),
                    $sourceItemId,
                    $isDeleted ? RevertResult::PROCESSED : RevertResult::SKIPPED,
                    $destinationId,
                    sprintf(
                        "%s (type %s) with destination ID %s (source id: %s)",
                        ($isDeleted ? 'Reverted' : 'Skipped'),
                        $migrator->getName(),
                        $destinationId,
                        $sourceItemId
                    )
                );
            } catch (\RuntimeException $e) {
                $result = new RevertFailedResult($migrator->getName(), $sourceItemId, $e->getMessage(), $e);
            }
        }

        $this->dispatcher->dispatch(Events::REVERT_OR_MIGRATE, $result);
        $this->dispatcher->dispatch(Events::REVERT, $result);
        return $result;
    }

    /**
     * Perform the migration
     *
     * @param MigratorInterface $migrator
     * @param string $sourceItemId
     * @return MigrateFailedResult|MigrateResult
     */
    private function doMigrate(MigratorInterface $migrator, string $sourceItemId)
    {
        // If no result yet (not skipped)
        if ($this->recorder->isMigrated($migrator->getName(), $sourceItemId)) {
            return new MigrateResult(
                $migrator->getName(),
                $sourceItemId,
                MigrateResult::SKIPPED,
                $this->recorder->findDestinationId($migrator->getName(), $sourceItemId),
                sprintf("Item (type %s) with ID %s is already migrated", $migrator->getName(), $sourceItemId)
            );
        } else {
            try {
                $sourceItem = $migrator->getItemFromSource($sourceItemId);
                $this->getDispatcher()->dispatch(
                    Events::PRE_PREPARE,
                    new MigratePrePrepareEvent($migrator->getName(), $sourceItemId, $sourceItem)
                );

                $destinationItem = $migrator->prepareSourceItem($sourceItem);
                $this->getDispatcher()->dispatch(
                    Events::PRE_PERSIST,
                    new MigratePrePersistEvent($migrator->getName(), $sourceItemId, $destinationItem)
                );

                $destinationItemId = $migrator->persistDestinationItem($destinationItem);
                $this->recorder->markMigrated($migrator->getName(), $sourceItemId, $destinationItemId);

                return new MigrateResult(
                    $migrator->getName(),
                    $sourceItemId,
                    MigrateResult::PROCESSED,
                    $destinationItemId,
                    sprintf(
                        "Migrated (type %s) with ID %s to destination with ID: %s",
                        $migrator->getName(),
                        $sourceItemId,
                        $destinationItemId
                    )
                );
            } catch (\RuntimeException $e) {
                return new MigrateFailedResult($migrator->getName(), $sourceItemId, $e->getMessage(), $e);
            }
        }
    }
}
