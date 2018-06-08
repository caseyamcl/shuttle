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
use Shuttle\Migrator\MigratorInterface;
use Shuttle\Migrator\MigrateTracker;

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
     * @param array $ids |string[]  Source IDs (pass empty array for all source items)
     * @param bool $clobber  Not used for revert. Ignore this.
     * @return iterable|MigrateResultInterface[]
     */
    protected function getActionIterator(MigratorInterface $migrator, array $ids = [], bool $clobber = false): iterable
    {
        return $this->migrateService->revertItems($migrator, $ids);
    }

    /**
     * @return MigrateTracker
     */
    protected function getNewTracker(): MigrateTracker
    {
        return new MigrateTracker(Events::REVERT);
    }
}
