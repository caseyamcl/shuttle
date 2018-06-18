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

use Shuttle\Migrator\MigratorInterface;
use Shuttle\ShuttleAction;
use Shuttle\SourceIdIterator;

/**
 * Revert Command
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Revert extends Migrate
{
    const ACTION_NAME = ShuttleAction::REVERT;

    /**
     * @param MigratorInterface $migrator
     * @param iterable|null $sourceIds
     * @param callable $continue
     */
    protected function runAction(MigratorInterface $migrator, ?iterable $sourceIds, callable $continue)
    {
        $this->shuttle->revert($migrator, $sourceIds, $continue);
    }

    /**
     * @param MigratorInterface $migrator
     * @return SourceIdIterator
     */
    protected function getIdIterator(MigratorInterface $migrator): SourceIdIterator
    {
        return $migrator->getMigratedSourceIdIterator();
    }
}
