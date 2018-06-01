<?php
/**
 * Shuttle Library
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

namespace Shuttle\ConsoleCommand;

use Shuttle\Migrator\Event\MigrateResultInterface;
use Shuttle\Migrator\Events;
use Shuttle\Migrator\MigrateService;
use Shuttle\Migrator\MigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Migrate extends Command
{
    const ACTION_NAME = 'migrate';

    /**
     * @var MigratorInterface
     */
    protected $migrator;

    /**
     * @var MigrateService
     */
    protected $migrateService;

    /**
     * Constructor
     *
     * @param MigrateService $migrateService  Migrate service
     * @param string         $migratorName    Optionally provide migrator name
     *                                        (if not provided, a CLI argument is made available to the user)
     */
    public function __construct(MigrateService $migrateService, $migratorName = '')
    {
        $this->migrateService = $migrateService;

        if ($migratorName) {
            $this->migrator = $migrateService->getMigrators()->get($migratorName);
        }

        parent::__construct();
    }

    protected function configure()
    {
        if ($this->migrator) {
            $this->setName(static::ACTION_NAME . ':' . $this->migrator->getSlug());
            $this->setDescription(ucfirst(static::ACTION_NAME) . " " . $this->migrator->getSlug());
        } else {
            $this->addArgument(
                'migrator',
                InputArgument::REQUIRED,
                'The name (slug) of the migrator to ' . static::ACTION_NAME
            );
        }

        $this->addOption(
            'ids',
            'i',
            InputOption::VALUE_REQUIRED,
            'Optionally indicate a comma-separated list of IDs from source to ' . static::ACTION_NAME
        );

        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_REQUIRED,
            'Optionally set a limit'
        );
    }

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
        } else {
            $idList   = [];
            $numItems = $this->getRecCount($migrator);
        }

        // Output some info
        $output->writeln(sprintf(
            'Processing <info>%s</info> items from <info>%s</info> (%s)',
            number_format($numItems),
            $migrator->getSlug(),
            $migrator->getDescription()
        ));

        // Register callback for Tracker to report on progress
        $processLog = [
            'total'                           => 0,
            MigrateResultInterface::FAILED    => 0,
            MigrateResultInterface::PROCESSED => 0,
            MigrateResultInterface::SKIPPED   => 0
        ];

        // Add listener to output a message for each record processed
        $this->migrateService->getDispatcher()->addListener(
            $this->getEventListenName(),
            function (MigrateResultInterface $event) use ($output, $numItems, &$processLog) {

                $processLog['total']++;
                $processLog[$event->getStatus()]++;

                $map = [$event::FAILED = 'FAIL', $event::PROCESSED = 'SAVE', $event::SKIPPED = 'SKIP'];
                $output->writeln(sprintf(
                    ' * [%d/%d] %s: %s',
                    $processLog['total'],
                    $numItems,
                    $map[$event->getStatus()],
                    $event->getMessage()
                ));
            }
        );

        // Do It!
        call_user_func(
            [$this->migrateService, static::ACTION_NAME],
            $migrator->getSlug(),
            $limit,
            $idList
        );

        // Final report
        $output->writeln(sprintf(
            "\n%s Finished for %s: <info>%s</info> processed"
            . "(<fg=yellow>%s</fg=yellow> skipped / <fg=red>%s</fg=red> failed)",
            ucfirst(static::ACTION_NAME),
            $migrator->getSlug(),
            number_format($processLog[MigrateResultInterface::PROCESSED]),
            number_format($processLog[MigrateResultInterface::SKIPPED]),
            number_format($processLog[MigrateResultInterface::FAILED])
        ));
    }

    /**
     * @param MigratorInterface $migrator
     * @return int
     */
    protected function getRecCount(MigratorInterface $migrator): int
    {
        return $migrator->getSource()->count();
    }

    /**
     * @return string
     */
    protected function getEventListenName(): string
    {
        return Events::MIGRATE;
    }
}
