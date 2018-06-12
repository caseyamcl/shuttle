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

use Shuttle\__OLD_Migrator\Event\MigrateResultInterface;
use Shuttle\__OLD_Migrator\Events;
use Shuttle\__OLD_Migrator\MigratorInterface;
use Shuttle\Helper\Tracker;

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
        return $this->shuttle->revertItems($migrator, $ids);
    }

    /**
     * @return Tracker
     */
    protected function getNewTracker(): Tracker
    {
        return new Tracker(Events::REVERT);
    }
}
