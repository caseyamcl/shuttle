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

use ConveyorBelt\Service\Migrator\SourceInterface;

/**
 * Class AbstractSourceInterfaceTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractSourceInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testListRecordIdsReturnsTraversableListOfStrings()
    {
        $obj = $this->getSourceObj();
        $ids = $obj->listRecordIds();

        $this->assertTrue(is_array($ids) OR $ids instanceof \Traversable);
        $this->assertContainsOnly('string', $ids);
    }

    public function testGetRecordReturnsNonEmptyArrayForExistingRecord()
    {
        $obj = $this->getSourceObj();
        $rec = $obj->getRecord($this->getExistingRecordId());

        $this->assertInternalType('array', $rec);
        $this->assertNotEmpty($rec);
    }

    public function testGetRecordThrowsMissingRecordExceptionForNonExistentRecord()
    {
        $this->setExpectedException('\ConveyorBelt\Service\Migrator\Exception\MissingRecordException');
        $obj = $this->getSourceObj();
        $obj->getRecord($this->getNonExistentRecordId());
    }

    // ---------------------------------------------------------------

    /**
     * @return SourceInterface
     */
    abstract protected function getSourceObj();

    /**
     * @return string
     */
    abstract protected function getExistingRecordId();

    /**
     * @return string
     */
    abstract protected function getNonExistentRecordId();
}
