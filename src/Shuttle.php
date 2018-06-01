<?php
/**
 * shuttle
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

namespace Shuttle;

use Shuttle\Migrator\MigrateService;
use Shuttle\Migrator\MigratorCollection;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\Recorder\Recorder;
use Shuttle\Recorder\RecorderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Shuttle Class provides convenience wrapper for common Shuttle Services
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Shuttle
{
    /**
     * @var Recorder
     */
    private $recorder;

    /**
     * @var MigrateService
     */
    private $migrateService;

    /**
     * Constructor
     *
     * @param string $appSlug A unique alphanum-dash name for this instance of Shuttle
     * @param RecorderInterface $recorder Optionally specify a custom recorder/tracker
     * @param EventDispatcherInterface $dispatcher Optionally inject your own Event Dispatcher instead of
     *                                             constructing a new one
     */
    public function __construct(
        string $appSlug,
        RecorderInterface $recorder = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->recorder = $recorder;
        $this->migrateService = new MigrateService($this->recorder, $dispatcher ?: new EventDispatcher());
    }

    /**
     * Revert items of type
     *
     * @param string $type  Type slug corresponds to migrator slug
     * @param int    $limit Leave blank for no limit
     * @param array  $ids   Optionally specify a limited number of IDs to migrate
     * @return int  The number of items processed
     */
    public function revert(string $type, ?int $limit = null, array $ids = []): int
    {
        return $this->migrateService->revert($type, $limit, $ids);
    }

    /**
     * Migrate items of type
     *
     * @param string $type  Type slug corresponds to migrator slug
     * @param int    $limit Leave blank for no limit
     * @param array  $ids   Optionally specify a limited number of IDs to migrate
     * @return int
     */
    public function migrate(string $type, ?int $limit = null, array $ids = []): int
    {
        return $this->migrateService->migrate($type, $limit, $ids);
    }

    /**
     * @param MigratorInterface $migrator
     */
    public function addMigrator(MigratorInterface $migrator): void
    {
        $this->migrateService->getMigrators()->add($migrator);
    }

    /**
     * @return RecorderInterface
     */
    public function getRecorder(): RecorderInterface
    {
        return $this->recorder;
    }

    /**
     * @return MigrateService
     */
    public function getMigrateService(): MigrateService
    {
        return $this->migrateService;
    }

    /**
     * @return MigratorCollection|MigratorInterface[]
     */
    public function getMigrators(): MigratorCollection
    {
        return $this->migrateService->getMigrators();
    }
}
