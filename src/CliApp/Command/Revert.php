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

namespace ConveyorBelt\CliApp\Command;

use ConveyorBelt\Service\Migrator\Events;
use ConveyorBelt\Service\Migrator\MigrateFailedResult;
use ConveyorBelt\Service\Migrator\MigratorInterface;


/**
 * Revert Command
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Revert extends Migrate
{
    /**
     * @param MigratorInterface $migrator
     * @return int
     */
    protected function getRecCount(MigratorInterface $migrator)
    {
        return $this->migrateService->getRecorder()->getMigratedCount($migrator->getSlug());
    }

    // ---------------------------------------------------------------

    /**
     * @return string
     */
    protected function getActionName()
    {
        return "revert";
    }

    // ---------------------------------------------------------------

    /**
     * @return string
     */
    protected function getEventListenName()
    {
        return Events::REVERT;
    }
}
