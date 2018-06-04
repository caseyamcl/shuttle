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
use Shuttle\Migrator\Event\MigratePrePrepareEvent;
use Shuttle\Migrator\Event\MigrateResult;
use Shuttle\Migrator\Event\MigrateResultInterface;
use Shuttle\Migrator\Event\RevertFailedResult;
use Shuttle\Migrator\Event\RevertResult;
use Shuttle\Recorder\RecorderInterface;
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
     * @var MigratorCollection
     */
    private $migrators;

    /**
     * Constructor
     *
     * @param RecorderInterface        $recorder
     * @param EventDispatcherInterface $dispatcher
     * @param MigratorCollection       $migrators
     */
    public function __construct(
        RecorderInterface $recorder,
        EventDispatcherInterface $dispatcher,
        MigratorCollection $migrators = null
    ) {
        $this->recorder   = $recorder;
        $this->dispatcher = $dispatcher;
        $this->migrators  = $migrators ?: new MigratorCollection();
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @return MigratorCollection
     */
    public function getMigrators(): MigratorCollection
    {
        return $this->migrators;
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
     * @param string $type  Migrator Slug
     * @param int    $limit 0 means no limit
     * @param array  $ids   Optionally limit to specified IDs of source records
     * @return int Number of records attempted
     */
    public function migrate($type, $limit = 0, array $ids = []): int
    {
        $migrator = $this->getMigrators()->get($type);

        $iterator = ( ! empty($ids)) ? new \ArrayIterator($ids) : $migrator->listSourceIds();
        $count = 0;

        foreach ($iterator as $sourceRecId) {
            // Get out if we are limiting records
            if ($limit && $count > $limit) {
                break;
            }

            $this->dispatcher->dispatch(Events::MIGRATE, $this->doMigrate($migrator, $sourceRecId));
            $count++;
        }

        return $count;
    }

    /**
     * Revert migrated records
     *
     * @param string $type
     * @param int    $limit
     * @param array  $ids Optionally limit to specified IDs of source records (yes, SOURCE records)
     * @return int Number of records attempted
     */
    public function revert($type, $limit = 0, array $ids = []): int
    {
        $migrator = $this->getMigrators()->get($type);

        if (! empty($ids)) {
            $newIds = [];
            foreach ($ids as $sourceId) {
                $newIds[] = $this->recorder->findDestinationId($type, $sourceId);
            }
            $iterator = new \ArrayIterator($newIds);
        } else {
            $iterator = $this->recorder->listDestinationIds($type);
        }

        $count = 0;

        foreach ($iterator as $destRecId) {
            // Get out if we are limiting records
            if ($limit && $count > $limit) {
                break;
            }

            $this->dispatcher->dispatch(Events::REVERT, $this->doRevert($migrator, $destRecId));
            $count++;
        }

        return $count;
    }

    /**
     * Do Migration
     *
     * @param MigratorInterface $migrator
     * @param string            $sourceItemId
     * @return MigrateResultInterface
     */
    protected function doMigrate(MigratorInterface $migrator, $sourceItemId): MigrateResultInterface
    {
        // If already migrated, skip
        if ($this->recorder->isMigrated($migrator->getSlug(), $sourceItemId)) {
            return new MigrateResult(
                $migrator->getSlug(),
                $sourceItemId,
                MigrateResult::SKIPPED,
                $this->recorder->findDestinationId($migrator->getSlug(), $sourceItemId),
                sprintf("Item (type %s) with id %s is already migrated", $migrator->getSlug(), $sourceItemId)
            );
        }

        // Get the new record ID
        try {
            $sourceItem = $migrator->getItemFromSource($sourceItemId);
            $this->getDispatcher()->dispatch(
                Events::PRE_PREPARE,
                new MigratePrePrepareEvent($migrator->getSlug(), $sourceItemId, $sourceItem)
            );


            $destinationItem = $migrator->prepareSourceItem($sourceItem);
            $this->getDispatcher()->dispatch(
                Events::PRE_PERSIST,
                new MigratePrePrepareEvent($migrator->getSlug(), $sourceItemId, $destinationItem)
            );

            $destinationItemId = $migrator->persistDestinationItem($destinationItem);
            $this->recorder->markMigrated($migrator->getSlug(), $sourceItemId, $destinationItemId);

            return new MigrateResult(
                $migrator->getSlug(),
                $sourceItemId,
                MigrateResult::PROCESSED,
                $destinationItemId,
                sprintf(
                    "Migrated (type %s) with id %s to destination with ID: %s",
                    $migrator->getSlug(),
                    $sourceItemId,
                    $destinationItemId
                )
            );
        } catch (\RuntimeException $e) {
            return new MigrateFailedResult($sourceItemId, $e->getMessage(), $e);
        }
    }

    /**
     * @param MigratorInterface $migrator
     * @param string            $destinationId
     * @return RevertResult
     */
    protected function doRevert(MigratorInterface $migrator, string $destinationId): MigrateResultInterface
    {
        try {

            $isDeleted  = $migrator->revert($destinationId);
            $sourceRecId = $this->recorder->findSourceId($migrator->getSlug(), $destinationId);

            $this->recorder->removeMigratedMark($migrator->getSlug(), $destinationId);

            return new RevertResult(
                $migrator->getSlug(),
                $sourceRecId,
                $isDeleted ? RevertResult::PROCESSED : RevertResult::SKIPPED,
                $destinationId,
                sprintf(
                    "%s (type %s) with destination ID %s (source id: %s)",
                    ($isDeleted ? 'reverted' : 'skipped'),
                    $migrator->getSlug(),
                    $destinationId,
                    $sourceRecId
                )
            );
        } catch (\RuntimeException $e) {
            return new RevertFailedResult($destinationId, $e->getMessage(), $e);
        }
    }
}
