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

namespace Shuttle\MigrateSource;

use Shuttle\Exception\MissingItemException;
use Shuttle\SourceIdIterator;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Simple JSON Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class JsonSource implements SourceInterface
{
    /**
     * @var array|array[]  Array of arrays
     */
    private $items;

    /**
     * Constructor
     *
     * @param string $rawJsonData
     * @param string $idFieldName  If empty, assume key/value arrangement
     */
    public function __construct(string $rawJsonData, string $idFieldName = '')
    {
        $this->items = $this->decodeInput($rawJsonData, $idFieldName);
    }

    /**
     * Decode Input
     *
     * @param string $rawInput
     * @param string $idFieldName
     * @return array Keys are IDs, values are records (as associative arrays)
     */
    protected function decodeInput($rawInput, $idFieldName)
    {
        $arr = [];

        foreach (json_decode($rawInput, true) as $key => $val) {
            $id = $idFieldName ? $val[$idFieldName] : $key;
            $arr[$id] = $val;
        }

        return $arr;
    }


    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int
    {
        return count($this->items);
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws \Exception  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem
    {
        if (array_key_exists($id, $this->items)) {
            return new SourceItem($id, $this->items[$id]);
        } else {
            throw new MissingItemException("Could not find record with ID: " . $id);
        }
    }

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return SourceIdIterator|string[]
     */
    public function getSourceIdIterator(): SourceIdIterator
    {
        return new SourceIdIterator(array_map('strval', array_keys($this->items)));
    }
}
