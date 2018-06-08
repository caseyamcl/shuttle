<?php

namespace Shuttle\Helper;

use Shuttle\Shuttle;
use Shuttle\ConsoleCommand as ConsoleCmd;
use Symfony\Component\Console\Command\Command;

/**
 * Class ConsoleCommandBuilder
 * @package Shuttle\Helper
 */
class ConsoleCommandBuilder
{
    /**
     * @param Shuttle $shuttle
     * @param bool $onePerMigrator
     * @param bool $includeListCommand
     * @return array|Command[]
     */
    public static function build(Shuttle $shuttle, bool $onePerMigrator = true, bool $includeListCommand = false)
    {
        return (new static())->buildCommands($shuttle, $onePerMigrator, $includeListCommand);
    }

    /**
     * @param Shuttle $shuttle
     * @param bool $onePerMigrator
     * @param bool $includeListCommand
     * @return array|Command[]
     */
    public function buildCommands(Shuttle $shuttle, bool $onePerMigrator = true, bool $includeListCommand = false)
    {
        $service = $shuttle->getMigrateService();

        if ($onePerMigrator == true) {
            foreach ($shuttle->getMigrators() as $migrator) {
                $commands[] = new ConsoleCmd\Migrate($service, $shuttle->getMigrators(), $migrator);
                $commands[] = new ConsoleCmd\Revert($service, $shuttle->getMigrators(), $migrator);
            }
        } else {
            $commands[] = new ConsoleCmd\Migrate($service, $shuttle->getMigrators());
            $commands[] = new ConsoleCmd\Revert($service, $shuttle->getMigrators());
        }

        if ($includeListCommand) {
            $commands[] = new ConsoleCmd\MigratorsList($shuttle->getMigrators(), $shuttle->getRecorder());
        }

        return $commands ?? [];
    }
}
