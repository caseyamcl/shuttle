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

use Shuttle\Helper\Tracker;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\MigratorCollection;
use Shuttle\Shuttle;
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
     * @var Shuttle
     */
    protected $shuttle;

    /**
     * @var MigratorCollection
     */
    private $migratorCollection;

    /**
     * @var MigratorInterface|null
     */
    protected $migrator;

    /**
     * Constructor
     *
     * @param Shuttle $shuttle  Migrate service
     * @param string $migratorName Optionally provide migrator name
     *                            (if not provided, a CLI argument is made available to the user)
     */
    public function __construct(Shuttle $shuttle, string $migratorName = '')
    {
        $this->shuttle = $shuttle;

        if ($migratorName) {
            $this->migrator = $shuttle->getMigrators()->get($migratorName);
        }

        parent::__construct();
    }

    protected function configure()
    {
        if ($this->migrator) {
            $this->setName('migrators:' . static::ACTION_NAME . ':' . (string) $this->migrator);
            $this->setDescription(ucfirst(static::ACTION_NAME) . " " . (string) $this->migrator);
        } else {
            $this->setName('migrators:' . static::ACTION_NAME);
            $this->addArgument(
                'migrator',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'The name (slug) of the migrators to ' . static::ACTION_NAME
            );

            $this->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                "Process all migrators (--dependencies is automatically applied here)"
            );

            $this->addOption(
                'dependencies',
                'd',
                InputOption::VALUE_NONE,
                'Process dependencies for specified migrators'
            );
        }

        if (static::ACTION_NAME !== Revert::ACTION_NAME) {
            $this->addOption(
                'clobber',
                'c',
                InputOption::VALUE_NONE,
                'Clobber; remove previously migrated items and migrate all items'
            );
        }

        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_REQUIRED,
            'Set a limit on the number of items to ' . static::ACTION_NAME
        );

        $this->addOption(
            'ids',
            'i',
            InputOption::VALUE_REQUIRED,
            'A comma-separated list of source IDs to ' . static::ACTION_NAME
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Setup a tracker
        $tracker = $this->getNewTracker();
        $this->shuttle->getEventDispatcher()->addSubscriber($tracker);

        // Setup migrators
        switch (true) {
            case $this->migrator:
                $migrators = [$this->migrator];
                break;
            case $input->getOption('all'):
                $migrators = $this->migratorCollection->getIterator();
                break;
            default:
                $migratorSlugs = $input->getArgument('migrator');
                $migrators = $input->getOption('dependencies')
                    ? call_user_func_array([$this->migratorCollection, 'resolveDependencies'], $migratorSlugs)
                    : call_user_func_array([$this->migratorCollection, 'getMultiple'], $migratorSlugs);
        }

        // Setup item limit
        $limit = (int) $input->getOption('limit') ?: 0;

        // Read ID list parameter
        $idList = ($input->getOption('ids'))
            ? array_filter(array_map('trim', explode(',', $input->getOption('ids'))))
            : [];

        // Setup 'clobber' option
        $clobber = $input->hasOption('clobber') ? $input->getOption('clobber') : false;

        /** @var MigratorInterface $migrator */
        foreach ($migrators as $migrator) {
            // Output some info
            $output->writeln(sprintf(
                'Processing items from <info>%s</info> (%s)',
                $migrator->getName(),
                $migrator->getDescription()
            ));

            // Do it..
            if ((! $limit) or $tracker->getTotalCount() < $limit) {
                foreach ($this->getActionIterator($migrator, $idList, $clobber) as $result) {
                    if ($limit && $tracker->getTotalCount() >= $limit) {
                        $output->writeln(sprintf(
                            "Limit reached (%s).  No more items will be processed.",
                            number_format($limit)
                        ));
                        break;
                    }

                    $this->logEvent($output, $result, $tracker->getTotalCount($migrator->getName()));
                }
            }

            $output->writeln(sprintf(
                'Finished <info>%s</info> %d total: <fg=green>%d</fg=green> processed'
                . ' / <fg=yellow>%d</fg=yellow> skipped / <fg=red>%d</fg=red> failed',
                $migrator->getName(),
                number_format($tracker->getTotalCount($migrator->getName())),
                number_format($tracker->getProcessedCount($migrator->getName())),
                number_format($tracker->getSkippedCount($migrator->getName())),
                number_format($tracker->getFailedCount($migrator->getName()))
            ));
        }

        // Final report
        $output->writeln(''); // Skip a line
        $output->writeln(sprintf(
            '%s complete (%d total): <fg=green>%d</fg=green> processed'
            . ' / <fg=yellow>%d</fg=yellow> skipped / <fg=red>%d</fg=red> failed',
            ucfirst(static::ACTION_NAME),
            number_format($tracker->getTotalCount()),
            number_format($tracker->getProcessedCount()),
            number_format($tracker->getSkippedCount()),
            number_format($tracker->getFailedCount())
        ));

        // Cleanup, in case this is running a sub-command
        $this->shuttle->getDispatcher()->removeSubscriber($tracker);
        unset($tracker);
    }

    /**
     * Log event
     *
     * @param OutputInterface $output
     * @param MigrateResultInterface $event
     * @param int $itemCount
     */
    protected function logEvent(OutputInterface $output, MigrateResultInterface $event, int $itemCount)
    {
        $map = [$event::FAILED => 'FAIL', $event::PROCESSED => 'SAVE', $event::SKIPPED => 'SKIP'];
        $output->writeln(sprintf(' * [%d] %s: %s', $itemCount, $map[$event->getStatus()], $event->getMessage()));
    }

    /**
     * @param MigratorInterface $migrator
     * @param array $ids |string[]  Source IDs (pass empty array for all source items)
     * @param bool $clobber
     * @return iterable|MigrateResultInterface[]
     */
    protected function getActionIterator(MigratorInterface $migrator, array $ids = [], bool $clobber = false): iterable
    {
        return $this->shuttle->migrateItems($migrator, $ids, $clobber);
    }

    /**
     * @return Tracker
     */
    protected function getNewTracker(): Tracker
    {
        return new Tracker(Events::MIGRATE);
    }
}
