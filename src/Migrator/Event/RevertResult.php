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

namespace Shuttle\Migrator\Event;

/**
 * Revert Result
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class RevertResult extends MigrateResult
{
    /**
     * Is Reverted is an alias for 'isMigrated'
     *
     * @return bool
     */
    public function isReverted(): bool
    {
        return $this->isMigrated();
    }
}
