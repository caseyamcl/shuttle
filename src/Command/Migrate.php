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

namespace Shuttle\Command;

use Shuttle\Service\Migrator\Event\MigrateResultInterface;
use Shuttle\Service\Migrator\Events;
use Shuttle\Service\Migrator\MigrateService;
use Shuttle\Service\Migrator\MigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TaskTracker\Subscriber\SymfonyConsoleLog;
use TaskTracker\Subscriber\SymfonyConsoleProgress;
use TaskTracker\TrackerFactory;

/**
 * Class Migrate
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Migrate extends Command
{
    /**
     * @var MigratorInterface
     */
    protected $migrator;

    /**
     * @var MigrateService
     */
    protected $migrateService;

    /**
     * @var TrackerFactory
     */
    protected $trackerFactory;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param MigrateService $migrateService  Migrate service
     * @param string         $migratorName    Optionally provide migrator name (if not provided, a CLI argument is available)
     * @param TrackerFactory $trackerFactory  Optionally provide a Task Tracker Factory
     */
    public function __construct(MigrateService $migrateService, $migratorName = '', TrackerFactory $trackerFactory = null)
    {
        $this->migrateService = $migrateService;
        $this->setTrackerFactory($trackerFactory ?: new TrackerFactory());

        if ($migratorName) {
            $this->migrator = $migrateService->getMigrators()->get($migratorName);
        }

        parent::__construct();
    }

    // ---------------------------------------------------------------

    /**
     * @return TrackerFactory
     */
    public function getTrackerFactory()
    {
        return $this->trackerFactory;
    }

    // ---------------------------------------------------------------

    /**
     * @param TrackerFactory $trackerFactory
     */
    public function setTrackerFactory(TrackerFactory $trackerFactory)
    {
        $this->trackerFactory = $trackerFactory;
    }

    // ---------------------------------------------------------------

    protected function configure()
    {
        if ($this->migrator) {
            $this->setName($this->getActionName() . ':' . $this->migrator->getSlug());
            $this->setDescription(ucfirst($this->getActionName()) . " " . $this->migrator->getSlug());
        }
        else {
            $this->addArgument('migrator', InputArgument::REQUIRED, 'The name (slug) of the migrator to ' . $this->getActionName());
        }

        $this->addOption('ids',   'i', InputOption::VALUE_REQUIRED, 'Optionally indicate a comma-separated list of IDs from source to ' . $this->getActionName());
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Optionally set a limit');
    }

    // ---------------------------------------------------------------

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup the migrator
        $migrator = $this->migrator ?: $this->migrateService->getMigrators()->get($input->getArgument('migrator'));

        // Read limit parameter
        $limit  = $input->getOption('limit') ?: 0;

        // Read ID list parameter
        if ($input->getOption('ids')) {
            $idList   = array_filter(array_map('trim', explode(',', $input->getOption('ids'))));
            $numItems = count($idList);
        }
        else {
            $idList   = [];
            $numItems = $this->getRecCount($migrator);
        }

        // Setup console output listener and tracker object
        $handler = ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE)
            ? new SymfonyConsoleLog($output)
            : new SymfonyConsoleProgress($output);
        $tracker = $this->trackerFactory->buildTracker($numItems, [$handler]);

        // Register callback for Tracker to report on progress
        $this->migrateService->getDispatcher()->addListener($this->getEventListenName(), function(MigrateResultInterface $event) use ($tracker) {
            $tracker->tick($event->getStatus(), $event->getMessage(), ['result_obj' => $event]);
        });

        // Do It!
        call_user_func([$this->migrateService, $this->getActionName()], $migrator->getSlug(), $limit, $idList);

        // Finish tracker
        $report = $tracker->finish();

        // Final report
        $output->writeln(sprintf(
            "\n%s Finished for %s: <info>%s</info> processed (<fg=yellow>%s</fg=yellow> skipped / <fg=red>%s</fg=red> failed)",
            ucfirst($this->getActionName()),
            $migrator->getSlug(),
            number_format($report->getNumItemsSuccess()),
            number_format($report->getNumItemsSkip()),
            number_format($report->getNumItemsFail())
        ));
    }

    // ---------------------------------------------------------------

    /**
     * @param MigratorInterface $migrator
     * @return int
     */
    protected function getRecCount(MigratorInterface $migrator)
    {
        return $migrator->getSource()->count();
    }

    // ---------------------------------------------------------------

    /**
     * @return string
     */
    protected function getActionName()
    {
        return "migrate";
    }

    // ---------------------------------------------------------------

    protected function getEventListenName()
    {
        return Events::MIGRATE;
    }
}
