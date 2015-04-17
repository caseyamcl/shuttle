<?php
/**
 * shuttle
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

namespace Shuttle;

use Shuttle\Service\Migrator\MigrateService;
use Shuttle\Service\Migrator\MigratorInterface;
use Shuttle\Service\Recorder\DefaultDatabaseBuilder;
use Shuttle\Service\Recorder\Recorder;
use Shuttle\Service\Recorder\RecorderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shuttle\Command as ConsoleCmd;

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

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string                   $appSlug     A unique alphanum-dash name for this instance of Shuttle
     * @param RecorderInterface        $recorder    Optionally specify a custom recorder/tracker
     * @param EventDispatcherInterface $dispatcher  Optionally inject your own Event Dispatcher instead of constructing a new one
     */
    public function __construct($appSlug, RecorderInterface $recorder = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->recorder = $recorder ?: new Recorder(DefaultDatabaseBuilder::buildDefaultDatabaseConnection($appSlug));
        $this->migrateService = new MigrateService($this->recorder, $dispatcher ?: new EventDispatcher());
    }

    // ---------------------------------------------------------------

    /**
     * Revert records of type
     *
     * @param string $type  Type slug corresponds to migrator slug
     * @param int    $limit Leave blank for no limit
     * @param array  $ids   Optionally specify a limited number of IDs to migrate
     * @return int
     */
    public function revert($type, $limit = null, array $ids = [])
    {
        return $this->migrateService->revert($type, $limit, $ids);
    }

    // ---------------------------------------------------------------

    /**
     * Revert records of type
     *
     * @param string $type  Type slug corresponds to migrator slug
     * @param int    $limit Leave blank for no limit
     * @param array  $ids   Optionally specify a limited number of IDs to migrate
     * @return int
     */
    public function migrate($type, $limit = null, array $ids = [])
    {
        return $this->migrateService->migrate($type, $limit, $ids);
    }

    // ---------------------------------------------------------------

    /**
     * @param MigratorInterface $migrator
     */
    public function addMigrator(MigratorInterface $migrator)
    {
        $this->migrateService->getMigrators()->add($migrator);
    }

    // ---------------------------------------------------------------

    /**
     * @return Recorder
     */
    public function getRecorder()
    {
        return $this->recorder;
    }

    // ---------------------------------------------------------------

    /**
     * @return MigrateService
     */
    public function getMigrateService()
    {
        return $this->migrateService;
    }

    // ---------------------------------------------------------------

    /**
     * @return Service\Migrator\MigratorCollection|MigratorInterface[]
     */
    public function getMigrators()
    {
        return $this->migrateService->getMigrators();
    }

    // ---------------------------------------------------------------

    /**
     * Get Console Commands
     *
     * @param bool $onePerMigrator
     * @param bool $includeListCommand
     * @return array|Command[] Commands
     */
    public function getConsoleCommands($onePerMigrator = true, $includeListCommand = false)
    {
        $cmds = [];

        if ($onePerMigrator == true) {
            foreach ($this->getMigrators() as $mig) {
                $cmds[] = new ConsoleCmd\Migrate($this->getMigrateService(), $mig->getSlug());
                $cmds[] = new ConsoleCmd\Revert($this->getMigrateService(), $mig->getSlug());
            }
        }
        else {
            $cmds[] = new ConsoleCmd\Migrate($this->getMigrateService());
            $cmds[] = new ConsoleCmd\Revert($this->getMigrateService());
        }

        if ($includeListCommand) {
            $cmds[] = new ConsoleCmd\MigratorsList($this->getMigrators(), $this->getRecorder());
        }

        return $cmds;
    }
}
