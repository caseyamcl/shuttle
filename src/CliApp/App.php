<?php
/**
 * ticketmove
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

namespace ConveyorBelt\CliApp;

use Cilex\Application as CilexApp;
use Cilex\Provider\ConfigServiceProvider;
use Cilex\Provider\DoctrineServiceProvider;
use Cilex\Provider\MonologServiceProvider;
use ConveyorBelt\Provider\MigratorProvider;
use ConveyorBelt\Service\Migrator\MigratorInterface;
use ConveyorBelt\Service\Recorder\Recorder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TaskTracker\Subscriber\Psr3Logger;
use TaskTracker\TrackerFactory;

/**
 * Class App
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class App
{
    const NAME    = 'Conveyor Belt - Data Migration Utility';
    const VERSION = '1.0';

    // ---------------------------------------------------------------

    const AUTO = null;

    // ---------------------------------------------------------------

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var CilexApp
     */
    private $cilex;

    // ---------------------------------------------------------------

    /**
     * Main Execution Program
     *
     * @param string $basePath
     */
    public static function main($basePath = self::AUTO)
    {
        $that = new static($basePath);
        $that->run();
    }

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $basePath
     */
    public function __construct($basePath = self::AUTO)
    {
        // Load BasePath
        $this->basePath = realpath($basePath ?: __DIR__ . '/../../');

        // Load Cilex
        $this->cilex = new CilexApp(self::NAME, self::VERSION);
    }

    // ---------------------------------------------------------------

    /**
     * Run the application
     */
    public function run()
    {
        $this->loadLibraries();
        $this->loadCommands();
        $this->checkDbs();

        $this->cilex->run();
    }

    // ---------------------------------------------------------------

    protected function loadCommands()
    {
        $app =& $this->cilex;

        // List migrators command
        $app->command(new Command\MigratorsList($app['migrator.migrators'], $app['recorder']));

        // Basic Migrate/Convert Commands
        $app->command(new Command\Migrate($app['tracker.factory'], $app['migrator.service']));
        $app->command(new Command\Revert($app['tracker.factory'], $app['migrator.service']));

        // Migrate/Convert Commands for registered migrators
        /** @var MigratorInterface $migrator */
        foreach ($app['migrator.migrators'] as $migrator) {
            $app->command(new Command\Migrate($app['tracker.factory'], $app['migrator.service'], $migrator->getSlug()));
            $app->command(new Command\Revert($app['tracker.factory'],  $app['migrator.service'], $migrator->getSlug()));
        }
    }

    // ---------------------------------------------------------------

    protected function loadLibraries()
    {
        $app =& $this->cilex;

        // 1st - Load Config
        $app->register(new ConfigServiceProvider(), [
            'config.path' => $this->basePath . '/config/config.yml'
        ]);

        // $app['dispatcher']
        $app['dispatcher'] = new EventDispatcher();

        // $app['db']
        $app->register(new DoctrineServiceProvider(), [
            'db.options' => ['url' => $app['config']['statusdb']['connstring']]
        ]);

        // $app['recorder']
        $app['recorder'] = $app->share(function(CilexApp $app) {
            return new Recorder($app['db'], $app['config']['statusdb']['table']);
        });

        // $app['migrator.migrators'], $app['migrator.service']
        $app->register(new MigratorProvider(), [
            'migrator.dispatcher' => $app['dispatcher'],
            'migrator.paths'      => $app['config']['migrators']
        ]);

        // $app['monolog']
        if (isset($app['config']['log'])) {
            $app->register(new MonologServiceProvider(), [
                'monolog.logfile' => $app['config']['log'],
                'monolog.name'    => 'conveyorbelt'
            ]);
        }

        // $app['tracker.factory'] - Setup Tracker factory
        $app['tracker.factory'] = $app->share(function(CilexApp $app) {
            return new TrackerFactory([new Psr3Logger($app['monolog'])]);
        });
    }

    // ---------------------------------------------------------------

    private function checkDbs()
    {
        /** @var Connection $dbConn */
        foreach ($this->cilex['dbs'] as $dbName => $dbConn) {
            try {
                $dbConn->connect();
            }
            catch (ConnectionException $e) {
                throw new \RuntimeException("Connection failure for: " . $dbName, 0, $e);
            }
        }
    }

}
