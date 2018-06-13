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

namespace ShuttleTest;

use PHPUnit\Framework\TestCase;
use Shuttle\DestinationInterface;

abstract class AbstractDestinationInterfaceTest extends TestCase
{
    public function testPersistItemSavesTheRecord()
    {
        $obj = $this->getDestObj();
        $recordData = $this->getNewRecordData();
        $newId = $obj->persist($recordData);

        $this->assertNotEmpty($newId);
    }

    public function testRemoveItemReturnsTrueForDeletingRealRecord()
    {
        $obj = $this->getDestObj();
        $recordData = $this->getNewRecordData();
        $newId = $obj->persist($recordData);

        $this->assertTrue($obj->remove($newId));
    }

    public function testRemoveItemReturnsFalseForDeletingNonExistentRecord()
    {
        $obj = $this->getDestObj();
        $this->assertFalse($obj->remove($this->getNonExistentItemId()));
    }

    // ---------------------------------------------------------------

    /**
     * Get the destination under test
     *
     * @return DestinationInterface
     */
    abstract protected function getDestObj(): DestinationInterface;

    /**
     * Get some data to put into the destination
     *
     * @return mixed
     */
    abstract protected function getNewRecordData();

    /**
     * Get a destination ID for an item that is expected to be in the destination
     *
     * @return string
     */
    abstract protected function getExistingItemId(): string;

    /**
     * Get a destination ID for an item that is expected to NOT be in the destination
     *
     * @return string
     */
    abstract protected function getNonExistentItemId(): string;
}
