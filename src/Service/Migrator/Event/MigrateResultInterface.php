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

interface MigrateResultInterface
{
    /**
     * @return int  (-1 skipped; 0 failed; 1 success)
     */
    function getStatus();

    /**
     * @return string
     */
    function getMessage();
}
