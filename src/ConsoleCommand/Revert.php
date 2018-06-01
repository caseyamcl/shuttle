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

use Shuttle\Migrator\Events;
use Shuttle\Migrator\MigratorInterface;

/**
 * Revert Command
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Revert extends Migrate
{
    const ACTION_NAME = 'revert';

    /**
     * @param MigratorInterface $migrator
     * @return int
     */
    protected function getRecCount(MigratorInterface $migrator): int
    {
        return $this->migrateService->getRecorder()->getMigratedCount($migrator->getSlug());
    }

    /**
     * @return string
     */
    protected function getEventListenName(): string
    {
        return Events::REVERT;
    }
}
