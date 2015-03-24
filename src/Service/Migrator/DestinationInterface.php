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
 * Interface DestinationInterface
 *
 * @package ConveyorBelt\Service\Migrator\Destination
 */
interface DestinationInterface
{
    /**
     * Get record
     *
     * @param string $id
     * @return array  Record, represented as array
     * @throws MissingRecordException
     */
    function getRecord($id);

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param array $recordData
     * @return string  The ID of the inserted record
     */
    function saveRecord(array $recordData);


    /**
     * Remove a record
     *
     * @param string $id
     * @return bool  If a record existed to be deleted, returns TRUE, else FALSE
     */
    function deleteRecord($id);
}
