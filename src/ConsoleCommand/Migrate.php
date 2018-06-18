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

use Shuttle\Event\ActionResultInterface;
use Shuttle\Event\MigrateFailedEvent;
use Shuttle\Event\RevertFailedEvent;
use Shuttle\Helper\Tracker;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\Shuttle;
use Shuttle\ShuttleAction;
use Shuttle\ShuttleEvents;
use Shuttle\SourceIdIterator;
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
    const ACTION_NAME = ShuttleAction::MIGRATE;

    /**
     * @var Shuttle
     */
    protected $shuttle;

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
            $this->setName('shuttle:' . static::ACTION_NAME . ':' . (string) $this->migrator);
            $this->setDescription(ucfirst(static::ACTION_NAME) . " " . (string) $this->migrator);
        } else {
            $this->setName('shuttle:' . static::ACTION_NAME);
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

        if (static::ACTION_NAME == ShuttleAction::MIGRATE) {
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

        $this->addOption(
            'abort-on-error',
            'o',
            InputOption::VALUE_NONE,
            'Abort on the first error'
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
        $tracker = Tracker::createAndAttach(static::ACTION_NAME, $this->shuttle->getEventDispatcher());

        // Setup console logging
        $consoleLogger = function (ActionResultInterface $result) use ($output, $tracker) {
            $this->logAction($output, $result, $tracker);
        };
        $this->shuttle->getEventDispatcher()->addListener(ShuttleEvents::MIGRATE_RESULT, $consoleLogger);
        $this->shuttle->getEventDispatcher()->addListener(ShuttleEvents::REVERT_RESULT, $consoleLogger);

        // Setup migrators
        switch (true) {
            case $this->migrator:
                $migrators = $input->getOption('dependencies')
                    ? $this->shuttle->getMigrators()->resolveDependencies([$this->migrator])
                    : [$this->migrator];
                break;
            case $input->getOption('all'):
                $migrators = $this->shuttle->getMigrators()->getIterator(static::ACTION_NAME);
                break;
            default:
                $migratorSlugs = $input->getArgument('migrator');
                $migrators = $input->getOption('dependencies')
                    ? $this->shuttle->getMigrators()->resolveDependencies($migratorSlugs, static::ACTION_NAME)
                    : $this->shuttle->getMigrators()->getMultiple($migratorSlugs);
        }

        // Read ID list parameter
        $idList = ($input->getOption('ids'))
            ? array_filter(array_map('trim', explode(',', $input->getOption('ids'))))
            : null;

        // Setup a continue callback
        $continueCallback = function (?ActionResultInterface $lastAction) use ($tracker, $input): bool {
            $limit = (int) $input->getOption('limit') ?: 0;
            $errorAbort = $input->getOption('abort-on-error');

            if ($errorAbort && $lastAction && $lastAction->getStatus() == ActionResultInterface::FAILED) {
                if ($lastAction instanceof MigrateFailedEvent or $lastAction instanceof RevertFailedEvent) {
                    $message = sprintf(
                        '%s failed for record (type %s) with source ID %s: %s',
                        ucfirst(static::ACTION_NAME),
                        $lastAction->getMigratorName(),
                        $lastAction->getSourceId(),
                        $lastAction->getException()->getMessage()
                    );

                    throw new \RuntimeException(
                        $message,
                        $lastAction->getException()->getCode(),
                        $lastAction->getException()
                    );
                }
                return false;
            } elseif ($limit && $tracker->getTotalCount() >= $limit) {
                return false;
            } else {
                return true;
            }
        };

        /** @var MigratorInterface $migrator */
        foreach ($migrators as $migrator) {
            $sourceIds = $idList ? new SourceIdIterator($idList) : $this->getIdIterator($migrator);

            // Output some info
            $output->writeln(sprintf(
                'Processing <info>%s</info> items from <info>%s</info> (%s)',
                number_format($sourceIds->count()),
                $migrator->__toString(),
                $migrator->getDescription()
            ));

            // Do it.
            $this->runAction($migrator, $sourceIds, $continueCallback);

            // Ouptut per-migrator report
            $output->writeln(sprintf(
                '<info>%s</info> complete; %d total: <fg=green>%d</fg=green> processed'
                . ' / <fg=yellow>%d</fg=yellow> skipped / <fg=red>%d</fg=red> failed',
                (string) $migrator,
                number_format($tracker->getTotalCount((string) $migrator)),
                number_format($tracker->getProcessedCount((string) $migrator)),
                number_format($tracker->getSkippedCount((string) $migrator)),
                number_format($tracker->getFailedCount((string) $migrator))
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
        $this->shuttle->getEventDispatcher()->removeSubscriber($tracker);
        unset($tracker);
    }

    /**
     * @param MigratorInterface $migrator
     * @param iterable|null $sourceIds
     * @param callable $continue
     */
    protected function runAction(MigratorInterface $migrator, ?iterable $sourceIds, callable $continue)
    {
        $this->shuttle->migrate($migrator, $sourceIds, $continue);
    }

    /**
     * @param MigratorInterface $migrator
     * @return SourceIdIterator
     */
    protected function getIdIterator(MigratorInterface $migrator): SourceIdIterator
    {
        return $migrator->getSourceIdIterator();
    }

    /**
     * Log event
     *
     * @param OutputInterface $output
     * @param ActionResultInterface $event
     * @param Tracker $tracker
     */
    protected function logAction(OutputInterface $output, ActionResultInterface $event, Tracker $tracker)
    {
        $map = [
            $event::FAILED => '<fg=red>FAIL</fg=red>',
            $event::PROCESSED => '<fg=green>SAVE</fg=green>',
            $event::SKIPPED => '<fg=yellow>SKIP</fg=yellow>'
        ];
        $output->writeln(sprintf(
            ' * [%d] %s: %s',
            $tracker->getTotalCount($event->getMigratorName()),
            $map[$event->getStatus()],
            $event->getMessage()
        ));
    }
}
