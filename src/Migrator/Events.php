<?php
/**
 * Shuttle
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

namespace Shuttle\Migrator;

final class Events
{
    /**
     * Migrate event dispatched when an item migration has completed
     */
    const MIGRATE = 'shuttle.migrate';

    /**
     * Revert event dispatched when an item revert has completed
     */
    const REVERT = 'shuttle.revert';

    /**
     * Action event dispatched whenever a revert or a migration has complete
     */
    const REVERT_OR_MIGRATE = 'shuttle.action';

    /**
     * Pre-prepare event dispatched after retrieving source item, but before preparing it for destination
     */
    const PRE_PREPARE = 'shuttle.pre_prepare';

    /**
     * Pre-persist event dispatched after preparing item for destination, but before saving it
     */
    const PRE_PERSIST = 'shuttle.pre_persist';
}
