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

namespace Shuttle\Service\Migrator\Event;

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
    public function isReverted()
    {
        return $this->isMigrated();
    }
}
