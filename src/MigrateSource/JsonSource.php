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

use Shuttle\Migrator\Exception\MissingItemException;
use Shuttle\Migrator\SourceInterface;

/**
 * Simple JSON Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class JsonSource implements \IteratorAggregate, SourceInterface
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
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return array|iterable|string[]  Get a list of item IDs in the source
     */
    function listItemIds(): iterable
    {
        return array_map('strval', array_keys($this->items));
    }

    function getItem(string $id): array
    {
        if (array_key_exists($id, $this->items)) {
            return $this->items[$id];
        } else {
            throw new MissingItemException("Could not find record with ID: " . $id);
        }
    }

    function getIterator()
    {
        return new \ArrayIterator($this->listItemIds());
    }
}
