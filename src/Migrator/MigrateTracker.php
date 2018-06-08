<?php

namespace Shuttle\Migrator;

use Shuttle\Migrator\Event\MigrateResultInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Helper class to track migrations
 *
 * @package Shuttle\Migrator
 */
class MigrateTracker implements EventSubscriberInterface
{
    public const ALL = 'all';
    private const TOTAL = 'total';

    /**
     * @var array|array[]
     */
    private $tracking = [];

    /**
     * @var string
     */
    private $trackEvent;

    /**
     * MigrateTracker constructor.
     * @param string $trackEvent  The migrate event to track
     */
    public function __construct(string $trackEvent = Events::REVERT_OR_MIGRATE)
    {
        $this->trackEvent = $trackEvent;
        $this->reset();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [Events::REVERT => 'trackRevert', Events::MIGRATE => 'trackMigrate'];
    }

    /**
     * @param MigrateResultInterface $migrateResult
     */
    public function trackMigrate(MigrateResultInterface $migrateResult)
    {
        if ($this->trackEvent == Events::MIGRATE or $this->trackEvent == Events::REVERT_OR_MIGRATE) {
            $this->doTrack($migrateResult);
        }
    }

    /**
     * @param MigrateResultInterface $revertResult
     */
    public function trackRevert(MigrateResultInterface $revertResult)
    {
        if ($this->trackEvent == Events::REVERT or $this->trackEvent == Events::REVERT_OR_MIGRATE) {
            $this->doTrack($revertResult);
        }
    }

    /**
     * @param MigrateResultInterface $action
     */
    protected function doTrack(MigrateResultInterface $action)
    {
        $this->initTracking($action->getMigratorName());
        $this->tracking[$action->getMigratorName()][self::TOTAL]++;
        $this->tracking[$action->getMigratorName()][$action->getStatus()]++;
    }

    /**
     * Reset tracking
     */
    public function reset()
    {
        $this->tracking = [];
        $this->initTracking(self::ALL);
    }

    /**
     * @return array
     */
    public function getTotals(): array
    {
        return $this->tracking[self::ALL];
    }

    /**
     * Get the total number of items attempted
     *
     * @param string $migratorName
     * @return int
     */
    public function getTotalCount(string $migratorName = self::ALL): int
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName][self::TOTAL];
    }

    /**
     * @param string $migratorName
     * @return int
     */
    public function getProcessedCount(string $migratorName = self::ALL): int
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName][MigrateResultInterface::PROCESSED];
    }

    /**
     * @param string $migratorName
     * @return int
     */
    public function getSkippedCount(string $migratorName = self::ALL): int
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName][MigrateResultInterface::SKIPPED];
    }

    /**
     * @param string $migratorName
     * @return int
     */
    public function getFailedCount(string $migratorName = self::ALL): int
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName][MigrateResultInterface::FAILED];
    }

    /**
     * @param string|null $migratorName
     * @return array
     */
    public function getReportForType(string $migratorName = null): array
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName];
    }

    /**
     * @return array|array[]
     */
    public function getAllReports(): array
    {
        return $this->tracking;
    }

    /**
     * Ensure that tracking exists for the given type
     * @param string $migratorName
     */
    final private function initTracking(string $migratorName): void
    {
        if (array_key_exists($migratorName, $this->tracking)) {
            $this->tracking[$migratorName] = [
                self::TOTAL                       => 0,
                MigrateResultInterface::FAILED    => 0,
                MigrateResultInterface::PROCESSED => 0,
                MigrateResultInterface::SKIPPED   => 0
            ];
        }
    }
}
