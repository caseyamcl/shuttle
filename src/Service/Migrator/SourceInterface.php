<?php
/**
 * conveyorbelt
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

namespace ConveyorBelt\Service\Migrator;

use ConveyorBelt\Service\Migrator\Exception\MissingRecordException;

/**
 * Source Interface
 *
 * @package ConveyorBelt\Service\Migrator\Source
 */
interface SourceInterface extends \Traversable, \Countable
{
    /**
     * @return array|\Traversable|string[]  Get a list of record IDs in the source
     */
    function listRecordIds();

    /**
     * @param string $id  The record ID to get
     * @return array Record, represented as key/value associative array
     * @throws MissingRecordException
     */
    function getRecord($id);
}
