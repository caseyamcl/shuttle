<?php
/**
 * Shuttle
 *
 * @license ${LICENSE_LINK}
 * @link ${PROJECT_URL_LINK}
 * @version ${VERSION}
 * @package ${PACKAGE_NAME}
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

namespace Shuttle\Service\Migrator;

use Shuttle\Service\Migrator\Event\MigrateFailedResult;
use Shuttle\Service\Migrator\Event\MigrateResult;
use Shuttle\Service\Migrator\Event\RevertFailedResult;
use Shuttle\Service\Migrator\Event\RevertResult;
use Shuttle\Service\Recorder\RecorderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param RecorderInterface        $recorder
     * @param EventDispatcherInterface $dispatcher
     * @param MigratorCollection       $migrators
     */
    public function __construct(RecorderInterface $recorder, EventDispatcherInterface $dispatcher, MigratorCollection $migrators = null)
    {
        $this->recorder   = $recorder;
        $this->dispatcher = $dispatcher;
        $this->migrators  = $migrators ?: new MigratorCollection();
    }

    // ---------------------------------------------------------------

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    // ---------------------------------------------------------------

    /**
     * @return MigratorCollection
     */
    public function getMigrators()
    {
        return $this->migrators;
    }

    // ---------------------------------------------------------------

    /**
     * @return RecorderInterface
     */
    public function getRecorder()
    {
        return $this->recorder;
    }

    // ---------------------------------------------------------------

    /**
     * Migrate records
     *
     * @param string $type  Migrator Slug
     * @param int    $limit 0 means no limit
     * @param array  $ids   Optionally limit to specified IDs of source records
     * @return int Number of records attempted
     */
    public function migrate($type, $limit = 0, array $ids = [])
    {
        $migrator = $this->getMigrators()->get($type);

        $iterator = ( ! empty($ids))
            ? new \ArrayIterator($ids)
            : $migrator->getSource()->listRecordIds();

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

    // ---------------------------------------------------------------

    /**
     * Revert migrated records
     *
     * @param string $type
     * @param int    $limit
     * @param array  $ids Optionally limit to specified IDs of source records (yes, SOURCE records)
     * @return int Number of records attempted
     */
    public function revert($type, $limit = 0, array $ids = [])
    {
        $migrator = $this->getMigrators()->get($type);

        if ( ! empty($ids)) {
            $newIds = [];
            foreach ($ids as $sourceId) {
                $newIds[] = $this->recorder->getNewId($type, $sourceId);
            }
            $iterator = new \ArrayIterator($newIds);
        }
        else {
            $iterator = $this->recorder->getNewIds($type);
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

    // ---------------------------------------------------------------

    /**
     * Do Migration
     *
     * @param MigratorInterface $migrator
     * @param string            $sourceRecId
     * @return MigrateResult
     */
    protected function doMigrate(MigratorInterface $migrator, $sourceRecId)
    {
        // If already migrated, skip
        if ($this->recorder->isMigrated($migrator->getSlug(), $sourceRecId)) {
            return new MigrateResult(
                $migrator->getSlug(),
                $sourceRecId,
                $this->recorder->getNewId($migrator->getSlug(), $sourceRecId),
                MigrateResult::SKIPPED,
                sprintf("Record (type %s) with id %s is already migrated", $migrator->getSlug(), $sourceRecId)
            );
        }

        // Get the new record ID
        try {
            $destRecId = $migrator->migrate($sourceRecId);
            $this->recorder->markMigrated($migrator->getSlug(), $sourceRecId, $destRecId);

            return new MigrateResult(
                $migrator->getSlug(),
                $sourceRecId,
                MigrateResult::PROCESSED,
                $destRecId,
                sprintf("Migrated (type %s) with id %s to destination record: %s", $migrator->getSlug(), $sourceRecId, $destRecId)
            );
        }
        catch (\RuntimeException $e) {
            return new MigrateFailedResult($sourceRecId, $e->getMessage(), $e);
        }
    }

    // ---------------------------------------------------------------

    /**
     * @param MigratorInterface $migrator
     * @param string            $destRecId
     * @return RevertResult
     */
    protected function doRevert(MigratorInterface $migrator, $destRecId)
    {
        try {
            $isDeleted   = $migrator->getDestination()->deleteRecord($destRecId);
            $sourceRecId = $this->recorder->getOldId($migrator->getSlug(), $destRecId);

            $this->recorder->removeMigratedMark($migrator->getSlug(), $destRecId);

            return new RevertResult(
                $migrator->getSlug(),
                $sourceRecId,
                $isDeleted ? RevertResult::PROCESSED : RevertResult::SKIPPED,
                $destRecId,
                sprintf(
                    "%s (type %s) with destination id %s (source id: %s)",
                    ($isDeleted ? 'reverted' : 'skipped'),
                    $migrator->getSlug(),
                    $destRecId,
                    $sourceRecId
                )
            );
        }
        catch (\RuntimeException $e) {
            return new RevertFailedResult($destRecId, $e->getMessage(), $e);
        }
    }
}
