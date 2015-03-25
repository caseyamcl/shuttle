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

namespace ConveyorBeltTest\Service\Migrator;


use ConveyorBelt\Service\Migrator\DestinationInterface;

abstract class AbstractDestinationInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveRecordSavesTheRecord()
    {
        $obj = $this->getDestObj();
        $recordData = $this->getNewRecordData();
        $newId = $obj->saveRecord($recordData);

        $newRec = $obj->getRecord($newId);

        $this->assertInternalType('array', $newRec);
        $this->assertNotEmpty($newRec);
    }

    public function testDeleteRecordReturnsTrueForDeletingRealRecord()
    {
        $obj = $this->getDestObj();
        $recordData = $this->getNewRecordData();
        $newId = $obj->saveRecord($recordData);

        $this->assertTrue($obj->deleteRecord($newId));
    }

    public function testDeleteRecordReturnsFalseForDeletingNonExistentRecord()
    {
        $obj = $this->getDestObj();
        $this->assertFalse($obj->deleteRecord($this->getNonExistentRecordId()));
    }

    public function testGetRecordReturnsNonEmptyArrayForExistingRecord()
    {
        $obj = $this->getDestObj();
        $rec = $obj->getRecord($this->getExistingRecordId());

        $this->assertInternalType('array', $rec);
        $this->assertNotEmpty($rec);
    }

    public function testGetRecordThrowsMissingRecordExceptionForNonExistentRecord()
    {
        $this->setExpectedException('\ConveyorBelt\Service\Migrator\Exception\MissingRecordException');
        $obj = $this->getDestObj();
        $obj->getRecord($this->getNonExistentRecordId());
    }

    // ---------------------------------------------------------------

    /**
     * @return DestinationInterface
     */
    abstract protected function getDestObj();

    /**
     * @return string
     */
    abstract protected function getExistingRecordId();

    /**
     * @return array key/value pairs for record to add
     */
    abstract protected function getNewRecordData();

    /**
     * @return string
     */
    abstract protected function getNonExistentRecordId();
}
