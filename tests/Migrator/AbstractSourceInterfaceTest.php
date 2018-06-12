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

namespace ShuttleTest\Migrator;

use PHPUnit\Framework\TestCase;

/**
 * Class AbstractSourceInterfaceTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractSourceInterfaceTest extends TestCase
{
    public function testListRecordIdsReturnsTraversableListOfStrings()
    {
        $obj = $this->getSourceObj();
        $ids = $obj->listItemIds();

        $this->assertTrue(is_array($ids) or $ids instanceof \Traversable);
        $this->assertContainsOnly('string', $ids);
    }

    public function testGetRecordReturnsNonEmptyArrayForExistingRecord()
    {
        $obj = $this->getSourceObj();
        $rec = $obj->getItem($this->getExistingRecordId());

        $this->assertInternalType('array', $rec);
        $this->assertNotEmpty($rec);
    }

    /**
     * @expectedException \Shuttle\Exception\MissingItemException
     */
    public function testGetRecordThrowsMissingRecordExceptionForNonExistentRecord()
    {
        $obj = $this->getSourceObj();
        $obj->getItem($this->getNonExistentRecordId());
    }

    // ---------------------------------------------------------------

    /**
     * @return SourceInterface
     */
    abstract protected function getSourceObj(): SourceInterface;

    /**
     * @return string
     */
    abstract protected function getExistingRecordId(): string;

    /**
     * @return string
     */
    abstract protected function getNonExistentRecordId(): string;
}
