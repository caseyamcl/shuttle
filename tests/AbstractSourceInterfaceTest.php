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
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class AbstractSourceInterfaceTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class AbstractSourceInterfaceTest extends TestCase
{
    public function testGetSourceIdIteratorReturnsIterableOfStrings()
    {
        $obj = $this->getSourceObj();
        $ids = $obj->getSourceIdIterator();

        $this->assertTrue(is_array($ids) or $ids instanceof \Traversable);
        $this->assertContainsOnly('string', $ids);
    }

    public function testGetItemReturnsSourceItemInstance()
    {
        $obj = $this->getSourceObj();
        $sourceItem = $obj->getSourceItem($this->getExistingRecordId());

        $this->assertInstanceOf(SourceItem::class, $sourceItem);
        $this->assertEquals($this->getExistingRecordId(), $sourceItem->getId());
    }

    /**
     * @expectedException \Shuttle\Exception\MissingItemException
     */
    public function testGetItemThrowsMissingRecordExceptionForNonExistentRecord()
    {
        $obj = $this->getSourceObj();
        $obj->getSourceItem($this->getNonExistentRecordId());
    }

    public function testCountItemsReturnsPositiveIntOrNull()
    {
        $obj = $this->getSourceObj();
        $this->assertEquals($this->getExpectedCount(), $obj->countSourceItems());
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

    /**
     * @return int|null
     */
    abstract protected function getExpectedCount(): ?int;
}
