<?php
/**
 * Shuttle
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

class RevertFailedResult extends MigrateFailedResult
{
    public function __construct($destRecId, $msg, \Exception $e = null)
    {
        parent::__construct($destRecId, $msg, $e);
    }
}
