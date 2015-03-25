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

namespace ConveyorBeltMigrator\Example;

use ConveyorBelt\Service\Migrator\DestinationInterface;
use ConveyorBelt\Service\Migrator\Exception\MissingRecordException;

/**
 * Class ExampleDestination
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ExampleDestination implements DestinationInterface
{
    /**
     * The destination array for the records
     *
     * This would normally be a database or something similar
     *
     * @var array
     */
    private $recs = [];

    /**
     * @var int  Internal counter tool
     */
    private $ct = 1;

    /**
     * Get record
     *
     * @param string $id
     * @return array  Record, represented as array
     * @throws MissingRecordException
     */
    function getRecord($id)
    {
        if (isset($this->recs[$id])) {
            return $this->recs[$id];
        }
        else {
            throw new MissingRecordException("Cannot find record with ID: ". $id);
        }
    }

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param array $recordData
     * @return string  The ID of the inserted record
     */
    function saveRecord(array $recordData)
    {
        $ct = $this->ct;
        $this->recs[$ct] = $recordData;
        $this->ct++;
        return $ct;
    }

    /**
     * Remove a record
     *
     * @param string $id
     * @return bool  If a record existed to be deleted, returns TRUE, else FALSE
     */
    function deleteRecord($id)
    {
        if (isset($this->recs[$id])) {
            unset($this->recs[$id]);
            return true;
        }
        else {
            return false;
        }
    }
}
