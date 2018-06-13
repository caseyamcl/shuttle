<?php

namespace Shuttle\Helper;

use Shuttle\Event\ActionResultInterface;
use Shuttle\ShuttleAction;
use Shuttle\ShuttleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Helper class to track migrations
 *
 * @package Shuttle\Migrator
 */
class Tracker implements EventSubscriberInterface
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
    private $trackAction;

    /**
     * @param string $trackAction
     * @param EventDispatcherInterface $dispatcher
     * @return Tracker
     */
    public static function createAndAttach(string $trackAction, EventDispatcherInterface $dispatcher): Tracker
    {
        $that = new static($trackAction);
        $dispatcher->addSubscriber($that);
        return $that;
    }

    /**
     * MigrateTracker constructor.
     * @param string $trackAction
     */
    public function __construct(string $trackAction)
    {
        $this->trackAction = ShuttleAction::ensureValidAction($trackAction);
        $this->initTracking(self::ALL);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ShuttleEvents::REVERT_RESULT  => 'track',
            ShuttleEvents::MIGRATE_RESULT => 'track'
        ];
    }

    /**
     * @param ActionResultInterface $action
     */
    public function track(ActionResultInterface $action)
    {
        if ($action->getAction() !== $this->trackAction) {
            return;
        }

        $this->initTracking($action->getMigratorName());

        $this->tracking[$action->getMigratorName()][self::TOTAL]++;
        $this->tracking[$action->getMigratorName()][$action->getStatus()]++;

        $this->tracking[self::ALL][self::TOTAL]++;
        $this->tracking[self::ALL][$action->getStatus()]++;
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
        return $this->tracking[$migratorName][ActionResultInterface::PROCESSED];
    }

    /**
     * @param string $migratorName
     * @return int
     */
    public function getSkippedCount(string $migratorName = self::ALL): int
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName][ActionResultInterface::SKIPPED];
    }

    /**
     * @param string $migratorName
     * @return int
     */
    public function getFailedCount(string $migratorName = self::ALL): int
    {
        $this->initTracking($migratorName);
        return $this->tracking[$migratorName][ActionResultInterface::FAILED];
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
        if (! array_key_exists($migratorName, $this->tracking)) {
            $this->tracking[$migratorName] = [
                self::TOTAL                      => 0,
                ActionResultInterface::FAILED    => 0,
                ActionResultInterface::PROCESSED => 0,
                ActionResultInterface::SKIPPED   => 0
            ];
        }
    }
}
