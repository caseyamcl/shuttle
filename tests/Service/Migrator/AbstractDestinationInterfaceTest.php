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

namespace ShuttleTest\Service\Migrator;

use PHPUnit\Framework\TestCase;
use Shuttle\Migrator\DestinationInterface;

abstract class AbstractDestinationInterfaceTest extends TestCase
{
    public function testSaveRecordSavesTheRecord()
    {
        $obj = $this->getDestObj();
        $recordData = $this->getNewRecordData();
        $newId = $obj->saveItem($recordData);

        $this->assertTrue($obj->hasItem($newId));
    }

    public function testDeleteRecordReturnsTrueForDeletingRealRecord()
    {
        $obj = $this->getDestObj();
        $recordData = $this->getNewRecordData();
        $newId = $obj->saveItem($recordData);

        $this->assertTrue($obj->deleteItem($newId));
    }

    public function testDeleteRecordReturnsFalseForDeletingNonExistentRecord()
    {
        $obj = $this->getDestObj();
        $this->assertFalse($obj->deleteItem($this->getNonExistentRecordId()));
    }

    public function testGetRecordReturnsNonEmptyArrayForExistingRecord()
    {
        $obj = $this->getDestObj();
        $this->assertTrue($obj->hasItem($this->getExistingRecordId()));
    }

    public function testHasRecordReturnsFalseForNonExistentRecord()
    {
        $obj = $this->getDestObj();
        $this->assertFalse($obj->hasItem($this->getNonExistentRecordId()));
    }

    // ---------------------------------------------------------------

    /**
     * @return DestinationInterface
     */
    abstract protected function getDestObj(): DestinationInterface;

    /**
     * @return string
     */
    abstract protected function getExistingRecordId(): string;

    /**
     * @return array key/value pairs for record to add
     */
    abstract protected function getNewRecordData(): array;

    /**
     * @return string
     */
    abstract protected function getNonExistentRecordId(): string;
}
